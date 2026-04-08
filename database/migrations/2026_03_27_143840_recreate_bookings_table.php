<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Hapus tabel jika ada (dengan cascade foreign key)
        Schema::dropIfExists('bookings');

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            // Kolom booking_code unik, bisa di-generate otomatis
            $table->string('booking_code')->unique();

            // Foreign keys
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('barber_id')->constrained()->onDelete('cascade');

            // Waktu booking
            $table->dateTime('booking_time');

            // Status dengan nilai yang diinginkan
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])
                  ->default('pending');

            // Metode pembayaran
            $table->enum('payment_method', ['cash', 'card', 'transfer'])
                  ->default('cash');

            // Catatan dan total harga
            $table->text('notes')->nullable();
            $table->decimal('total_price', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};