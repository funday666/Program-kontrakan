<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LaporanKeuanganExport implements FromView, ShouldAutoSize
{
    protected $data;

    // Menerima data dari routes/web.php
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        // Ajaib: Memanggil file 'export.blade.php' yang sama persis dengan PDF!
        return view('laporan.export', $this->data);
    }
}