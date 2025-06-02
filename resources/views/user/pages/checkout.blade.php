@extends('user.layout.main')

@section('title', 'Checkout History')

@section('header')
<header class="bg-dark py-5">
  <div class="container px-4 px-lg-5 my-5">
    <div class="text-center text-white">
      <h1 class="display-4 fw-bolder">Checkout History</h1>
      <p class="lead fw-normal text-white-50 mb-0">Your purchase history</p>
    </div>
  </div>
</header>
@endsection

@section('content')
<div class="container mt-5">
    <h2>Riwayat Checkout</h2>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <div class="row">
        @forelse ($transactions as $transaction)
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $transaction->sepatu->title }}</h5>
                        <p>Jumlah: {{ $transaction->jumlah }}</p>
                        <p>Total Harga: Rp {{ number_format($transaction->total_harga, 0, ',', '.') }}</p>
                        <p>Status: {{ ucfirst($transaction->status) }}</p>
                        @if ($transaction->status == 'pending' && $transaction->expired_at)
                            <p>Batas Waktu Pembayaran: {{ $transaction->expired_at->format('d M Y H:i') }}</p>
                            @if ($transaction->expired_at->isPast())
                                <p class="text-danger">Transaksi telah kadaluarsa.</p>
                            @endif
                        @endif
                        <p>Tanggal: {{ $transaction->created_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center">Belum ada transaksi.</p>
        @endforelse
    </div>
</div>
@endsection