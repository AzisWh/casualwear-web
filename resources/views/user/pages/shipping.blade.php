@extends('user.layout.main')

@section('title', 'User - Cek Pengiriman')

@section('content')
    <div class="container mt-5">
        <h2>Cek Pengiriman</h2>

        <div class="mb-4">
            <form method="GET" action="{{ route('user.shipping.index') }}" class="d-inline">
                <select name="shipping_status" class="form-select d-inline w-auto" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ $shippingStatusFilter === '' ? 'selected' : '' }}>Pending</option>
                    <option value="processed" {{ $shippingStatusFilter === 'processed' ? 'selected' : '' }}>Diproses</option>
                    <option value="shipped" {{ $shippingStatusFilter === 'shipped' ? 'selected' : '' }}>Dikirim</option>
                    <option value="delivered" {{ $shippingStatusFilter === 'delivered' ? 'selected' : '' }}>Diterima</option>
                    <option value="cancelled" {{ $shippingStatusFilter === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </form>
        </div>

        @if ($transactions->isEmpty())
            <div class="alert alert-warning text-center">Belum ada data checkout di sini</div>
        @else
            <div class="row">
                @foreach ($transactions as $transaction)
                    <div class="col-12 mb-4"> 
                        <div class="card h-100">
                            <h5 class="card-header">Transaksi #{{ $transaction->id }}</h5>
                            <div class="card-body d-flex">
                                <div class="me-3">
                                    <img src="{{ asset('storage/' . $transaction->sepatu->image_sepatu) }}" alt="{{ $transaction->sepatu->title }}" class="img-fluid" style="width: 200px; height: 200px; object-fit: cover;">
                                </div>
                                <div class="flex-grow-1"> 
                                    <h5 class="card-title">{{ $transaction->sepatu->title }}</h5>
                                    <p class="card-text"><strong>Jumlah:</strong> {{ $transaction->jumlah }}</p>
                                    <p class="card-text"><strong>Harga Total:</strong> Rp {{ number_format($transaction->total_harga, 0, ',', '.') }}</p>
                                    <p class="card-text"><strong>Status Checkout:</strong> <span class="badge bg-{{ $transaction->status === 'pending' ? 'warning' : 'success' }}">{{ ucfirst($transaction->status) }}</span></p>
                                    <p class="card-text"><strong>Status Pengiriman:</strong> 
                                        <span class="badge bg-{{ $transaction->shipping_status ? ($transaction->shipping_status === 'delivered' ? 'success' : ($transaction->shipping_status === 'shipped' ? 'info' : ($transaction->shipping_status === 'processed' ? 'primary' : 'danger'))) : 'secondary' }}">
                                            {{ $transaction->shipping_status ?? 'menunggu pembayaran' }}
                                        </span>
                                    </p>
                                    <p class="card-text"><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaction->created_at, 'Asia/Jakarta')->format('d M Y H:i') }} WIB</p>
                                    @if ($transaction->shipping_status)
                                        <p class="card-text"><strong>Alamat:</strong> {{ $transaction->alamat }}</p>
                                        <p class="card-text"><strong>Kurir:</strong> {{ $transaction->courier }}</p>
                                        <p class="card-text"><strong>Biaya Pengiriman:</strong> Rp {{ number_format($transaction->shipping_cost, 0, ',', '.') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection