@extends('user.layout.main')

@section('title', 'Detail Checkout')

@section('header')
<header class="bg-dark py-5">
  <div class="container px-4 px-lg-5 my-5">
    <div class="text-center text-white">
      <h1 class="display-4 fw-bolder">Detail Checkout</h1>
      <p class="lead fw-normal text-white-50 mb-0">Lengkapi detail pengiriman Anda</p>
    </div>
  </div>
</header>
@endsection

@section('content')
<div class="container mt-5">
    <h2>Detail Transaksi</h2>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <!-- Gambar Sepatu (Kiri) -->
        <div class="col-md-4">
            <img src="{{ asset('storage/' . $transaction->sepatu->image_sepatu) }}" alt="{{ $transaction->sepatu->title }}" class="img-fluid" style="height: 300px; object-fit: cover;">
        </div>

        <!-- Detail Transaksi (Kanan) -->
        <div class="col-md-8">
            <h5 class="card-title">{{ $transaction->sepatu->title }}</h5>
            <p><strong>Deskripsi Produk:</strong> {{ $transaction->sepatu->deskripsi ?? 'Tidak ada deskripsi' }}</p>
            <p><strong>Jumlah yang Dibeli:</strong> {{ $transaction->jumlah }}</p>
            <p><strong>Harga Barang:</strong> Rp {{ number_format($transaction->total_harga, 0, ',', '.') }}</p>
            <p><strong>Stok Tersedia:</strong> {{ $transaction->sepatu->stok }}</p>
            <p><strong>Status:</strong> {{ ucfirst($transaction->status) }}</p>
            <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaction->created_at, 'Asia/Jakarta')->format('d M Y H:i') }} WIB</p>
            @if ($transaction->status == 'pending' && $transaction->expired_at)
                <?php
                    $expiredDate = \Carbon\Carbon::parse($transaction->expired_at, 'Asia/Jakarta');
                    $now = \Carbon\Carbon::now('Asia/Jakarta');
                ?>
                <p><strong>Batas Waktu Pembayaran:</strong> {{ $expiredDate->format('d M Y H:i') }} WIB</p>
                @if ($expiredDate->lt($now))
                    <p class="text-danger">Transaksi telah kadaluarsa.</p>
                    <form action="{{ route('user.checkout.expire', $transaction->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-warning btn-sm">Perbarui Status</button>
                    </form>
                @else
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#shippingModal">Pilih Pengiriman</button>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Modal Pengiriman -->
<div class="modal fade" id="shippingModal" tabindex="-1" aria-labelledby="shippingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shippingModalLabel">Pilih Detail Pengiriman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="shipping-form">
                    <div class="mb-3">
                        <label for="destination_province" class="form-label">Provinsi Tujuan</label>
                        <select name="destination_province" id="destination_province" class="form-select" required>
                            <option value="">Pilih Provinsi</option>
                            @foreach ($provinces as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="destination" class="form-label">Kota Tujuan</label>
                        <select name="destination" id="destination" class="form-select" required>
                            <option value="">Pilih Kota</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="courier" class="form-label">Kurir</label>
                        <select name="courier" id="courier" class="form-select" required>
                            <option value="jne">JNE</option>
                            <option value="pos">POS</option>
                            <option value="tiki">TIKI</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary" id="calculate-shipping">Cek Ongkir</button>
                </form>
                <div id="shipping-result" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#destination_province').change(function() {
            let provinceId = $(this).val();
            if (provinceId) {
                $.ajax({
                    url: '{{ url("/cities") }}/' + provinceId,
                    type: 'GET',
                    success: function(data) {
                        $('#destination').empty().append('<option value="">Pilih Kota</option>');
                        $.each(data, function(id, name) {
                            $('#destination').append('<option value="' + id + '">' + name + '</option>');
                        });
                    }
                });
            }
        });

        $('#calculate-shipping').click(function() {
            let destination = $('#destination').val();
            let courier = $('#courier').val();

            if (!destination || !courier) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Silakan pilih kota tujuan dan kurir.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            $.ajax({
                url: '{{ route("user.checkout.calculate", $transaction->id) }}',
                type: 'POST',
                data: {
                    destination: destination,
                    courier: courier,
                    jumlah: {{ $transaction->jumlah }},
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    let result = '<h5>Pilih Layanan Pengiriman:</h5>';
                    $.each(data, function(i, cost) {
                        result += '<div class="form-check">';
                        result += '<input class="form-check-input shipping-option" type="radio" name="shipping_option" value="' + cost.cost[0].value + '" data-service="' + cost.service + '" required>';
                        result += '<label class="form-check-label">' + cost.service + ': Rp ' + cost.cost[0].value.toLocaleString('id-ID') + ' (' + cost.cost[0].etd + ' hari)</label>';
                        result += '</div>';
                    });
                    result += '<button type="button" class="btn btn-success mt-3" id="save-shipping">Simpan</button>';
                    $('#shipping-result').html(result);

                    $('#save-shipping').click(function() {
                        let selectedOption = $('.shipping-option:checked');
                        if (!selectedOption.length) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Silakan pilih layanan pengiriman.',
                                confirmButtonText: 'OK'
                            });
                            return;
                        }

                        $.ajax({
                            url: '{{ route("user.checkout.save", $transaction->id) }}',
                            type: 'POST',
                            data: {
                                destination: $('#destination').val(),
                                courier: $('#courier').val(),
                                shipping_cost: selectedOption.val(),
                                service: selectedOption.data('service'),
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                window.location.href = '{{ route("user.checkout.index") }}';
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Gagal menyimpan detail pengiriman.',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    });
                },
                error: function() {
                    $('#shipping-result').html('<p class="text-danger">Gagal mengambil data ongkir.</p>');
                }
            });
        });
    });
</script>
@endsection