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
                    <img src="{{ asset('storage/' . $transaction->sepatu->image_sepatu) }}" class="card-img-top" alt="{{ $transaction->sepatu->title }}" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title">{{ $transaction->sepatu->title }}</h5>
                        <p>Jumlah: {{ $transaction->jumlah }}</p>
                        <p>Total Harga: Rp {{ number_format($transaction->total_harga, 0, ',', '.') }}</p>
                        @if ($transaction->shipping_cost)
                            <p>Ongkos Kirim: Rp {{ number_format($transaction->shipping_cost, 0, ',', '.') }} ({{ strtoupper($transaction->courier) }} - {{ $transaction->service }})</p>
                        @endif
                        @if ($transaction->alamat)
                            <p><strong>Alamat Pengiriman:</strong> {{ $transaction->alamat }}</p>
                        @endif
                        @if ($transaction->deskripsi_alamat)
                            <p><strong>Deskripsi Alamat:</strong> {{ $transaction->deskripsi_alamat }}</p>
                        @endif
                        <p>Status: {{ ucfirst($transaction->status) }}</p>
                        @if ($transaction->status == 'pending' && $transaction->expired_at)
                            <?php
                                $expiredDate = \Carbon\Carbon::parse($transaction->expired_at, 'Asia/Jakarta');
                                $now = \Carbon\Carbon::now('Asia/Jakarta');
                            ?>
                            <p>Batas Waktu Pembayaran: {{ $expiredDate->format('d M Y H:i') }} WIB</p>
                            @if ($expiredDate->lt($now))
                                <p class="text-danger">Transaksi telah kadaluarsa.</p>
                                <form action="{{ route('user.checkout.expire', $transaction->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-warning btn-sm">Perbarui Status</button>
                                </form>
                            @else
                                @if ($transaction->snap_token)
                                    <?php
                                        $isShippingDataComplete = $transaction->origin && $transaction->destination && $transaction->courier && $transaction->shipping_cost && $transaction->service;
                                    ?>
                                    <button class="btn btn-primary pay-now" data-snap-token="{{ $transaction->snap_token }}" data-transaction-id="{{ $transaction->id }}" @if(!$isShippingDataComplete) disabled @endif>Bayar Sekarang</button>
                                    @if(!$isShippingDataComplete)
                                        <p class="text-warning mt-2">Silakan lengkapi data pengiriman terlebih dahulu di halaman detail transaksi.</p>
                                    @endif
                                @else
                                    <p class="text-warning">Snap token tidak tersedia. Hubungi admin.</p>
                                @endif
                            @endif
                        @endif
                        <p>Tanggal: {{ \Carbon\Carbon::parse($transaction->created_at, 'Asia/Jakarta')->format('d M Y H:i') }} WIB</p>
                        <a href="{{ route('user.checkout.detail', $transaction->id) }}" class="btn btn-info btn-sm">Lihat Detail</a>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center">Belum ada transaksi.</p>
        @endforelse
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.pay-now').forEach(function(button) {
            button.addEventListener('click', function() {
                var snapToken = this.getAttribute('data-snap-token');
                var transactionId = this.getAttribute('data-transaction-id');
                if (!snapToken) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Snap token tidak ditemukan. Silakan coba lagi atau hubungi admin.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Memproses Pembayaran',
                    text: 'Silakan tunggu...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                snap.pay(snapToken, {
                    onSuccess: function(result) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses',
                            text: 'Pembayaran berhasil!',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = '{{ route('user.checkout.index') }}';
                        });
                    },
                    onPending: function(result) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Tertunda',
                            text: 'Pembayaran tertunda. Silakan selesaikan pembayaran.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = '{{ route('user.checkout.index') }}';
                        });
                    },
                    onError: function(result) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Pembayaran gagal! Silakan coba lagi.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = '{{ route('user.checkout.index') }}';
                        });
                    },
                    onClose: function() {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Dibatalkan',
                            text: 'Anda menutup popup pembayaran.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = '{{ route('user.checkout.index') }}';
                        });
                    }
                });
            });
        });
    });
</script>
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
@endsection