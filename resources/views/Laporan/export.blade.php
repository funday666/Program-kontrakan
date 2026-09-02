@php
    use Carbon\Carbon;
    Carbon::setLocale('id'); 

    $groupedPemasukan = collect($historiPemasukan)->groupBy(function($item) {
        return Carbon::parse($item->payment_date)->translatedFormat('F Y');
    });
    $groupedPengeluaran = collect($historiPengeluaran)->groupBy(function($item) {
        return Carbon::parse($item->created_at)->translatedFormat('F Y');
    });
    $groupedMutasi = collect($historiMutasi)->groupBy(function($item) {
        return Carbon::parse($item->created_at)->translatedFormat('F Y');
    });

    $periodeTeks = ($startDate && $endDate) ? date('d/m/Y', strtotime($startDate)) . ' s/d ' . date('d/m/Y', strtotime($endDate)) : 'Keseluruhan Waktu';
@endphp

@if($isExcel)
    {{-- ========================================================== --}}
    {{-- DESAIN KHUSUS EXCEL (MURNI TABLE & TANPA SIMBOL "&")       --}}
    {{-- ========================================================== --}}
    <table>
        <!-- HEADER UTAMA -->
        <tr>
            <td colspan="6" align="center" style="font-weight: bold; font-size: 14px;">LAPORAN KEUANGAN PANDE MESARI</td>
        </tr>
        <tr>
            <td colspan="6" align="center">Periode: {{ $periodeTeks }}</td>
        </tr>
        <tr><td colspan="6"></td></tr>

        <!-- RANGKUMAN SALDO -->
        <tr><td colspan="6" style="font-weight: bold; font-size: 12px;">Rangkuman Saldo Saat Ini</td></tr>
        <tr>
            <th colspan="2" style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Total Saldo Uang Fisik (Cash)</th>
            <th colspan="2" style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Total Saldo Bank (Transfer)</th>
            <th colspan="2" style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Total Sisa Piutang (Belum Lunas)</th>
        </tr>
        <tr>
            <td colspan="2" style="border: 1px solid #000000; font-weight: bold;">Rp {{ number_format($saldoCash, 0, ',', '.') }}</td>
            <td colspan="2" style="border: 1px solid #000000; font-weight: bold;">Rp {{ number_format($saldoTransfer, 0, ',', '.') }}</td>
            <td colspan="2" style="border: 1px solid #000000; font-weight: bold;">Rp {{ number_format($totalPiutangSeluruh, 0, ',', '.') }}</td>
        </tr>
        <tr><td colspan="6"></td></tr>

        <!-- TABEL PEMASUKAN -->
        <tr><td colspan="6" style="font-weight: bold; font-size: 12px;">1. Histori Pemasukan Dana</td></tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Tanggal</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">No. Nota</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Nama Penyewa</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Metode</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Nominal Masuk</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Pencatat</th>
        </tr>
        @forelse($groupedPemasukan as $bulan => $items)
            <tr>
                <td colspan="6" style="font-weight: bold; border: 1px solid #000000; background-color: #d1e7dd;">Bulan: {{ $bulan }}</td>
            </tr>
            @foreach($items as $row)
            <tr>
                <td style="border: 1px solid #000000;">{{ date('d/m/Y', strtotime($row->payment_date)) }}</td>
                <td style="border: 1px solid #000000;">PM-{{ sprintf('%05d', $row->rental_id) }}</td>
                <td style="border: 1px solid #000000;">{{ $row->tenant_name }}</td>
                <td style="border: 1px solid #000000;">{{ $row->payment_method ?? 'Cash' }}</td>
                <td style="border: 1px solid #000000;">Rp {{ number_format($row->amount_paid, 0, ',', '.') }}</td>
                <td style="border: 1px solid #000000;">{{ $row->created_by ?? 'Admin' }}</td>
            </tr>
            @endforeach
        @empty
            <tr><td colspan="6" align="center" style="border: 1px solid #000000;">Belum ada histori pemasukan.</td></tr>
        @endforelse
        <tr><td colspan="6"></td></tr>

        <!-- TABEL PENGELUARAN -->
        <tr><td colspan="6" style="font-weight: bold; font-size: 12px;">2. Histori Pengeluaran Dana</td></tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Tanggal</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Kategori</th>
            <th colspan="2" style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Keterangan dan Sumber Dana</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Nominal Keluar</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Pencatat</th>
        </tr>
        @forelse($groupedPengeluaran as $bulan => $items)
            <tr>
                <td colspan="6" style="font-weight: bold; border: 1px solid #000000; background-color: #d1e7dd;">Bulan: {{ $bulan }}</td>
            </tr>
            @foreach($items as $row)
            <tr>
                <td style="border: 1px solid #000000;">{{ date('d/m/Y', strtotime($row->created_at)) }}</td>
                <td style="border: 1px solid #000000;">{{ $row->category_name }}</td>
                <td colspan="2" style="border: 1px solid #000000;">{{ $row->description }} ({{ $row->payment_method ?? 'Cash' }})</td>
                <td style="border: 1px solid #000000;">Rp {{ number_format($row->amount, 0, ',', '.') }}</td>
                <td style="border: 1px solid #000000;">{{ $row->created_by ?? 'Admin' }}</td>
            </tr>
            @endforeach
        @empty
            <tr><td colspan="6" align="center" style="border: 1px solid #000000;">Belum ada histori pengeluaran.</td></tr>
        @endforelse
        <tr><td colspan="6"></td></tr>

        <!-- TABEL MUTASI -->
        <tr><td colspan="6" style="font-weight: bold; font-size: 12px;">3. Histori Mutasi (Pindah Dana)</td></tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Waktu Transaksi</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Jenis Mutasi</th>
            <th colspan="2" style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Keterangan</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Nominal</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Pencatat</th>
        </tr>
        @forelse($groupedMutasi as $bulan => $items)
            <tr>
                <td colspan="6" style="font-weight: bold; border: 1px solid #000000; background-color: #d1e7dd;">Bulan: {{ $bulan }}</td>
            </tr>
            @foreach($items as $row)
            <tr>
                <td style="border: 1px solid #000000;">{{ date('d/m/Y H:i', strtotime($row->created_at)) }}</td>
                <td style="border: 1px solid #000000;">{{ $row->type }}</td>
                <td colspan="2" style="border: 1px solid #000000;">{{ $row->description }}</td>
                <td style="border: 1px solid #000000;">Rp {{ number_format($row->amount, 0, ',', '.') }}</td>
                <td style="border: 1px solid #000000;">{{ $row->created_by ?? 'Admin' }}</td>
            </tr>
            @endforeach
        @empty
            <tr><td colspan="6" align="center" style="border: 1px solid #000000;">Belum ada histori mutasi dana.</td></tr>
        @endforelse
    </table>

