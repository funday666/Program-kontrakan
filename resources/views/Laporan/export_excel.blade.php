<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Export Excel</title>
</head>

<body>
    <table style="border-collapse: collapse;">
        <tbody>
            <tr>
                <td colspan="6" style="font-weight: bold; text-align: center; font-size: 14px;">LAPORAN KEUANGAN PANDE
                    MESARI</td>
            </tr>
            <tr>
                <td colspan="6" style="text-align: center;">
                    Periode: {{ $startDate ? date('d-m-Y', strtotime($startDate)) : 'Awal' }} s/d
                    {{ $endDate ? date('d-m-Y', strtotime($endDate)) : 'Sekarang' }}
                </td>
            </tr>
            <tr>
                <td colspan="6"></td>
            </tr>

            <tr>
                <td colspan="6" style="font-weight: bold;">RANGKUMAN SALDO SAAT INI (ALL-TIME)</td>
            </tr>
            <tr>
                <td colspan="6">Saldo Cash (Tunai Laci): {{ $saldoCash }}</td>
            </tr>
            <tr>
                <td colspan="6">Saldo Bank (Transfer): {{ $saldoTransfer }}</td>
            </tr>
            <tr>
                <td colspan="6">Piutang Belum Dibayar: {{ $totalPiutangSeluruh }}</td>
            </tr>
            <tr>
                <td colspan="6"></td>
            </tr>

            <tr>
                <td colspan="6" style="font-weight: bold; background-color: #d3d3d3;">1. HISTORI PEMASUKAN (SETORAN
                    SEWA)</td>
            </tr>
            <tr>
                <td style="font-weight: bold; border: 1px solid #000;">No</td>
                <td style="font-weight: bold; border: 1px solid #000;">Tanggal</td>
                <td style="font-weight: bold; border: 1px solid #000;">Nama Penyewa</td>
                <td style="font-weight: bold; border: 1px solid #000;">Metode</td>
                <td style="font-weight: bold; border: 1px solid #000;">Dicatat Oleh</td>
                <td style="font-weight: bold; border: 1px solid #000;">Nominal Masuk (Rp)</td>
            </tr>
            @php $totalMasuk = 0; @endphp
            @forelse($historiPemasukan as $index => $in)
                @php $totalMasuk += $in->amount_paid; @endphp
                <tr>
                    <td style="border: 1px solid #000;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid #000;">{{ date('d-m-Y', strtotime($in->payment_date)) }}</td>
                    <td style="border: 1px solid #000;">{{ $in->tenant_name }}</td>
                    <td style="border: 1px solid #000;">{{ $in->payment_method ?? 'Cash' }}</td>
                    <td style="border: 1px solid #000;">{{ $in->created_by ?? 'Admin' }}</td>
                    <td style="border: 1px solid #000;">{{ $in->amount_paid }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="border: 1px solid #000; text-align: center;">Tidak ada transaksi
                        pemasukan.</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="5" style="font-weight: bold; text-align: right; border: 1px solid #000;">TOTAL
                    PEMASUKAN:</td>
                <td style="font-weight: bold; border: 1px solid #000;">{{ $totalMasuk }}</td>
            </tr>

            <tr>
                <td colspan="6"></td>
            </tr>

            <tr>
                <td colspan="6" style="font-weight: bold; background-color: #d3d3d3;">2. HISTORI PENGELUARAN
                    (PENGGUNAAN DANA)</td>
            </tr>
            <tr>
                <td style="font-weight: bold; border: 1px solid #000;">No</td>
                <td style="font-weight: bold; border: 1px solid #000;">Tanggal</td>
                <td style="font-weight: bold; border: 1px solid #000;">Kategori</td>
                <td style="font-weight: bold; border: 1px solid #000;">Keterangan</td>
                <td style="font-weight: bold; border: 1px solid #000;">Sumber Dana</td>
                <td style="font-weight: bold; border: 1px solid #000;">Nominal Keluar (Rp)</td>
            </tr>
            @php $totalKeluar = 0; @endphp
            @forelse($historiPengeluaran as $index => $exp)
                @php $totalKeluar += $exp->amount; @endphp
                <tr>
                    <td style="border: 1px solid #000;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid #000;">{{ date('d-m-Y', strtotime($exp->created_at)) }}</td>
                    <td style="border: 1px solid #000;">{{ $exp->category_name }}</td>
                    <td style="border: 1px solid #000;">{{ $exp->description }}</td>
                    <td style="border: 1px solid #000;">{{ $exp->payment_method ?? 'Cash' }}</td>
                    <td style="border: 1px solid #000;">{{ $exp->amount }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="border: 1px solid #000; text-align: center;">Tidak ada transaksi
                        pengeluaran.</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="5" style="font-weight: bold; text-align: right; border: 1px solid #000;">TOTAL
                    PENGELUARAN:</td>
                <td style="font-weight: bold; border: 1px solid #000;">{{ $totalKeluar }}</td>
            </tr>

            <tr>
                <td colspan="6"></td>
            </tr>

            <tr>
                <td colspan="6" style="font-weight: bold; background-color: #d3d3d3;">3. HISTORI MUTASI (PINDAH DANA
                    TARIK/SETOR)</td>
            </tr>
            <tr>
                <td style="font-weight: bold; border: 1px solid #000;">No</td>
                <td style="font-weight: bold; border: 1px solid #000;">Tanggal dan Waktu</td>
                <td style="font-weight: bold; border: 1px solid #000;">Jenis Mutasi</td>
                <td style="font-weight: bold; border: 1px solid #000;">Keterangan</td>
                <td colspan="2" style="font-weight: bold; border: 1px solid #000;">Nominal Dipindahkan (Rp)</td>
            </tr>
            @forelse($historiMutasi as $index => $mutasi)
                <tr>
                    <td style="border: 1px solid #000;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid #000;">{{ date('d-m-Y H:i', strtotime($mutasi->created_at)) }}</td>
                    <td style="border: 1px solid #000;">{{ $mutasi->type }}</td>
                    <td style="border: 1px solid #000;">{{ $mutasi->description }}</td>
                    <td colspan="2" style="border: 1px solid #000;">{{ $mutasi->amount }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="border: 1px solid #000; text-align: center;">Tidak ada transaksi mutasi.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
