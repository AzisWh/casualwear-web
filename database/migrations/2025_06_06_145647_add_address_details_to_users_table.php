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
        Schema::table('users', function (Blueprint $table) {
            $table->text('alamat_tinggal')->nullable()->after('no_hp');
            $table->string('asal_kota')->nullable()->after('alamat_tinggal');
            $table->string('asal_provinsi')->nullable()->after('asal_kota');
            $table->string('kodepos')->nullable()->after('asal_provinsi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['alamat_tinggal', 'asal_kota', 'asal_provinsi', 'kodepos']);
        });
    }
};
