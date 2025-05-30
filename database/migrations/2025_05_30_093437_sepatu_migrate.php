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
        Schema::create('sepatu', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('size');
            $table->foreignId('id_kat') ->constrained('kategori_sepatu') ->onDelete('cascade');
            $table->string('image_sepatu');
            $table->text('deskripsi');
            $table->integer('stok');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sepatu');
    }
};
