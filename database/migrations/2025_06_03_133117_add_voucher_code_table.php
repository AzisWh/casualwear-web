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
        Schema::create('voucherlist', function (Blueprint $table) {
            $table->id(); 
            $table->string('code')->unique(); 
            $table->decimal('discount_value', 8, 2); 
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage'); 
            $table->dateTime('start_date'); 
            $table->dateTime('end_date'); 
            $table->integer('max_usage')->nullable()->default(null); 
            $table->integer('used_count')->default(0);
            $table->boolean('is_active')->default(true); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucherlist');
    }
};
