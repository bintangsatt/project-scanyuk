<?php

namespace App\Jobs;

use App\Models\Marker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CompileMindARJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan ulang jika job gagal.
     * Karena ini masalah resource, 3 kali sudah cukup.
     */
    public int $tries = 3;

    /**
     * Timeout job dalam detik. 
     * Disesuaikan dengan kebutuhan MindAR yang berat (5 menit).
     */
    public int $timeout = 3000;

    public function __construct(
        public readonly Marker $marker
    ) {}

    /**
     * Eksekusi job compile marker
     */
    public function handle(): void
    {
        Log::info("CompileMindARJob: Memulai proses untuk marker ID {$this->marker->id}");

        try {
            // 1. Persiapan Path
            $imagePath = Storage::disk('public')->path(
                str_replace('public/', '', $this->marker->image_path)
            );

            if (!file_exists($imagePath)) {
                throw new \RuntimeException("File gambar tidak ditemukan di path: {$imagePath}");
            }

            $mindFilename = 'targets/' . pathinfo($imagePath, PATHINFO_FILENAME) . '_' . $this->marker->id . '.mind';
            $mindPath     = Storage::disk('public')->path($mindFilename);

            Storage::disk('public')->makeDirectory('targets');

            // 2. Definisi Command menggunakan Symfony Process
            // Ini lebih aman dan efisien daripada menggunakan exec()
            $scriptPath = base_path('scripts/compile-mind-ar.mjs');
            
            $process = new Process([
                'node', 
                $scriptPath, 
                $imagePath, 
                $mindPath
            ]);

            // Set timeout proses agar sinkron dengan timeout job
            $process->setTimeout($this->timeout);

            Log::info("CompileMindARJob: Menjalankan command: " . $process->getCommandLine());

            // 3. Eksekusi Proses
            // mustRun() akan melempar ProcessFailedException jika exit code != 0
            $process->mustRun(function ($type, $buffer) {
                // Log output secara real-time ke laravel.log untuk debugging
                Log::debug("MindAR-Compiler: " . $buffer);
            });

            // 4. Verifikasi Hasil
            if (!file_exists($mindPath)) {
                throw new \RuntimeException("Perintah Node selesai tapi file .mind tidak ditemukan.");
            }

            // 5. Update Database
            $this->marker->update([
                'status'        => Marker::STATUS_READY,
                'mind_path'     => 'public/' . $mindFilename,
                'error_message' => null,
            ]);

            Log::info("CompileMindARJob: Selesai! Marker ID {$this->marker->id} berhasil dikompilasi.");

        } catch (ProcessFailedException $e) {
            $errorOutput = $e->getProcess()->getErrorOutput() ?: $e->getProcess()->getOutput();
            $this->handleFailure("Node Process Error: " . $errorOutput);
        } catch (\Throwable $e) {
            $this->handleFailure($e->getMessage());
        }
    }

    /**
     * Helper untuk menangani kegagalan di dalam handle
     */
    private function handleFailure(string $message): void
    {
        Log::error("CompileMindARJob Gagal: " . $message);

        $this->marker->update([
            'status'        => Marker::STATUS_FAILED,
            'error_message' => $message,
        ]);
    }

    /**
     * Dipanggil jika job benar-benar gagal setelah semua retry habis
     */
    public function failed(\Throwable $exception): void
    {
        $this->handleFailure("Final Job Failure: " . $exception->getMessage());
    }
}