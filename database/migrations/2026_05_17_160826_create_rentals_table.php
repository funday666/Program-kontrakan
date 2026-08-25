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
            Schema::create('rentals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id'); // Hubungan ke properti
                
                // PASTIKAN BARIS-BARIS INI SUDAH TERTULIS:
                $table->string('tenant_name');
                $table->string('tenant_phone');
                $table->decimal('rented_length', 8, 2);
                $table->decimal('rented_width', 8, 2);
                $table->integer('contract_duration_months');
                $table->decimal('price_per_meter_year', 12, 2);
                $table->decimal('total_price', 12, 2);
                $table->string('payment_type');
                $table->string('payment_status');
                
                $table->timestamps();
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
