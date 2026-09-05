<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Nota - {{ $prop->nama_penyewa }}</title>
    <style>
        /* SETTING KERTAS UNTUK MESIN PRINT (A5) */
        @page {
            margin: 20px 20px 40px 20px;
        }

        /* 1. TAMPILAN DI LAYAR MONITOR (KAYAK PDF VIEWER) */
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 30px;
            background-color: #525659;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* 2. KOTAK KERTAS BUATAN DI TENGAH LAYAR */
        .kertas {
            background-color: #fff;
            width: 100%;
            max-width: 19cm;
            min-height: 13cm;
            padding: 25px 25px 40px 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }

        /* 3. RESET TAMPILAN SAAT BENAR-BENAR DIPRINT KE KERTAS FISIK / PDF */
        @media print {
            body {
                background-color: #fff;
                padding: 0;
                display: block;
            }

            .kertas {
                max-width: 100%;
                box-shadow: none;
                padding: 0;
                margin: 0;
                border: none;
                min-height: auto;
            }

            .button-container {
                display: none !important;
            }
        }

        /* DESAIN KONTEN NOTA */
        .header {
            color: #28a745;
            font-size: 20px;
            font-weight: bold;
        }

        .sub-header {
            border-bottom: 2px solid #28a745;
            margin-bottom: 8px;
            padding-bottom: 4px;
            font-size: 11px;
            color: #555;
        }

        .title {
            text-align: center;
            border: 1px solid #333;
            padding: 4px;
            font-weight: bold;
            margin-bottom: 8px;
            background-color: #f8f9fa;
            letter-spacing: 1px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table.bordered th,
        table.bordered td {
            border: 1px solid #aaa;
            padding: 4px 6px;
        }

        table.bordered th {
            background-color: #f1f1f1;
            text-align: left;
        }

        .watermark {
            position: absolute;
            top: 40%;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 38px;
            color: rgba(180, 180, 180, 0.15);
            transform: rotate(-25deg);
            z-index: 1;
            white-space: nowrap;
            font-weight: bold;
            letter-spacing: 2px;
            pointer-events: none;
        }

        .catatan-box {
            border: 1px dashed #555;
            padding: 8px;
            border-radius: 5px;
            background-color: #fafafa;
            font-size: 9px;
            page-break-inside: avoid;
        }

        .footer {
            margin-top: 25px;
            font-size: 8px;
            color: #777;
            font-style: italic;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            clear: both;
            position: relative;
            z-index: 2;
        }

        .text-success {
            color: #198754;
        }

        .text-danger {
            color: #dc3545;
        }

        /* Tombol Aksi Tunggal */
        .button-container {
            margin-top: 25px;
            display: flex;
            justify-content: center;
        }

        .btn-print {
            padding: 12px 35px;
            background: #0d6efd;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 13px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-print:hover {
            background: #0b5ed7;
        }

        .content-wrapper {
            position: relative;
            z-index: 2;
        }
    </style>
</head>

<body>
    <!-- PEMBUNGKUS "KERTAS" -->
    <div class="kertas">
        @php
            $hashRahasia = strtoupper(substr(md5($prop->id . $prop->created_at), 0, 4));
            $regNumber = 'PROP-' . sprintf('%05d', $prop->id) . '-' . $hashRahasia;

            $totalBayar = $prop->nominal_dp;
            $sisaTagihan = max(0, $prop->total_pembayaran - $totalBayar);
            $statusText = $sisaTagihan <= 0 ? 'LUNAS' : 'MENCICIL';

            $tglMulai = date('d/m/Y', strtotime($prop->created_at));
            $tglBerakhir = date(
                'd/m/Y',
                strtotime('+' . $prop->durasi_bulan . ' months', strtotime($prop->created_at)),
            );

            $validationUrl = url('/data-property/cetak/' . $prop->id);
            $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=90x90&data=' . urlencode($validationUrl);
        @endphp

        <div class="watermark">
            PANDE MESARI &bull; {{ strtoupper($prop->nama_penyewa) }} &bull; {{ $statusText }}
        </div>

        <div class="content-wrapper">
            <div class="header">PANDE MESARI</div>
            <div class="sub-header">Sewa Kontrak Properti</div>

            <div class="title">KWITANSI PEMBAYARAN</div>

            <!-- TABEL 1: INFORMASI KONTRAK -->
            <table class="bordered" style="margin-bottom: 12px; font-size: 10px;">
                <tr>
                    <td width="18%"><b>No. Registrasi:</b></td>
                    <td width="32%" style="font-family: monospace; font-size: 11px; font-weight: bold;">
                        {{ $regNumber }}
                    </td>
                    <td width="18%"><b>Nama:</b></td>
                    <td width="32%"><b>{{ $prop->nama_penyewa }}</b></td>
                </tr>
                <tr>
                    <td><b>No. WA:</b></td>
                    <td>{{ $prop->no_whatsapp ?: '-' }}</td>
                    <td><b>Luas:</b></td>
                    <td>{{ $prop->panjang }} x {{ $prop->lebar }} m ({{ $prop->panjang * $prop->lebar }} m&sup2;)</td>
                </tr>
                <tr>
                    <td><b>Mulai:</b></td>
                    <td>{{ $tglMulai }}</td>
                    <td><b>Berakhir:</b></td>
                    <td>{{ $tglBerakhir }}</td>
                </tr>
                <tr>
                    <td><b>Lama Kontrak:</b></td>
                    <td>{{ $prop->durasi_bulan }} bulan</td>
                    <td><b>Total Kontrak:</b></td>
                    <td style="font-size: 11px;"><b>Rp {{ number_format($prop->total_pembayaran, 0, ',', '.') }}</b>
                    </td>
                </tr>
            </table>

            <!-- KONTENER UTAMA (KIRI - KANAN) -->
            <div style="width: 100%;">

                <!-- BAGIAN KIRI: RIWAYAT & TOTAL -->
                <div style="width: 53%; float: left;">
                    <b style="font-size: 11px;">Riwayat Pembayaran</b>
                    <table class="bordered" style="margin-top: 4px; font-size: 10px; margin-bottom: 6px;">
                        <thead>
                            <tr>
                                <th width="10%" style="text-align: center;">No</th>
                                <th width="30%">Tanggal</th>
                                <th width="35%">Jumlah</th>
                                <th width="25%">Metode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @forelse($histori as $pay)
                                <tr>
                                    <td style="text-align: center;">{{ $no++ }}</td>
                                    <td>{{ date('d/m/Y', strtotime($pay->created_at)) }}</td>
                                    <td style="font-weight: bold;">Rp
                                        {{ number_format($pay->nominal_bayar, 0, ',', '.') }}</td>
                                    <td>{{ $no == 2 && $prop->metode_dp ? strtoupper($prop->metode_dp) : 'CASH/TRF' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; font-style: italic; color: #777;">
                                        Belum ada pembayaran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div style="page-break-inside: avoid;">
                        <table style="width: 100%; font-size: 11px; border: none;">
                            <tr>
                                <td style="width: 50%; text-align: right; padding-right: 10px; border: none;"><b>Total
                                        Dibayar:</b></td>
                                <td style="width: 50%; border: none;"><b>Rp
                                        {{ number_format($totalBayar, 0, ',', '.') }}</b>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: right; padding-right: 10px; border: none;"><b>Sisa Tagihan:</b>
                                </td>
                                <td style="font-size: 12px; border: none;">
                                    @if ($sisaTagihan <= 0)
                                        <span class="text-success" style="font-weight: bold;">LUNAS</span>
                                    @else
                                        <span class="text-danger" style="font-weight: bold;">Rp
                                            {{ number_format($sisaTagihan, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- BAGIAN KANAN: CATATAN & QR CODE -->
                <div style="width: 45%; float: right;">
                    <div class="catatan-box">
                        <table style="width: 100%; border: none; margin: 0; padding: 0;">
                            <tr>
                                <td style="border: none; padding: 0; vertical-align: top;">
                                    <b style="font-size: 10px;">Catatan Tambahan:</b>
                                    <div style="margin-top: 2px; margin-bottom: 4px;">
                                        {{ $prop->catatan ?: '-' }}
                                    </div>
                                    <b style="font-size: 10px;">Ketentuan:</b>
                                    <ul style="padding-left: 12px; margin-top: 3px; margin-bottom: 0;">
                                        <li>Uang yang diterima tidak bisa ditarik.</li>
                                        <li>Aturan pemilik tanah:
                                            <ol style="padding-left: 12px; margin-top: 2px; margin-bottom: 2px;">
                                                <li>Dilarang minum alkohol / mabuk.</li>
                                                <li>Wajib menjaga kebersihan.</li>
                                                <li>Menjaga keamanan & ketertiban.</li>
                                                <li>Musik maksimal jam 22:00.</li>
                                            </ol>
                                        </li>
                                        <li>KTP non-Badung wajib memiliki IKTS.</li>
                                        <li>Tunduk aturan Adat & Pemerintah.</li>
                                    </ul>
                                </td>
                                <td
                                    style="width: 65px; border: none; padding: 0; padding-left: 5px; vertical-align: top; text-align: center;">
                                    <img src="{{ $qrApiUrl }}" alt="QR Code"
                                        style="width: 60px; height: 60px; border: 1px solid #ccc; background: white; padding: 2px;"><br>
                                    <span style="font-size: 7px; font-weight: bold;">SCAN KEASLIAN</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Pembersih kolom kiri-kanan -->
                <div style="clear: both;"></div>
            </div>

            <!-- FOOTER KEAMANAN -->
            <div class="footer">
                Dokumen PDF ini dicetak dan diamankan secara otomatis oleh Sistem Pande Mesari. <br>
                Kode Verifikasi: <b>{{ $regNumber }}</b> | Waktu Cetak Server: <b>{{ date('d F Y, H:i:s') }}
                    WITA</b>
            </div>

        </div>
    </div>

    <!-- 1 Tombol Aksi yang Bersih -->
    <div class="button-container">
        <button class="btn-print" onclick="window.print()">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

</body>

</html>
