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
        Schema::table('services', function (Blueprint $table) {
            // Tambah kolom image (nullable) setelah name
            $table->string('image')->nullable()->after('name');
            // Tambah kolom category (enum) setelah image
            $table->enum('category', ['potong', 'grooming', 'perawatan', 'warna'])
                  ->default('potong')
                  ->after('image');
            // Tambah kolom is_active (boolean) setelah price, default true
            $table->boolean('is_active')->default(true)->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['image', 'category', 'is_active']);
        });
    }
};