<!DOCTYPE html>
<html>

<head>
    <title>Laporan Keuangan</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            padding: 0;
        }

        .header p {
            margin: 5px 0;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background-color: #e9ecef;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-success {
            color: green;
        }

        .text-danger {
            color: red;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
            background-color: #343a40;
            color: white;
            padding: 5px;
        }

        .summary-box {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>LAPORAN KEUANGAN PANDE MESARI</h2>
        <p>
            Periode:
            @if ($startDate || $endDate)
                {{ $startDate ? date('d-m-Y', strtotime($startDate)) : 'Awal' }} s/d
                {{ $endDate ? date('d-m-Y', strtotime($endDate)) : 'Sekarang' }}
            @else
                Keseluruhan (All-Time)
            @endif
        </p>
    </div>

    <div class="summary-box">
        <strong>RANGKUMAN SALDO SAAT INI (ALL-TIME):</strong><br>
        - Saldo Cash (Tunai Laci): Rp {{ number_format($saldoCash, 0, ',', '.') }}<br>
        - Saldo Bank (Transfer): Rp {{ number_format($saldoTransfer, 0, ',', '.') }}<br>
        - Piutang Belum Dibayar: Rp {{ number_format($totalPiutangSeluruh, 0, ',', '.') }}
    </div>

    <!-- TABEL 1: PEMASUKAN -->
    <div class="section-title">HISTORI PEMASUKAN (SETORAN SEWA)</div>
    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Tanggal</th>
                <th>Nama Penyewa</th>
                <th>Metode</th>
                <th>Dicatat Oleh</th>
                <th class="text-right">Nominal Masuk</th>
            </tr>
        </thead>
        <tbody>
            @php $totalMasuk = 0; @endphp
            @forelse($historiPemasukan as $index => $in)
                @php $totalMasuk += $in->amount_paid; @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ date('d-m-Y', strtotime($in->payment_date)) }}</td>
                    <td>{{ $in->tenant_name }}</td>
                    <td>{{ $in->payment_method ?? 'Cash' }}</td>
                    <td>{{ $in->created_by ?? 'Admin' }}</td>
                    <td class="text-right text-success">+ Rp {{ number_format($in->amount_paid, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada transaksi pemasukan pada periode ini.</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="5" class="text-right" style="font-weight:bold;">TOTAL PEMASUKAN:</td>
                <td class="text-right" style="font-weight:bold;">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- TABEL 2: PENGELUARAN -->
    <div class="section-title">HISTORI PENGELUARAN (PENGGUNAAN DANA)</div>
    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Tanggal</th>
                <th>Kategori (Bagi Hasil)</th>
                <th>Keterangan</th>
                <th>Sumber Dana</th>
                <th class="text-right">Nominal Keluar</th>
            </tr>
        </thead>
        <tbody>
            @php $totalKeluar = 0; @endphp
            @forelse($historiPengeluaran as $index => $exp)
                @php $totalKeluar += $exp->amount; @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ date('d-m-Y', strtotime($exp->created_at)) }}</td>
                    <td>{{ $exp->category_name }}</td>
                    <td>{{ $exp->description }}</td>
                    <td>{{ $exp->payment_method ?? 'Cash' }}</td>
                    <td class="text-right text-danger">- Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada transaksi pengeluaran pada periode ini.</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="5" class="text-right" style="font-weight:bold;">TOTAL PENGELUARAN:</td>
                <td class="text-right" style="font-weight:bold;">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- TABEL 3: MUTASI DANA -->
    <div class="section-title">HISTORI MUTASI (PINDAH DANA TARIK/SETOR)</div>
    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Tanggal & Waktu</th>
                <th>Jenis Mutasi</th>
                <th>Keterangan</th>
                <th class="text-right">Nominal Dipindahkan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($historiMutasi as $index => $mutasi)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ date('d-m-Y H:i', strtotime($mutasi->created_at)) }}</td>
                    <td>{{ $mutasi->type }}</td>
                    <td>{{ $mutasi->description }}</td>
                    <td class="text-right">Rp {{ number_format($mutasi->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada transaksi mutasi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right;">
        Dicetak pada: {{ date('d-m-Y H:i:s') }}
    </div>
</body>

</html>
