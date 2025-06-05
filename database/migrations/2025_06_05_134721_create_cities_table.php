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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('city_id')->unique();
            $table->string('province_id');
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('postal_code')->nullable();
            $table->timestamps();

            $table->foreign('province_id')->references('province_id')->on('provinces')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
