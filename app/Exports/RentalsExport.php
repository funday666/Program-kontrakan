<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RentalsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
    * Ambil data dari database rentals
    */
    public function collection()
    {
        return DB::table('rentals')->orderBy('id', 'desc')->get();
    }

    /**
    * Mengatur Header / Judul Kolom di Excel paling atas
    */
    public function headings(): array
    {
        return [
            'ID Sewa',
            'Nama Penyewa',
            'Nomor HP',
            'Panjang Lahan (m)',
            'Lebar Lahan (m)',
            'Luas Lahan (m²)',
            'Durasi Kontrak (Bulan)',
            'Harga per m²/Tahun',
            'Total Nilai Kontrak',
            'Metode Pembayaran',
            'Status Pembayaran',
            'Tanggal Kontrak Dibuat'
        ];
    }

    /**
    * Memetakan data dari database ke kolom Excel (Formatting angka & teks)
    */
    public function map($row): array
    {
        // Hitung luas lahan
        $luas = $row->rented_length * $row->rented_width;
        
        // Format metode pembayaran agar lebih rapi dibaca manusia
        $metode = $row->payment_type == 'lunas_awal' ? 'Lunas di Awal' : 'Cicilan Bulanan';
        
        // Format status pembayaran
        $status = 'Belum Bayar';
        if ($row->payment_status == 'lunas') {
            $status = 'Lunas';
        } elseif ($row->payment_status == 'mencicil') {
            $status = 'Mencicil';
        }

        return [
            '#SR-00' . $row->id,
            $row->tenant_name,
            "'" . $row->tenant_phone, // Ditambah petik satu (') agar nomor HP nol (0) di awal tidak hilang di Excel
            $row->rented_length,
            $row->rented_width,
            $luas,
            $row->contract_duration_months,
            $row->price_per_meter_year,
            $row->total_price,
            $metode,
            $status,
            date('d-m-Y H:i', strtotime($row->created_at))
        ];
    }
}