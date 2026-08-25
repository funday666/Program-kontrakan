<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Exports\RentalsExport;
use App\Exports\LaporanKeuanganExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

Route::get('/', function () { return view('auth.login'); });
Route::get('/login', function () { return view('auth.login'); })->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required']]);
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/laporan-keuangan');
    }
    return back()->withErrors(['email' => 'Email atau password yang Anda masukkan salah.']);
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
});

// =========================================================================
// ROUTE PUBLIK (Bisa diakses oleh Penyewa / Tanpa perlu Login Admin)
// =========================================================================
Route::get('/sewa-lahan/cetak-nota/{id}', function ($id) {
    $rental = DB::table('rentals')->where('id', $id)->first();
    
    // Keamanan tambahan: Jika ID tidak ditemukan, tampilkan error 404
    if (!$rental) {
        abort(404, 'Data nota tidak ditemukan.');
    }

    $histori = DB::table('payment_details')->where('rental_id', $id)->get();
    
    // Mengaktifkan fitur remote agar PDF bisa mengunduh gambar QR Code
    $pdf = Pdf::setOptions(['isRemoteEnabled' => true])
              ->loadView('rentals.nota', compact('rental', 'histori'))
              ->setPaper('a5', 'landscape');
              
    return $pdf->stream('Kwitansi_'.$rental->tenant_name.'.pdf');
});
// =========================================================================

// AREA KHUSUS ADMIN (Wajib Login)
Route::middleware('auth')->group(function () {

   // MENU: SEWA LAHAN
    Route::get('/sewa-lahan', function (Request $request) {
        $search = $request->input('search');
        $status = $request->input('status');
        $waktu  = $request->input('waktu'); // Menangkap filter waktu

        $subQuery = DB::table('rentals')
            ->select('rentals.*', DB::raw('(SELECT COALESCE(SUM(amount_paid), 0) FROM payment_details WHERE payment_details.rental_id = rentals.id) as total_dibayar'));

        $dataRentals = DB::query()->fromSub($subQuery, 'rentals_with_payment')
            ->when($search, function ($query, $search) {
                return $query->where('tenant_name', 'like', "%{$search}%");
            })
            ->when($status, function ($query, $status) {
                if ($status === 'lunas') {
                    return $query->whereRaw('total_dibayar >= total_price');
                } elseif ($status === 'mencicil') {
                    return $query->whereRaw('total_dibayar > 0 AND total_dibayar < total_price');
                } elseif ($status === 'belum_bayar') {
                    return $query->whereRaw('total_dibayar = 0');
                }
            })
            ->when($waktu, function ($query, $waktu) {
                // Logika Kalkulasi Tanggal Otomatis di Database (MySQL)
                if ($waktu === 'hampir_habis') {
                    // Tanggal habis = Antara hari ini sampai 30 hari ke depan
                    return $query->whereRaw('DATE_ADD(created_at, INTERVAL contract_duration_months MONTH) BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)');
                } elseif ($waktu === 'habis') {
                    // Tanggal habis = Kurang dari waktu hari ini
                    return $query->whereRaw('DATE_ADD(created_at, INTERVAL contract_duration_months MONTH) < NOW()');
                }
            })
            ->orderBy('id', 'desc') 
            ->paginate(10);
        
        foreach ($dataRentals as $rental) {
            $rental->sisa_pembayaran = $rental->total_price - $rental->total_dibayar;
            if ($rental->sisa_pembayaran <= 0) {
                $rental->status_tampilan = 'lunas';
            } elseif ($rental->total_dibayar > 0) {
                $rental->status_tampilan = 'mencicil';
            } else {
                $rental->status_tampilan = 'belum_bayar';
            }
        }
        
        $paymentDetails = DB::table('payment_details')->get(); 
        return view('rentals.index', compact('dataRentals', 'paymentDetails'));
    });

    Route::get('/sewa-lahan/export-excel', function () {
        return Excel::download(new RentalsExport, 'laporan-sewa-lahan.xlsx');
    });

    // MENU: USER
    Route::get('/users', function () {
        $dataUsers = DB::table('users')->get(); 
        return view('users.index', compact('dataUsers'));
    });

    Route::post('/manajemen-user/simpan', function (Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);
        DB::table('users')->insert([
            'name' => $request->name, 'email' => $request->email,
            'password' => bcrypt($request->password),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return redirect('/users')->with('sukses', 'Akun admin baru berhasil ditambahkan!');
    });

    Route::post('/manajemen-user/update/{id}', function (Request $request, $id) {
        $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email,' . $id]);
        $updateData = ['name' => $request->name, 'email' => $request->email, 'updated_at' => now()];
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $updateData['password'] = bcrypt($request->password);
        }
        DB::table('users')->where('id', $id)->update($updateData);
        return redirect('/users')->with('sukses', 'Data admin berhasil diperbarui!');
    });

    Route::delete('/manajemen-user/hapus/{id}', function ($id) {
        if (Auth::id() == $id) {
            return redirect('/users')->withErrors(['Gagal: Anda tidak bisa menghapus akun Anda sendiri saat sedang login.']);
        }
        DB::table('users')->where('id', $id)->delete();
        return redirect('/users')->with('sukses', 'Akun admin berhasil dihapus!');
    });

    // === LOGIKA BARU UNTUK PROSES SIMPAN KONTRAK + DP ===
    Route::post('/sewa-lahan/simpan', function (Request $request) {
        $luas = $request->rented_length * $request->rented_width;
        $totalHarga = $luas * ($request->contract_duration_months / 12) * $request->price_per_meter_year;
        $tanggalMulai = $request->start_date ? $request->start_date . ' 00:00:00' : now();

        $rentalId = DB::table('rentals')->insertGetId([
            'property_id' => 1, 
            'tenant_name' => $request->tenant_name, 
            'tenant_phone' => $request->tenant_phone, 
            'rented_length' => $request->rented_length, 
            'rented_width' => $request->rented_width, 
            'contract_duration_months' => $request->contract_duration_months,
            'price_per_meter_year' => $request->price_per_meter_year, 
            'total_price' => $totalHarga,
            'payment_status' => $request->payment_status,
            'payment_type' => '-',
            'created_at' => $tanggalMulai, 
            'updated_at' => now(),
        ]);
        
        if ($request->payment_status == 'lunas') {
            DB::table('payment_details')->insert([
                'rental_id' => $rentalId, 'payment_date' => $tanggalMulai, 
                'amount_paid' => $totalHarga, 'payment_method' => $request->initial_payment_method ?? 'Cash',
                'created_by' => Auth::user()->name, 'created_at' => now(), 'updated_at' => now()
            ]);
        } elseif ($request->payment_status == 'mencicil' && $request->filled('initial_payment')) {
            DB::table('payment_details')->insert([
                'rental_id' => $rentalId, 'payment_date' => $tanggalMulai, 
                'amount_paid' => $request->initial_payment, 'payment_method' => $request->initial_payment_method ?? 'Cash',
                'created_by' => Auth::user()->name, 'created_at' => now(), 'updated_at' => now()
            ]);
        }
        
        return redirect('/sewa-lahan')->with('sukses', 'Kontrak berhasil disimpan!');
    });

    Route::post('/sewa-lahan/update/{id}', function (Request $request, $id) {
        $luas = $request->rented_length * $request->rented_width;
        $totalHarga = $luas * ($request->contract_duration_months / 12) * $request->price_per_meter_year;
        
        DB::table('rentals')->where('id', $id)->update([
            'tenant_name' => $request->tenant_name, 
            'tenant_phone' => $request->tenant_phone,
            'rented_length' => $request->rented_length, 
            'rented_width' => $request->rented_width,
            'contract_duration_months' => $request->contract_duration_months,
            'price_per_meter_year' => $request->price_per_meter_year, 
            'payment_status' => $request->payment_status, 
            'payment_type' => '-',
            'total_price' => $totalHarga, 
            'updated_at' => now()
        ]);
        return redirect('/sewa-lahan')->with('sukses', 'Data kontrak berhasil diperbarui!');
    });

    Route::get('/sewa-lahan/hapus/{id}', function ($id) {
        DB::table('rentals')->where('id', $id)->delete();
        DB::table('payment_details')->where('rental_id', $id)->delete();
        return redirect('/sewa-lahan')->with('sukses', 'Data berhasil dihapus!');
    });

    Route::post('/sewa-lahan/simpan-cicilan/{id}', function (Request $request, $id) {
        DB::table('payment_details')->insert([
            'rental_id' => $id, 'payment_date' => $request->payment_date, 
            'amount_paid' => $request->amount_paid, 'payment_method' => $request->payment_method,
            'created_by' => Auth::user()->name, 'created_at' => now(), 'updated_at' => now()
        ]);
        return redirect('/sewa-lahan')->with('sukses', 'Setoran berhasil!');
    });

    Route::delete('/sewa-lahan/hapus-cicilan/{id}', function ($id) {
        DB::table('payment_details')->where('id', $id)->delete();
        return redirect('/sewa-lahan')->with('sukses', 'Histori setoran berhasil dihapus!');
    });

    // --- ROUTE EXPORT LAPORAN KEUANGAN (EXCEL & PDF) ---
    Route::get('/laporan-keuangan/export/{type}', function (Request $request, $type) {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $filterDate = function($query, $column) use ($startDate, $endDate) {
            if ($startDate) $query->whereDate($column, '>=', $startDate);
            if ($endDate) $query->whereDate($column, '<=', $endDate);
            return $query;
        };

        $rentals = DB::table('rentals')->get();
        $totalHargaSeluruh = $rentals->sum('total_price');
        $totalTerbayarSeluruh = DB::table('payment_details')->sum('amount_paid');
        $totalPiutangSeluruh = $rentals->sum('total_price') - DB::table('payment_details')->sum('amount_paid');

        $pemasukanCash = DB::table('payment_details')->where(function($q) { $q->where('payment_method', 'Cash')->orWhereNull('payment_method'); })->sum('amount_paid');
        $pemasukanTransfer = DB::table('payment_details')->where('payment_method', 'Transfer')->sum('amount_paid');
        $pengeluaranCash = DB::table('expenses')->where(function($q) { $q->where('payment_method', 'Cash')->orWhereNull('payment_method'); })->sum('amount');
        $pengeluaranTransfer = DB::table('expenses')->where('payment_method', 'Transfer')->sum('amount');
        $tarikTunai = DB::table('balance_mutations')->where('type', 'Tarik Tunai')->sum('amount');
        $setorTunai = DB::table('balance_mutations')->where('type', 'Setor Tunai')->sum('amount');

        $saldoCash = $pemasukanCash - $pengeluaranCash + $tarikTunai - $setorTunai;
        $saldoTransfer = $pemasukanTransfer - $pengeluaranTransfer - $tarikTunai + $setorTunai;

        $historiPengeluaran = $filterDate(DB::table('expenses')->orderBy('created_at', 'desc'), 'created_at')->get();
        $historiPemasukan = $filterDate(DB::table('payment_details')->join('rentals', 'payment_details.rental_id', '=', 'rentals.id')->select('payment_details.*', 'rentals.tenant_name')->orderBy('payment_details.payment_date', 'desc'), 'payment_details.payment_date')->get();
        $historiMutasi = $filterDate(DB::table('balance_mutations')->orderBy('created_at', 'desc'), 'created_at')->get();

        $namaFile = 'Laporan_Keuangan_Pande_Mesari_' . date('Y-m-d');

        if ($type == 'excel') {
            $isExcel = true;
            $data = compact('startDate', 'endDate', 'saldoCash', 'saldoTransfer', 'totalPiutangSeluruh', 'historiPengeluaran', 'historiPemasukan', 'historiMutasi', 'isExcel');
            return Excel::download(new \App\Exports\LaporanKeuanganExport($data), $namaFile . '.xlsx');
        } elseif ($type == 'pdf') {
            $isExcel = false;
            $data = compact('startDate', 'endDate', 'saldoCash', 'saldoTransfer', 'totalPiutangSeluruh', 'historiPengeluaran', 'historiPemasukan', 'historiMutasi', 'isExcel');
            $pdf = Pdf::loadView('laporan.export', $data)->setPaper('a4', 'portrait');
            return $pdf->stream($namaFile . '.pdf');
        }
        abort(404);
    });

    // --- LAPORAN KEUANGAN (HALAMAN INDEX) ---
    Route::get('/laporan-keuangan', function (Request $request) {
        $rentals = DB::table('rentals')->get();
        $totalHargaSeluruh = $rentals->sum('total_price');
        $totalTerbayarSeluruh = DB::table('payment_details')->sum('amount_paid');
        
        $totalPiutangSeluruh = 0;
        foreach ($rentals as $r) {
            $dibayarUntukRentalIni = DB::table('payment_details')->where('rental_id', $r->id)->sum('amount_paid');
            $sisa = $r->total_price - $dibayarUntukRentalIni;
            if ($sisa > 0) { $totalPiutangSeluruh += $sisa; }
        }
        
        $kategori = ['Ketua (Kakek)' => 0.05, 'Wakil Ketua (Dadong)' => 0.05, 'Sekertaris (Pan Ryo)' => 0.35, 'Bendahara (Pan Adit)' => 0.35, 'Dana Sosial' => 0.15, 'Cadangan' => 0.05];
        $pembagian = [];
        foreach ($kategori as $nama => $persen) {
            $jatahAwal = $totalTerbayarSeluruh * $persen;
            $totalKeluar = DB::table('expenses')->where('category_name', $nama)->sum('amount');
            $pembagian[$nama] = ['pemasukan' => $jatahAwal, 'pengeluaran' => $totalKeluar, 'sisa' => $jatahAwal - $totalKeluar];
        }

        $pemasukanCash = DB::table('payment_details')->where(function($q) {
            $q->where('payment_method', 'Cash')->orWhereNull('payment_method');
        })->sum('amount_paid');
        $pemasukanTransfer = DB::table('payment_details')->where('payment_method', 'Transfer')->sum('amount_paid');

        $pengeluaranCash = DB::table('expenses')->where(function($q) {
            $q->where('payment_method', 'Cash')->orWhereNull('payment_method');
        })->sum('amount');
        $pengeluaranTransfer = DB::table('expenses')->where('payment_method', 'Transfer')->sum('amount');

        $tarikTunai = DB::table('balance_mutations')->where('type', 'Tarik Tunai')->sum('amount');
        $setorTunai = DB::table('balance_mutations')->where('type', 'Setor Tunai')->sum('amount');

        $saldoCash = $pemasukanCash - $pengeluaranCash + $tarikTunai - $setorTunai;
        $saldoTransfer = $pemasukanTransfer - $pengeluaranTransfer - $tarikTunai + $setorTunai;

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $filterDate = function($query, $column) use ($startDate, $endDate) {
            if ($startDate) $query->whereDate($column, '>=', $startDate);
            if ($endDate) $query->whereDate($column, '<=', $endDate);
            return $query;
        };

        $historiPengeluaran = $filterDate(DB::table('expenses')->orderBy('created_at', 'desc'), 'created_at')
            ->paginate(5, ['*'], 'pengeluaran_page')->appends($request->query());
        
        $historiPemasukan = $filterDate(DB::table('payment_details')
            ->join('rentals', 'payment_details.rental_id', '=', 'rentals.id')
            ->select('payment_details.*', 'rentals.tenant_name')
            ->orderBy('payment_details.payment_date', 'desc'), 'payment_details.payment_date')
            ->paginate(5, ['*'], 'pemasukan_page')->appends($request->query());
            
        $historiMutasi = $filterDate(DB::table('balance_mutations')->orderBy('created_at', 'desc'), 'created_at')
            ->paginate(5, ['*'], 'mutasi_page')->appends($request->query());

        return view('laporan.index', compact(
            'totalHargaSeluruh', 'totalTerbayarSeluruh', 'totalPiutangSeluruh', 
            'pembagian', 'historiPengeluaran', 'historiPemasukan', 'historiMutasi', 
            'saldoCash', 'saldoTransfer'
        ));
    });

    Route::post('/laporan/tambah-pengeluaran', function (Request $request) {
        DB::table('expenses')->insert([
            'category_name' => $request->category_name, 'amount' => $request->amount, 
            'payment_method' => $request->payment_method, 'description' => $request->description, 
            'created_by' => Auth::user()->name, 'created_at' => now()
        ]);
        return back()->with('sukses', 'Pengeluaran berhasil ditambahkan!');
    });

    Route::post('/laporan/update-pengeluaran/{id}', function (Request $request, $id) {
        DB::table('expenses')->where('id', $id)->update([
            'amount' => $request->amount, 'payment_method' => $request->payment_method,
            'description' => $request->description, 'updated_at' => now()
        ]);
        return back()->with('sukses', 'Data pengeluaran berhasil diubah!');
    });

    Route::delete('/laporan/hapus-pengeluaran/{id}', function ($id) {
        DB::table('expenses')->where('id', $id)->delete();
        return back()->with('sukses', 'Data pengeluaran berhasil dihapus!');
    });

    Route::post('/laporan/mutasi', function (Request $request) {
        DB::table('balance_mutations')->insert([
            'type' => $request->type, 'amount' => $request->amount, 
            'description' => $request->description, 'created_by' => Auth::user()->name, 'created_at' => now()
        ]);
        return back()->with('sukses', 'Mutasi saldo berhasil dicatat!');
    });
    
    Route::delete('/laporan/hapus-mutasi/{id}', function ($id) {
        DB::table('balance_mutations')->where('id', $id)->delete();
        return back()->with('sukses', 'Riwayat mutasi berhasil dihapus! Saldo telah dikembalikan.');
    });
});