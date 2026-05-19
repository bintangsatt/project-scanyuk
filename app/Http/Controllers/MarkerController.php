<?php

namespace App\Http\Controllers;

use App\Jobs\CompileMindARJob;
use App\Models\Marker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarkerController extends Controller
{
    /**
     * Upload gambar marker dan dispatch job compile
     *
     * POST /api/markers
     * Returns: JSON { marker_id, status, image_url }
     */
    public function upload(Request $request): JsonResponse
    {
        // Validasi file upload
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'], // max 5MB
        ]);

        // Simpan file ke storage/app/public/markers/
        $path = $request->file('image')->store('markers', 'public');

        // Buat record marker dengan status awal processing
        $marker = Marker::create([
            'image_path'    => 'public/' . $path,
            'status'        => Marker::STATUS_PROCESSING,
            'error_message' => null,
        ]);

        // Dispatch job ke queue untuk compile .mind di background
        CompileMindARJob::dispatch($marker);

        return response()->json([
            'marker_id' => $marker->id,
            'status'    => $marker->status,
            'image_url' => Storage::url($path),
            'message'   => 'Marker sedang diproses. Mohon tunggu...',
        ], 201);
    }

    /**
     * Cek status marker (digunakan polling dari frontend)
     *
     * GET /api/marker/{id}
     * Returns: JSON { id, status, image_url, mind_url, error_message }
     */
    public function status(Marker $marker): JsonResponse
    {
        return response()->json([
            'id'            => $marker->id,
            'status'        => $marker->status,
            'image_url'     => $marker->image_url,
            'mind_url'      => $marker->mind_url,
            'error_message' => $marker->error_message,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $markers = Marker::where('status', Marker::STATUS_READY)
            ->orderByDesc('id')
            ->get()
            ->map(fn (Marker $marker) => [
                'id'        => $marker->id,
                'image_url' => $marker->image_url,
                'status'    => $marker->status,
            ]);

        return response()->json($markers);
    }
}
