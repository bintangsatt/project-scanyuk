import { createCanvas } from 'canvas'; // alias ke @napi-rs/canvas via package.json overrides
import { buildTrackingImageList } from 'mind-ar/src/image-target/image-list.js';
import { extractTrackingFeatures } from 'mind-ar/src/image-target/tracker/extract-utils.js';
import { CompilerBase } from 'mind-ar/src/image-target/compiler-base.js';

// Polyfill document.createElement('canvas') untuk Node.js
// Dibutuhkan oleh mind-ar saat load TF.js dan detector kernels
if (typeof global.document === 'undefined') {
  global.document = {
    createElement: (type) => (type === 'canvas' ? createCanvas(1, 1) : {}),
  };
}

/**
 * OfflineCompiler — wrapper CompilerBase untuk lingkungan Node.js
 *
 * PENTING: Jangan override compileImageTargets()!
 * CompilerBase.compileImageTargets() sudah menangani:
 *   - konversi image → greyscale
 *   - ekstraksi matchingData (50% pertama)
 *   - memanggil this.compileTrack() (50% kedua)
 *   - menyimpan hasil ke this.data[] dengan struktur yang benar
 *
 * Kita hanya perlu override dua method:
 *   1. createProcessCanvas(img) → buat canvas dari @napi-rs/canvas
 *   2. compileTrack(...)        → ekstraksi tracking features
 */
export class OfflineCompiler extends CompilerBase {
  constructor() {
    super();
  }

  /**
   * Override wajib #1: buat canvas untuk memproses gambar.
   * CompilerBase akan memanggil ini untuk setiap gambar input.
   *
   * @param {object} img - objek dengan .width dan .height
   * @returns {Canvas} canvas dari @napi-rs/canvas
   */
  createProcessCanvas(img) {
    return createCanvas(img.width, img.height);
  }

  /**
   * Override wajib #2: ekstraksi tracking features.
   * Dipanggil oleh CompilerBase.compileImageTargets() setelah
   * matchingData selesai (progress 50-100%).
   *
   * Harus return: Promise<Array[]> — satu array trackingData per gambar.
   *
   * @param {Function} progressCallback
   * @param {Array}    targetImages - array {data, width, height} greyscale
   * @param {number}   basePercent  - progress awal (50 dari CompilerBase)
   */
  compileTrack({ progressCallback, targetImages, basePercent }) {
    return new Promise((resolve, reject) => {
      try {
        const percentPerImage = (100 - basePercent) / targetImages.length;
        let percent = 0;
        const list = [];

        for (let i = 0; i < targetImages.length; i++) {
          const targetImage = targetImages[i];
          const imageList   = buildTrackingImageList(targetImage);
          const percentPerAction = percentPerImage / (imageList.length || 1);

          const trackingData = extractTrackingFeatures(imageList, () => {
            percent += percentPerAction;
            progressCallback(basePercent + percent);
          });

          list.push(trackingData);
        }

        // Return array — satu element per gambar
        // CompilerBase akan simpan ini ke this.data[i].trackingData
        resolve(list);
      } catch (err) {
        reject(err);
      }
    });
  }
}
