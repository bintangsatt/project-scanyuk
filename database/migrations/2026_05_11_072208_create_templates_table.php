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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('model_path');              // Path ke file .glb template
            $table->string('thumbnail')->nullable();   // Path ke thumbnail gambar
            $table->json('config_schema')->nullable(); // Schema form input (name, label, type)
            $table->json('placeholders')->nullable();  // Mapping placeholder ke object name di GLB
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
