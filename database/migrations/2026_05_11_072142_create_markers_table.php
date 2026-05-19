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
        Schema::create('markers', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');          // Path ke file gambar asli
            $table->string('mind_path')->nullable(); // Path ke file .mind hasil compile
            $table->enum('status', ['processing', 'ready', 'failed'])->default('processing');
            $table->text('error_message')->nullable(); // Pesan error jika gagal
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('markers');
    }
};
