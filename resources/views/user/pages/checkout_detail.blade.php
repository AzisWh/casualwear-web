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
                    $user = Auth::user();
                    $isAddressComplete = $user->alamat_tinggal && $user->asal_kota && $user->asal_provinsi && $user->kodepos;
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
                    @if ($isAddressComplete)
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#editShippingModal">Edit Data Pengiriman</button>
                    @else
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addShippingModal">Pilih Pengiriman</button>
                    @endif
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Modal for Adding New Shipping Address -->
<div class="modal fade" id="addShippingModal" tabindex="-1" aria-labelledby="addShippingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addShippingModalLabel">Pilih Detail Pengiriman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-shipping-form" action="{{ route('user.checkout.updateAddress', $transaction->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="add_destination_province" class="form-label">Provinsi Tujuan</label>
                        <select name="destination_province" id="add_destination_province" class="form-select" required>
                            <option value="">Pilih Provinsi</option>
                            @foreach ($provinces as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add_destination" class="form-label">Kota Tujuan</label>
                        <select name="destination" id="add_destination" class="form-select" required>
                            <option value="">Pilih Kota</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add_courier" class="form-label">Kurir</label>
                        <select name="courier" id="add_courier" class="form-select" required>
                            <option value="jne">JNE</option>
                            <option value="pos">POS</option>
                            <option value="tiki">TIKI</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add_alamat" class="form-label">Alamat Tujuan</label>
                        <div class="input-group">
                            <textarea class="form-control" id="add_alamat" name="alamat" rows="3" required></textarea>
                            <button type="button" class="btn btn-outline-secondary" id="use-saved-address-add">Gunakan Alamat Profil</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add_deskripsi_alamat" class="form-label">Deskripsi Alamat (Opsional)</label>
                        <textarea class="form-control" id="add_deskripsi_alamat" name="deskripsi_alamat" rows="2" placeholder="Contoh: Dekat masjid, warna rumah biru"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" id="save-address-add">Simpan Alamat</button>
                    <div id="add-shipping-result" class="mt-3"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Editing Shipping Address -->
<div class="modal fade" id="editShippingModal" tabindex="-1" aria-labelledby="editShippingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editShippingModalLabel">Edit Data Pengiriman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-shipping-form" action="{{ route('user.checkout.updateAddress', $transaction->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="edit_destination_province" class="form-label">Provinsi Tujuan</label>
                        <select name="destination_province" id="edit_destination_province" class="form-select" >
                            <option value="">Pilih Provinsi</option>
                            @foreach ($provinces as $id => $name)
                                <option value="{{ $id }}" {{ Auth::user()->asal_provinsi_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_destination" class="form-label">Kota Tujuan</label>
                        <select name="destination" id="edit_destination" class="form-select" >
                            <option value="">Pilih Kota</option>
                            @if(Auth::user()->asal_kota_id)
                                <option value="{{ Auth::user()->asal_kota_id }}" selected>{{ Auth::user()->asal_kota }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_courier" class="form-label">Kurir</label>
                        <select name="courier" id="edit_courier" class="form-select" >
                            <option value="jne">JNE</option>
                            <option value="pos">POS</option>
                            <option value="tiki">TIKI</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_alamat" class="form-label">Alamat Tujuan</label>
                        <div class="input-group">
                            <textarea class="form-control" id="edit_alamat" name="alamat" rows="3" >{{ Auth::user()->alamat_tinggal ?? '' }}</textarea>
                            <button type="button" class="btn btn-outline-secondary" id="use-saved-address-edit">Gunakan Alamat Profil</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_deskripsi_alamat" class="form-label">Deskripsi Alamat (Opsional)</label>
                        <textarea class="form-control" id="edit_deskripsi_alamat" name="deskripsi_alamat" rows="2" placeholder="Contoh: Dekat masjid, warna rumah biru">{{ Auth::user()->deskripsi_alamat ?? '' }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" id="save-address-edit">Simpan Perubahan</button>
                    <div id="edit-shipping-result" class="mt-3"></div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Load cities dynamically for add modal
        $('#add_destination_province').change(function() {
            let provinceId = $(this).val();
            if (provinceId) {
                $.ajax({
                    url: '{{ url("/cities") }}/' + provinceId,
                    type: 'GET',
                    success: function(data) {
                        $('#add_destination').empty().append('<option value="">Pilih Kota</option>');
                        $.each(data, function(id, name) {
                            $('#add_destination').append('<option value="' + id + '">' + name + '</option>');
                        });
                    }
                });
            }
        });

        // Load cities dynamically for edit modal
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
                        let userCityId = '{{ Auth::user()->asal_kota_id ?? '' }}';
                        if (userCityId && data[userCityId]) {
                            $('#edit_destination').val(userCityId);
                        }
                    }
                });
            }
        });

        // Use saved address for add modal
        $('#use-saved-address-add').click(function() {
            const user = @json(Auth::user());
            const savedAddress = user.alamat_tinggal || '';
            const savedCity = user.asal_kota || '';
            const savedProvince = user.asal_provinsi || '';
            const savedPostalCode = user.kodepos || '';

            if (savedAddress || savedCity || savedProvince || savedPostalCode) {
                const fullAddress = [savedAddress, savedCity, savedProvince, savedPostalCode].filter(Boolean).join(', ');
                $('#add_alamat').val(fullAddress);
                if (user.asal_provinsi_id) {
                    $('#add_destination_province').val(user.asal_provinsi_id).trigger('change');
                    if (user.asal_kota_id) {
                        setTimeout(() => $('#add_destination').val(user.asal_kota_id), 100);
                    }
                }
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Alamat Tidak Tersedia',
                    text: 'Silakan lengkapi alamat di profil Anda atau masukkan secara manual.',
                    confirmButtonText: 'OK'
                });
            }
        });

        // Use saved address for edit modal
        $('#use-saved-address-edit').click(function() {
            const user = @json(Auth::user());
            const savedAddress = user.alamat_tinggal || '';
            const savedCity = user.asal_kota || '';
            const savedProvince = user.asal_provinsi || '';
            const savedPostalCode = user.kodepos || '';

            if (savedAddress || savedCity || savedProvince || savedPostalCode) {
                const fullAddress = [savedAddress, savedCity, savedProvince, savedPostalCode].filter(Boolean).join(', ');
                $('#edit_alamat').val(fullAddress);
                if (user.asal_provinsi_id) {
                    $('#edit_destination_province').val(user.asal_provinsi_id).trigger('change');
                    if (user.asal_kota_id) {
                        setTimeout(() => $('#edit_destination').val(user.asal_kota_id), 100);
                    }
                }
            }
        });

        // Handle form submission for add modal
        $('#add-shipping-form').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Alamat berhasil disimpan!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        $('#addShippingModal').modal('hide');
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat menyimpan alamat.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        });

        // Handle form submission for edit modal
        $('#edit-shipping-form').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Alamat berhasil diperbarui!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        $('#editShippingModal').modal('hide');
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat memperbarui alamat.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        });

        // Initialize with user's province and city for edit modal
        let userProvinceId = '{{ Auth::user()->asal_provinsi_id ?? '' }}';
        if (userProvinceId) {
            $('#edit_destination_province').val(userProvinceId).trigger('change');
        }
    });
</script>
@endsection