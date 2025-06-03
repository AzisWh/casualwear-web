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
        Schema::table('transaction', function (Blueprint $table) {
            // Hapus kolom expired_at yang sudah ada (timestamp)
            $table->dropColumn('expired_at');
        });

        Schema::table('transaction', function (Blueprint $table) {
            // Tambahkan kolom expired_at dengan tipe datetime
            $table->dateTime('expired_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction', function (Blueprint $table) {
            // Hapus kolom expired_at (datetime)
            $table->dropColumn('expired_at');
        });

        Schema::table('transaction', function (Blueprint $table) {
            // Kembalikan ke timestamp jika rollback
            $table->timestamp('expired_at')->nullable()->after('status');
        });
    }
};
