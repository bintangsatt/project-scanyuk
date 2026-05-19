#!/usr/bin/env node

/**
 * compile-marker.js
 * 
 * Script custom untuk compile gambar marker menjadi file .mind
 * menggunakan MindAR API secara langsung (bukan CLI binary).
 * 
 * Kenapa perlu script ini:
 *   mind-ar CLI bergantung pada `canvas` native module yang
 *   membutuhkan build tools khusus. Script ini menggunakan
 *   `@napi-rs/canvas` sebagai pengganti yang pre-compiled.
 * 
 * Usage:
 *   node compile-marker.js <input_image_path> <output_mind_path>
 * 
 * Exit codes:
 *   0 = sukses
 *   1 = error (pesan di stderr)
 */

'use strict';

const path = require('path');
const fs   = require('fs');

// ─── Validasi argumen ────────────────────────────────────────
const [,, inputPath, outputPath] = process.argv;

if (!inputPath || !outputPath) {
    console.error('Usage: node compile-marker.js <input> <output.mind>');
    process.exit(1);
}

if (!fs.existsSync(inputPath)) {
    console.error(`Error: File tidak ditemukan: ${inputPath}`);
    process.exit(1);
}

// Pastikan direktori output ada
const outputDir = path.dirname(outputPath);
if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
}

// ─── Load dependencies ───────────────────────────────────────
async function loadDependencies() {
    // Coba load canvas — coba beberapa alternatif secara berurutan
    let createCanvas, loadImage;

    // Opsi 1: @napi-rs/canvas (pre-compiled, tidak butuh build tools)
    try {
        const napiCanvas = require('@napi-rs/canvas');
        createCanvas = napiCanvas.createCanvas;
        loadImage    = napiCanvas.loadImage;
        console.log('Using @napi-rs/canvas');
        return { createCanvas, loadImage };
    } catch (e) { /* lanjut ke opsi berikutnya */ }

    // Opsi 2: canvas v3 (mungkin sudah ter-rebuild)
    try {
        const canvasMod = require('canvas');
        createCanvas = canvasMod.createCanvas;
        loadImage    = canvasMod.loadImage;
        console.log('Using canvas (v3)');
        return { createCanvas, loadImage };
    } catch (e) { /* lanjut */ }

    // Opsi 3: jimp + manual canvas shim (pure JS, lambat tapi works)
    try {
        const Jimp = require('jimp');
        console.log('Using jimp as canvas shim');
        return { createCanvas: null, loadImage: null, jimp: Jimp };
    } catch (e) { /* lanjut */ }

    throw new Error(
        'Tidak ada canvas library yang tersedia.\n' +
        'Jalankan salah satu:\n' +
        '  npm install @napi-rs/canvas\n' +
        '  atau: npm rebuild canvas\n' +
        '  atau: npm install jimp'
    );
}

// ─── Main compile function ───────────────────────────────────
async function compile() {
    console.log(`Compiling: ${inputPath} → ${outputPath}`);

    const deps = await loadDependencies();

    // Load MindAR compiler module
    // Coba dari node_modules lokal project dulu, fallback ke global
    let Compiler;
    const possiblePaths = [
        path.join(process.cwd(), 'node_modules/mind-ar/src/image-target/compiler.js'),
        path.join(process.cwd(), 'node_modules/mind-ar/dist/mindar-image.prod.js'),
        path.join(__dirname, 'node_modules/mind-ar/src/image-target/compiler.js'),
    ];

    // Coba load dari berbagai lokasi
    for (const p of possiblePaths) {
        if (fs.existsSync(p)) {
            try {
                // Patch global canvas sebelum load mind-ar
                if (deps.createCanvas) {
                    global.document = {
                        createElement: (tag) => {
                            if (tag === 'canvas') return deps.createCanvas(1, 1);
                            return {};
                        }
                    };
                }

                const mod = require(p);
                Compiler = mod.Compiler || mod.default?.Compiler || mod;
                if (Compiler && typeof Compiler === 'function') {
                    console.log(`Loaded MindAR compiler from: ${p}`);
                    break;
                }
            } catch (e) {
                console.warn(`Gagal load dari ${p}: ${e.message}`);
            }
        }
    }

    if (!Compiler) {
        throw new Error(
            'Tidak dapat load MindAR Compiler module.\n' +
            'Pastikan mind-ar terinstall: npm install mind-ar'
        );
    }

    // Load gambar input
    console.log('Membaca gambar input...');
    let imageData;

    if (deps.loadImage) {
        // Menggunakan canvas loadImage
        const img    = await deps.loadImage(inputPath);
        const canvas = deps.createCanvas(img.width, img.height);
        const ctx    = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);
        imageData = ctx.getImageData(0, 0, img.width, img.height);
        console.log(`Gambar: ${img.width}x${img.height}`);
    } else {
        throw new Error('Canvas tidak tersedia untuk membaca gambar');
    }

    // Compile menggunakan MindAR Compiler
    console.log('Compiling marker data...');
    const compiler = new Compiler();
    await compiler.compileImageTargets([imageData], (progress) => {
        process.stdout.write(`\rProgress: ${Math.round(progress * 100)}%`);
    });
    console.log('\nCompile selesai!');

    // Export data ke buffer
    console.log('Mengekspor file .mind...');
    const exportedBuffer = await compiler.exportData();

    // Simpan ke file
    fs.writeFileSync(outputPath, Buffer.from(exportedBuffer));
    const sizeKB = (fs.statSync(outputPath).size / 1024).toFixed(1);
    console.log(`✅ Berhasil! File .mind disimpan: ${outputPath} (${sizeKB} KB)`);
}

// ─── Run ─────────────────────────────────────────────────────
compile().catch(err => {
    console.error('\n❌ Compile gagal:', err.message);
    process.exit(1);
});
