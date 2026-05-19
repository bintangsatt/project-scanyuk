<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ar_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marker_id')->nullable()->constrained('markers')->nullOnDelete();
            $table->enum('type', ['template', 'gltf', 'blend']); // Mode: template, GLB/GLTF, atau Blend
            $table->foreignId('template_id')->nullable()->constrained('templates')->nullOnDelete();
            $table->string('model_path')->nullable();      // Untuk mode gltf atau blend (GLB hasil konversi)
            $table->json('config')->nullable();            // Konfigurasi teks dari form
            $table->float('scale')->default(1.0);         // Skala model di AR
            $table->json('position')->nullable();          // Posisi [x,y,z]
            $table->json('rotation')->nullable();          // Rotasi [x,y,z]
            $table->string('status')->default('ready');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ar_projects');
    }
};
