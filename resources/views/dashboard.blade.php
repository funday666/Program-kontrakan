@extends('layouts.app') @section('content')
<div class="row">
    <div class="col-12 mb-4">
        <h2>Selamat Datang di Dashboard Penyewaan</h2>
        <p class="text-muted">Kelola data lahan, tanah, ruko, dan garase kamu di sini.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-primary shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Total Properti</h5>
                <p class="card-text fs-3 fw-bold">12 Unit</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-success shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Sedang Disewa</h5>
                <p class="card-text fs-3 fw-bold">5 Unit</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-warning shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Permintaan Pending</h5>
                <p class="card-text fs-3 fw-bold">3 Transaksi</p>
            </div>
        </div>
    </div>
</div>
@endsection