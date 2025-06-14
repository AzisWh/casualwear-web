@extends('user.layout.userlayout')

@section('title', 'Detail Checkout')


@section('content')
<div class="bg-light">
    <div class="container pt-5">
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
                        $user = Auth::user();
                        $isAddressComplete = $user->alamat_tinggal && $user->asal_kota && $user->asal_provinsi && $user->kodepos;
                        $hasShippingDetails = $transaction->destination && $transaction->courier && $transaction->alamat;
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
                        @if (!$hasShippingDetails)
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#shippingModal">Pilih Pengiriman</button>
                        @else
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#shippingModal">Ubah Pengiriman</button>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#editShippingModal">Edit Data Pengiriman</button>
                        @endif
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
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat Tujuan</label>
                            <div class="input-group">
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                                <button type="button" class="btn btn-outline-secondary" id="use-saved-address">Gunakan Alamat Profil</button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi_alamat" class="form-label">Deskripsi Alamat (Opsional)</label>
                            <textarea class="form-control" id="deskripsi_alamat" name="deskripsi_alamat" rows="2" placeholder="Contoh: Dekat masjid, warna rumah biru"></textarea>
                        </div>
                        <button type="button" class="btn btn-primary" id="calculate-shipping">Cek Ongkir</button>
                    </form>
                    <div id="shipping-result" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Edit Data Pengiriman -->
    <div class="modal fade" id="editShippingModal" tabindex="-1" aria-labelledby="editShippingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editShippingModalLabel">Edit Data Pengiriman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-shipping-form">
                        <div class="mb-3">
                            <label for="edit_destination_province" class="form-label">Provinsi Tujuan</label>
                            <select name="destination_province" id="edit_destination_province" class="form-select" required>
                                <option value="">Pilih Provinsi</option>
                                @foreach ($provinces as $id => $name)
                                    <option value="{{ $id }}" {{ $transaction->origin_province_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_destination" class="form-label">Kota Tujuan</label>
                            <select name="destination" id="edit_destination" class="form-select" required>
                                <option value="">Pilih Kota</option>
                                @if ($transaction->destination)
                                    <option value="{{ $transaction->destination }}" selected>{{ $transaction->destination_city ?? $transaction->destination }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_courier" class="form-label">Kurir</label>
                            <select name="courier" id="edit_courier" class="form-select" required>
                                <option value="jne" {{ $transaction->courier == 'jne' ? 'selected' : '' }}>JNE</option>
                                <option value="pos" {{ $transaction->courier == 'pos' ? 'selected' : '' }}>POS</option>
                                <option value="tiki" {{ $transaction->courier == 'tiki' ? 'selected' : '' }}>TIKI</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_alamat" class="form-label">Alamat Tujuan</label>
                            <textarea class="form-control" id="edit_alamat" name="alamat" rows="3" required>{{ $transaction->alamat ?? '' }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_deskripsi_alamat" class="form-label">Deskripsi Alamat (Opsional)</label>
                            <textarea class="form-control" id="edit_deskripsi_alamat" name="deskripsi_alamat" rows="2" placeholder="Contoh: Dekat masjid, warna rumah biru">{{ $transaction->deskripsi_alamat ?? '' }}</textarea>
                        </div>
                        <button type="button" class="btn btn-primary" id="calculate-edit-shipping">Cek Ongkir</button>
                    </form>
                    <div id="edit-shipping-result" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Gunakan alamat dari profil user untuk shipping modal
        $('#use-saved-address').click(function() {
            const user = @json(Auth::user());
            const savedAddress = user.alamat_tinggal || '';
            const savedCity = user.asal_kota || '';
            const savedProvince = user.asal_provinsi || '';
            const savedPostalCode = user.kodepos || '';

            if (savedAddress || savedCity || savedProvince || savedPostalCode) {
                const fullAddress = [savedAddress, savedCity, savedProvince, savedPostalCode].filter(Boolean).join(', ');
                $('#alamat').val(fullAddress);
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Alamat Tidak Tersedia',
                    text: 'Silakan lengkapi alamat di profil Anda atau masukkan secara manual.',
                    confirmButtonText: 'OK'
                });
            }
        });

        // Load cities dynamically for shipping modal
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

        // Calculate shipping for shipping modal with loading state
        $('#calculate-shipping').click(function() {
            let $button = $(this);
            let destination = $('#destination').val();
            let courier = $('#courier').val();
            let alamat = $('#alamat').val();

            if (!destination || !courier || !alamat) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Silakan pilih kota tujuan, kurir, dan isi alamat tujuan.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Disable button and add loading state
            $button.prop('disabled', true).text('Memuat...');

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
                                alamat: $('#alamat').val(),
                                deskripsi_alamat: $('#deskripsi_alamat').val(),
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
                },
                complete: function() {
                    // Re-enable button and restore text after request completes
                    $button.prop('disabled', false).text('Cek Ongkir');
                }
            });
        });

        // Load cities dynamically for edit shipping modal
        $('#edit_destination_province').change(function() {
            let provinceId = $(this).val();
            if (provinceId) {
                $.ajax({
                    url: '{{ url("/cities") }}/' + provinceId,
                    type: 'GET',
                    success: function(data) {
                        $('#edit_destination').empty().append('<option value="">Pilih Kota</option>');
                        $.each(data, function(id, name) {
                            $('#edit_destination').append('<option value="' + id + '">' + name + '</option>');
                        });
                        // Preselect the current destination if available
                        let currentDestination = '{{ $transaction->destination ?? '' }}';
                        if (currentDestination && data[currentDestination]) {
                            $('#edit_destination').val(currentDestination);
                        }
                    }
                });
            }
        });

        // Calculate shipping for edit shipping modal with loading state
        $('#calculate-edit-shipping').click(function() {
            let $button = $(this);
            let destination = $('#edit_destination').val();
            let courier = $('#edit_courier').val();
            let alamat = $('#edit_alamat').val();

            if (!destination || !courier || !alamat) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Silakan pilih kota tujuan, kurir, dan isi alamat tujuan.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Disable button and add loading state
            $button.prop('disabled', true).text('Memuat...');

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
                        result += '<input class="form-check-input edit-shipping-option" type="radio" name="edit_shipping_option" value="' + cost.cost[0].value + '" data-service="' + cost.service + '" required>';
                        result += '<label class="form-check-label">' + cost.service + ': Rp ' + cost.cost[0].value.toLocaleString('id-ID') + ' (' + cost.cost[0].etd + ' hari)</label>';
                        result += '</div>';
                    });
                    result += '<button type="button" class="btn btn-success mt-3" id="update-shipping">Perbarui Pengiriman</button>';
                    $('#edit-shipping-result').html(result);

                    $('#update-shipping').click(function() {
                        let selectedOption = $('.edit-shipping-option:checked');
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
                                destination: $('#edit_destination').val(),
                                courier: $('#edit_courier').val(),
                                shipping_cost: selectedOption.val(),
                                service: selectedOption.data('service'),
                                alamat: $('#edit_alamat').val(),
                                deskripsi_alamat: $('#edit_deskripsi_alamat').val(),
                                is_update: true,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: 'Detail pengiriman berhasil diperbarui!',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    $('#editShippingModal').modal('hide');
                                    location.reload();
                                });
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Gagal memperbarui detail pengiriman.',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    });
                },
                error: function() {
                    $('#edit-shipping-result').html('<p class="text-danger">Gagal mengambil data ongkir.</p>');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Cek Ongkir');
                }
            });
        });
    });
</script>
@endsection