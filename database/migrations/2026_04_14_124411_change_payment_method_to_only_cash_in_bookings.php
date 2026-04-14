<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Ubah kolom payment_method menjadi hanya cash dengan default cash
        Schema::table('bookings', function (Blueprint $table) {
            // Hapus enum lama, buat kolom baru dengan default 'cash'
            $table->dropColumn('payment_method');
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_method')->default('cash')->after('status');
        });
        // Atau jika ingin tetap menggunakan enum:
        // $table->enum('payment_method', ['cash'])->default('cash')->after('status');
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'card', 'transfer'])->default('cash')->after('status');
        });
    }
};