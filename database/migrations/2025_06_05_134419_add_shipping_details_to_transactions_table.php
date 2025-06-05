<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transaction', function (Blueprint $table) {
            $table->string('origin')->nullable()->after('snap_token');
            $table->string('destination')->nullable()->after('origin');
            $table->string('courier')->nullable()->after('destination');
            $table->decimal('shipping_cost', 15, 2)->nullable()->after('courier');
            $table->string('service')->nullable()->after('shipping_cost');
        });
    }

    public function down()
    {
        Schema::table('transaction', function (Blueprint $table) {
            $table->dropColumn(['origin', 'destination', 'courier', 'shipping_cost', 'service']);
        });
    }
};
