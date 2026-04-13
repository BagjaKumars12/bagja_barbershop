<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_name');               // nama user yang melakukan aksi
            $table->string('user_role');               // admin/kasir/owner
            $table->string('action');                  // CREATE, UPDATE, DELETE, LOGIN, etc
            $table->string('module');                  // Transaction, Customer, Barber, Service, User
            $table->text('description')->nullable();   // detail aktivitas
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
};