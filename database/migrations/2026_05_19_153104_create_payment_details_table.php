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
            Schema::create('payment_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rental_id')->constrained('rentals')->onDelete('cascade'); // Hubungan ke tabel rentals
                $table->integer('month_number'); // Bulan ke-1, bulan ke-2, dst.
                $table->boolean('is_paid')->default(false); // Status checklist (true = lunas, false = belum)
                $table->timestamps();
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_details');
    }
};
