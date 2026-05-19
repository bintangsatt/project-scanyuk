<?php

namespace App\Jobs;

use App\Models\ArProject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class ConvertBlenderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 600;

    public function __construct(
        protected ArProject $project,
        protected string    $tmpPath
    ) {}

    public function handle(): void
    {
        $this->project->update(['status' => 'processing']);

        $source      = Storage::disk('local')->path($this->tmpPath);
        $glbFileName = uniqid('model_') . '.glb';
        $destination = Storage::disk('public')->path('models/' . $glbFileName);
        $scriptPath  = null;

        Storage::disk('public')->makeDirectory('models');

        $blender    = $this->resolveBlender();
        $scriptPath = $this->writeExportScript();

        $process = new Process([
            $blender,
            '--background',
            '--python', $scriptPath,
            '--',
            $source,
            $destination,
        ]);
        $process->setTimeout(540);
        $process->run();

        sleep(1);
        clearstatcache(true, $destination);

        $stdout   = $process->getOutput();
        $stderr   = $process->getErrorOutput();
        $fileSize = file_exists($destination) ? filesize($destination) : 0;
        $success  = $process->isSuccessful() && $fileSize > 0;

        Log::info('[ConvertBlenderJob] Blender output', [
            'project_id'  => $this->project->id,
            'exit_code'   => $process->getExitCode(),
            'file_size'   => $fileSize,
            'stdout_tail' => substr($stdout, -2000),
            'stderr_tail' => substr($stderr, -1000),
        ]);

        if ($success) {
            $this->project->update([
                'model_path' => 'public/models/' . $glbFileName,
                'status'     => 'ready',
            ]);
            Storage::disk('local')->delete($this->tmpPath);
            Log::info('[ConvertBlenderJob] Selesai', [
                'project_id' => $this->project->id,
                'file_size'  => $fileSize,
            ]);
        } else {
            $this->project->update(['status' => 'failed']);
            Log::error('[ConvertBlenderJob] Gagal', [
                'project_id' => $this->project->id,
                'exit_code'  => $process->getExitCode(),
                'file_size'  => $fileSize,
            ]);
        }

        if ($scriptPath && file_exists($scriptPath)) {
            unlink($scriptPath);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('[ConvertBlenderJob] Exception: ' . $e->getMessage());
        $this->project->update(['status' => 'failed']);
    }

    private function writeExportScript(): string
    {
        $scriptDir  = Storage::disk('local')->path('tmp-scripts');
        $scriptPath = $scriptDir . DIRECTORY_SEPARATOR . $this->project->id . '.py';

        if (!is_dir($scriptDir)) {
            mkdir($scriptDir, 0755, true);
        }

        // PRINSIP: jangan modifikasi scene sama sekali.
        // Export dengan settings PERSIS SAMA seperti klik Export GLB di UI Blender.
        // Semua modifikasi (skala, warna) dilakukan di Three.js sisi frontend.
        file_put_contents($scriptPath, <<<'PYSCRIPT'
import bpy, sys, os

argv = sys.argv
idx  = argv.index('--')
src  = argv[idx + 1]
dst  = argv[idx + 2]

print("[WebAR] Blender " + bpy.app.version_string)

# ── Enable GLTF addon ─────────────────────────────────────────────────────────
addon = "io_scene_gltf2"
if addon not in bpy.context.preferences.addons:
    bpy.ops.preferences.addon_enable(module=addon)

# ── Buka file ─────────────────────────────────────────────────────────────────
bpy.ops.wm.open_mainfile(filepath=src)

# ── Analisa texture situation ─────────────────────────────────────────────────
print(f"[WebAR] Materials={len(bpy.data.materials)} Images={len(bpy.data.images)} Actions={len(bpy.data.actions)}")

packed_ok    = 0
missing_imgs = []

for img in bpy.data.images:
    if img.type in ('RENDER_RESULT', 'COMPOSITING'):
        continue
    if img.packed_file:
        packed_ok += 1
        print(f"[WebAR] IMG OK (packed): {img.name!r}")
    else:
        abs_path = bpy.path.abspath(img.filepath) if img.filepath else ""
        if abs_path and os.path.exists(abs_path):
            try:
                img.pack()
                packed_ok += 1
                print(f"[WebAR] IMG PACKED: {img.name!r}")
            except Exception as e:
                missing_imgs.append(img)
                print(f"[WebAR] IMG PACK FAIL: {img.name!r} {e}")
        else:
            missing_imgs.append(img)
            print(f"[WebAR] IMG MISSING: {img.name!r} path={abs_path!r}")

print(f"[WebAR] packed_ok={packed_ok} missing={len(missing_imgs)}")

# ── Jika ada texture yang missing: bake ke vertex color ───────────────────────
# Ini fallback agar model tetap berwarna meski texture file tidak tersedia
if missing_imgs:
    print("[WebAR] Attempting vertex color bake for missing textures...")

    # Set render engine ke CYCLES untuk bake
    scene = bpy.context.scene
    original_engine = scene.render.engine
    scene.render.engine = 'CYCLES'
    scene.cycles.device = 'CPU'
    scene.cycles.samples = 4  # minimal samples, cukup untuk bake warna

    for obj in bpy.data.objects:
        if obj.type != 'MESH':
            continue

        # Skip jika material tidak punya missing texture
        has_missing = False
        for mat_slot in obj.material_slots:
            mat = mat_slot.material
            if not mat or not mat.use_nodes:
                continue
            for node in mat.node_tree.nodes:
                if node.type == 'TEX_IMAGE' and node.image in missing_imgs:
                    has_missing = True
                    break

        if not has_missing:
            continue

        print(f"[WebAR] Baking {obj.name!r}...")

        # Buat vertex color layer baru
        vcol_name = "BakedColor"
        if vcol_name not in obj.data.color_attributes:
            obj.data.color_attributes.new(name=vcol_name, type='BYTE_COLOR', domain='CORNER')

        vcol_attr = obj.data.color_attributes[vcol_name]

        # Set active object
        bpy.context.view_layer.objects.active = obj
        obj.select_set(True)

        # Buat render target image untuk bake
        bake_img = bpy.data.images.new("BakeTarget", width=512, height=512, alpha=False)
        bake_img.generated_color = (0.5, 0.5, 0.5, 1.0)

        # Tambahkan Image Texture node di setiap material sebagai bake target
        bake_nodes = []
        for mat_slot in obj.material_slots:
            mat = mat_slot.material
            if not mat or not mat.node_tree:
                continue
            img_node = mat.node_tree.nodes.new('ShaderNodeTexImage')
            img_node.image = bake_img
            img_node.select = True
            mat.node_tree.nodes.active = img_node
            bake_nodes.append((mat, img_node))

        try:
            bpy.ops.object.bake(type='DIFFUSE', pass_filter={'COLOR'}, target='VERTEX_COLORS')
            print(f"[WebAR] Bake OK: {obj.name!r}")
        except Exception as e:
            print(f"[WebAR] Bake FAILED: {obj.name!r} - {e}")

        # Hapus bake target nodes
        for mat, node in bake_nodes:
            mat.node_tree.nodes.remove(node)

        bpy.data.images.remove(bake_img)
        obj.select_set(False)

    scene.render.engine = original_engine

# ── Deteksi parameter valid ────────────────────────────────────────────────────
rna        = bpy.ops.export_scene.gltf.get_rna_type()
valid_keys = {p.identifier for p in rna.properties}

def safe_param(key, val):
    if key in valid_keys:
        return {key: val}
    print("[WebAR] SKIP: " + key)
    return {}

# ── Pilih anim mode ───────────────────────────────────────────────────────────
anim_mode = 'ACTIONS'
if 'export_anim_mode' in valid_keys:
    rna_prop    = next(p for p in rna.properties if p.identifier == 'export_anim_mode')
    valid_modes = [e.identifier for e in rna_prop.enum_items]
    for preferred in ('ACTIONS', 'ACTIVE_ACTIONS', 'NLA_TRACKS', 'SCENE'):
        if preferred in valid_modes:
            anim_mode = preferred
            break
print("[WebAR] anim_mode=" + anim_mode)

# ── Export ────────────────────────────────────────────────────────────────────
params = {"filepath": dst, "export_format": "GLB"}
params.update(safe_param("use_selection",     False))
params.update(safe_param("export_apply",      False))
params.update(safe_param("export_materials",  "EXPORT"))
params.update(safe_param("export_image_format", "AUTO"))
params.update(safe_param("export_texcoords",  True))
params.update(safe_param("export_normals",    True))
params.update(safe_param("export_colors",     True))
params.update(safe_param("export_animations", True))
params.update(safe_param("export_anim_mode",  anim_mode))
params.update(safe_param("export_current_frame", False))
params.update(safe_param("export_nla_strips", True))
params.update(safe_param("export_morph",      True))
params.update(safe_param("export_skins",      True))
params.update(safe_param("export_cameras",    False))
params.update(safe_param("export_lights",     False))

print("[WebAR] Exporting to: " + dst)
result = bpy.ops.export_scene.gltf(**params)
print("[WebAR] result: " + str(result))

if os.path.exists(dst):
    print("[WebAR] EXPORT_DONE: " + str(os.path.getsize(dst)) + " bytes")
else:
    print("[WebAR] ERROR: file not found")
    sys.exit(1)
PYSCRIPT);

        return $scriptPath;
    }

    private function resolveBlender(): string
    {
        $candidate = env('BLENDER_PATH', 'blender');
        $path      = trim(shell_exec('command -v ' . escapeshellarg($candidate)) ?: '');

        if (!$path) {
            throw new \RuntimeException(
                'Blender tidak ditemukan. Install Blender atau set BLENDER_PATH di .env'
            );
        }

        return $path;
    }
}