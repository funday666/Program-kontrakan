<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tambah kolom pencatat di tabel pemasukan (cicilan/lunas)
        Schema::table('payment_details', function (Blueprint $table) {
            $table->string('created_by')->nullable()->after('amount_paid');
        });

        // Tambah kolom pencatat di tabel pengeluaran
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('created_by')->nullable()->after('description');
        });
    }

    public function down()
    {
        Schema::table('payment_details', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }
};