@else
    {{-- ========================================================== --}}
    {{-- DESAIN KHUSUS PDF (MENGGUNAKAN HTML LENGKAP & RAPI)        --}}
    {{-- ========================================================== --}}
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Laporan Keuangan Pande Mesari</title>
        <style>
            body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11pt; color: #333; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .font-bold { font-weight: bold; }
            .mb-2 { margin-bottom: 15px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
            th, td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; }
            th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
            .month-header { background-color: #d1e7dd; font-weight: bold; text-align: left; font-size: 12pt; }
            .summary-box { background-color: #e2e3e5; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="text-center mb-2">
            <h2 style="margin: 0;">LAPORAN KEUANGAN PANDE MESARI</h2>
            <p style="margin: 5px 0;">Periode: {{ $periodeTeks }}</p>
        </div>

        <!-- TABEL RANGKUMAN -->
        <h3>Rangkuman Saldo Saat Ini</h3>
        <table>
            <tr>
                <th>Total Saldo Uang Fisik (Cash)</th>
                <th>Total Saldo Bank (Transfer)</th>
                <th>Total Sisa Piutang (Belum Lunas)</th>
            </tr>
            <tr>
                <td class="text-right font-bold summary-box">Rp {{ number_format($saldoCash, 0, ',', '.') }}</td>
                <td class="text-right font-bold summary-box">Rp {{ number_format($saldoTransfer, 0, ',', '.') }}</td>
                <td class="text-right font-bold summary-box">Rp {{ number_format($totalPiutangSeluruh, 0, ',', '.') }}</td>
            </tr>
        </table>

        <!-- TABEL PEMASUKAN -->
        <h3>1. Histori Pemasukan Dana</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Tanggal</th>
                    <th style="width: 15%;">No. Nota</th>
                    <th style="width: 25%;">Nama Penyewa</th>
                    <th style="width: 15%;">Metode</th>
                    <th style="width: 15%;">Nominal</th>
                    <th style="width: 15%;">Pencatat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupedPemasukan as $bulan => $items)
                    <tr>
                        <td colspan="6" class="month-header">Bulan: {{ $bulan }}</td>
                    </tr>
                    @foreach($items as $row)
                    <tr>
                        <td class="text-center">{{ date('d/m/Y', strtotime($row->payment_date)) }}</td>
                        <td class="text-center">PM-{{ sprintf('%05d', $row->rental_id) }}</td>
                        <td>{{ $row->tenant_name }}</td>
                        <td class="text-center">{{ $row->payment_method ?? 'Cash' }}</td>
                        <td class="text-right">Rp {{ number_format($row->amount_paid, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $row->created_by ?? 'Admin' }}</td>
                    </tr>
                    @endforeach
                @empty
                    <tr><td colspan="6" class="text-center">Belum ada histori pemasukan.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- TABEL PENGELUARAN -->
        <h3>2. Histori Pengeluaran Dana</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Tanggal</th>
                    <th style="width: 20%;">Kategori</th>
                    <th style="width: 30%;">Keterangan</th>
                    <th style="width: 10%;">Sumber</th>
                    <th style="width: 15%;">Nominal</th>
                    <th style="width: 10%;">Pencatat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupedPengeluaran as $bulan => $items)
                    <tr>
                        <td colspan="6" class="month-header">Bulan: {{ $bulan }}</td>
                    </tr>
                    @foreach($items as $row)
                    <tr>
                        <td class="text-center">{{ date('d/m/Y', strtotime($row->created_at)) }}</td>
                        <td>{{ $row->category_name }}</td>
                        <td>{{ $row->description }}</td>
                        <td class="text-center">{{ $row->payment_method ?? 'Cash' }}</td>
                        <td class="text-right">Rp {{ number_format($row->amount, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $row->created_by ?? 'Admin' }}</td>
                    </tr>
                    @endforeach
                @empty
                    <tr><td colspan="6" class="text-center">Belum ada histori pengeluaran.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- TABEL MUTASI DANA -->
        <h3>3. Histori Mutasi (Pindah Dana)</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 20%;">Waktu Transaksi</th>
                    <th style="width: 25%;">Jenis Mutasi</th>
                    <th style="width: 25%;">Keterangan</th>
                    <th style="width: 15%;">Nominal</th>
                    <th style="width: 15%;">Pencatat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupedMutasi as $bulan => $items)
                    <tr>
                        <td colspan="5" class="month-header">Bulan: {{ $bulan }}</td>
                    </tr>
                    @foreach($items as $row)
                    <tr>
                        <td class="text-center">{{ date('d/m/Y H:i', strtotime($row->created_at)) }}</td>
                        <td>{{ $row->type }}</td>
                        <td>{{ $row->description }}</td>
                        <td class="text-right">Rp {{ number_format($row->amount, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $row->created_by ?? 'Admin' }}</td>
                    </tr>
                    @endforeach
                @empty
                    <tr><td colspan="5" class="text-center">Belum ada histori mutasi dana.</td></tr>
                @endforelse
            </tbody>
        </table>
    </body>
    </html>
@endif