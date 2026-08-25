<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tambah kolom metode bayar di tabel pengeluaran
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('amount');
        });

        // 2. Buat tabel mutasi (tarik/setor tunai)
        Schema::create('balance_mutations', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'Tarik Tunai' (Bank ke Cash) atau 'Setor Tunai' (Cash ke Bank)
            $table->integer('amount');
            $table->string('description')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
        Schema::dropIfExists('balance_mutations');
    }
};