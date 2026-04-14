<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_method')->default('cash')->after('change_amount');
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'qris'])->default('cash')->after('change_amount');
        });
    }
};