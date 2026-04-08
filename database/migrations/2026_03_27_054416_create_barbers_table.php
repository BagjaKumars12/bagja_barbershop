<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('barbers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('specialties')->nullable(); // kombo, e.g. "Fade, Classic Cut"
            $table->decimal('rating', 2, 1)->default(0.0); // e.g. 4.9
            $table->integer('jobs_count')->default(0);
            $table->integer('experience_years')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barbers');
    }
};
