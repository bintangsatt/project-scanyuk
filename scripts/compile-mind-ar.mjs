#!/usr/bin/env node

/**
 * compile-mind-ar.mjs
 * MindAR Offline Compiler CLI untuk Node.js (CachyOS / Linux)
 *
 * Prerequisite:
 *   npm install mind-ar @napi-rs/canvas
 *   (di package.json tambahkan overrides: { "canvas": "npm:@napi-rs/canvas@latest" })
 *
 * Usage:
 *   node compile-mind-ar.mjs <imagePath> <outputPath>
 *
 * Contoh:
 *   node compile-mind-ar.mjs ./marker.jpg ./storage/app/public/targets/marker.mind
 */

import fs   from 'fs';
import path from 'path';

// Import compiler dan loadImage dari @napi-rs/canvas (via alias 'canvas')
import { loadImage } from 'canvas';
import { OfflineCompiler } from './offline-compiler.js';

// ── Harus load kernel CPU SEBELUM compile ──────────────────────────────────
// Ini yang sering terlewat: CompilerBase memakai TF.js detector
// yang butuh kernel terdaftar. Di browser dilakukan oleh bundler,
// di Node.js harus di-import manual.
import 'mind-ar/src/image-target/detector/kernels/cpu/index.js';

// ── Validasi argumen CLI ───────────────────────────────────────────────────
const [,, imagePath, outputPath] = process.argv;

if (!imagePath || !outputPath) {
  console.error('\x1b[31mError: Argumen tidak lengkap.\x1b[0m');
  console.log('Usage  : node compile-mind-ar.mjs <imagePath> <outputPath>');
  console.log('Contoh : node compile-mind-ar.mjs ./marker.jpg ./targets/marker.mind');
  process.exit(1);
}

const resolvedInput  = path.resolve(imagePath);
const resolvedOutput = path.resolve(outputPath);

if (!fs.existsSync(resolvedInput)) {
  console.error(`\x1b[31mError: File tidak ditemukan: ${resolvedInput}\x1b[0m`);
  process.exit(1);
}

// Pastikan direktori output ada
fs.mkdirSync(path.dirname(resolvedOutput), { recursive: true });

// ── Main ───────────────────────────────────────────────────────────────────
async function run() {
  console.log('\x1b[36m--- MindAR Offline Compiler ---\x1b[0m');
  console.log(`Input  : ${resolvedInput}`);
  console.log(`Output : ${resolvedOutput}`);

  // 1. Load gambar menggunakan @napi-rs/canvas
  console.log('\nMemuat gambar...');
  const img = await loadImage(resolvedInput);
  console.log(`Resolusi: ${img.width}x${img.height}`);

  if (img.width === 0 || img.height === 0) {
    console.error('\x1b[31mError: Gambar tidak terbaca (dimensi 0x0).\x1b[0m');
    process.exit(1);
  }

  // 2. Inisialisasi compiler
  const compiler = new OfflineCompiler();

  // 3. Compile — CompilerBase.compileImageTargets() handle semua tahapan:
  //    a. Konversi ke greyscale
  //    b. buildImageList → matchingData (progress 0-50%)
  //    c. this.compileTrack() → trackingData (progress 50-100%)
  //    d. Simpan ke this.data[] dengan struktur yang benar
  console.log('\nMemulai kompilasi (bisa memakan 30-120 detik)...');
  console.log('Progress:');

  await compiler.compileImageTargets([img], (progress) => {
    const bar  = '█'.repeat(Math.floor(progress / 5)).padEnd(20, '░');
    process.stdout.write(`\r  [${bar}] ${Math.round(progress)}%  `);
  });

  process.stdout.write('\n');

  // 4. Export ke binary MessagePack (.mind)
  //    exportData() membaca this.data[] yang sudah diisi compileImageTargets()
  console.log('\nMengekspor ke format .mind (MessagePack)...');
  const buffer = compiler.exportData(); // returns Uint8Array

  // 5. Verifikasi sebelum tulis
  if (!buffer || buffer.length < 100) {
    console.error('\x1b[31mError: Export gagal — buffer terlalu kecil.\x1b[0m');
    console.error('Kemungkinan penyebab: gambar terlalu polos/seragam, kurang fitur visual.');
    process.exit(1);
  }

  // 6. Tulis ke file
  fs.writeFileSync(resolvedOutput, Buffer.from(buffer));
  const sizeKB = (fs.statSync(resolvedOutput).size / 1024).toFixed(1);

  console.log(`\x1b[32m\nBerhasil!\x1b[0m File .mind dibuat: ${path.basename(resolvedOutput)} (${sizeKB} KB)`);
  console.log('File siap digunakan dengan MindAR.js di browser.');
}

run().catch((err) => {
  process.stdout.write('\n');
  console.error(`\x1b[31mError fatal: ${err.message}\x1b[0m`);

  if (err.message.includes('canvas')) {
    console.log('\nTip: Pastikan @napi-rs/canvas sudah diinstall dan overrides sudah diset di package.json:');
    console.log('  "overrides": { "canvas": "npm:@napi-rs/canvas@latest" }');
  }
  if (err.message.includes('kernel') || err.message.includes('tfjs')) {
    console.log('\nTip: Import CPU kernel wajib ada:');
    console.log('  import \'mind-ar/src/image-target/detector/kernels/cpu/index.js\'');
  }
  process.exit(1);
});
