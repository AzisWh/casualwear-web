@extends('user.layout.main')

@section('title', 'Home Page')

@section('header')
<header class="bg-dark py-5">
  <div class="container px-4 px-lg-5 my-5">
    <div class="text-center text-white">
      <h1 class="display-4 fw-bolder">Shop in style</h1>
      <p class="lead fw-normal text-white-50 mb-0">With this shop homepage template</p>
    </div>
  </div>
</header>
@endsection

@section('content')
<div class="container mt-5">
    <form method="GET" action="{{ route('user.home') }}" class="mb-4">
      <div class="row">
        <div class="col-md-6">
          <select name="kategori" class="form-select" onchange="this.form.submit()">
            <option value="">-- Semua Kategori --</option>
            @foreach ($dataKategori as $kategori)
              <option value="{{ $kategori->id }}" {{ request('kategori') == $kategori->id ? 'selected' : '' }}>
                {{ $kategori->nama_kategori }}
              </option>
            @endforeach
          </select>
        </div>
      </div>
    </form>
</div>

<section class="py-3">
    <div class="container">
      <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
        @forelse ($dataSepatu as $sepatu)
          <div class="col mb-5">
            <div class="card h-100">
              <img class="card-img-top"
                src="{{ asset('storage/' . $sepatu->image_sepatu) }}"
                alt="{{ $sepatu->title }}"
                style="height: 200px; object-fit: cover;" />
              <div class="card-body p-4">
                <div class="text-center">
                  <h5 class="fw-bolder">{{ $sepatu->title }}</h5>
                  <p>Ukuran sepatu: {{ $sepatu->size }}</p>
                  <p>Harga: Rp {{ number_format($sepatu->harga_sepatu, 0, ',', '.') }}</p>
                  <p>Stok: <span class="text-danger">{{ $sepatu->stok }}</span></p>
                </div>
                <div class="d-flex flex-column gap-4">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#checkoutModal{{ $sepatu->id }}">
                      Checkout
                    </button>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCartModal{{ $sepatu->id }}">
                      Add to Cart
                    </button>
                </div>
              </div>
            </div>
          </div>
          <!-- Modal Checkout -->
          <div class="modal fade" id="checkoutModal{{ $sepatu->id }}" tabindex="-1" aria-labelledby="checkoutModalLabel{{ $sepatu->id }}" aria-hidden="true">
            <div class="modal-dialog">
              <form action="{{ route('user.checkout.store', $sepatu->id) }}" method="POST">
                @csrf
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="checkoutModalLabel{{ $sepatu->id }}">Checkout - {{ $sepatu->title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <p><strong>{{ $sepatu->title }}</strong></p>
                    <p>Harga per item: Rp {{ number_format($sepatu->harga_sepatu, 0, ',', '.') }}</p>
                    <p>Stok tersedia: {{ $sepatu->stok }}</p>
                    <div class="mb-3">
                      <label for="jumlah" class="form-label">Jumlah:</label>
                      <input type="number" name="jumlah" class="form-control jumlahInput" data-harga="{{ $sepatu->harga_sepatu }}" min="1" max="{{ $sepatu->stok }}" required>
                    </div>
                    <div class="mb-3">
                      <label for="voucher_code" class="form-label">Kode Voucher (Opsional):</label>
                      <input type="text" name="voucher_code" id="voucher_code_{{ $sepatu->id }}" class="form-control" placeholder="Masukkan kode voucher">
                      <button type="button" class="btn btn-secondary mt-2" onclick="applyVoucher('{{ $sepatu->id }}')">Terapkan Voucher</button>
                      <p id="voucher_message_{{ $sepatu->id }}" class="mt-2" style="display: none;"></p>
                    </div>
                    <div class="mb-3">
                      <label>Total Harga:</label>
                      <p class="fw-bold totalHargaText" id="totalHargaText_{{ $sepatu->id }}">Rp 0</p>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Checkout Sekarang</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
          <!-- Modal Add to Cart -->
          <div class="modal fade" id="addCartModal{{ $sepatu->id }}" tabindex="-1" aria-labelledby="addCartModalLabel{{ $sepatu->id }}" aria-hidden="true">
            <div class="modal-dialog">
              <form action="{{ route('cart.add', $sepatu->id) }}" method="POST">
                @csrf
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="addCartModalLabel{{ $sepatu->id }}">Tambah ke Keranjang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <p><strong>{{ $sepatu->title }}</strong></p>
                    <p>Harga per item: Rp {{ number_format($sepatu->harga_sepatu, 0, ',', '.') }}</p>
                    <p>Stok tersedia: {{ $sepatu->stok }}</p>
                    <div class="mb-3">
                      <label for="jumlah" class="form-label">Jumlah:</label>
                      <input type="number" name="jumlah" class="form-control jumlahInput" data-harga="{{ $sepatu->harga_sepatu }}" min="1" max="{{ $sepatu->stok }}" required>
                    </div>
                    <div class="mb-3">
                      <label>Total Harga:</label>
                      <p class="fw-bold totalHargaText">Rp 0</p>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Tambahkan</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        @empty
          <p class="text-center">Tidak ada sepatu untuk kategori ini.</p>
        @endforelse
      </div>
    </div>
</section>

<script>
    function applyVoucher(sepatuId) {
        const voucherCode = document.getElementById('voucher_code_' + sepatuId).value;
        const totalText = document.getElementById('totalHargaText_' + sepatuId);
        const voucherMessage = document.getElementById('voucher_message_' + sepatuId);
        const jumlahInput = document.querySelector('#checkoutModal' + sepatuId + ' .jumlahInput');
        const harga = parseInt(jumlahInput.dataset.harga);
        const jumlah = parseInt(jumlahInput.value);

        if (!voucherCode) {
            voucherMessage.style.display = 'block';
            voucherMessage.className = 'text-danger mt-2';
            voucherMessage.innerText = 'Kode voucher tidak boleh kosong.';
            voucherMessage.dataset.discount = '0';
            totalText.innerText = 'Rp ' + (harga * jumlah).toLocaleString('id-ID');
            return;
        }

        // Use AJAX to call the checkVoucher endpoint
        fetch('{{ route('user.check-voucher') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ code: voucherCode })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                voucherMessage.style.display = 'block';
                voucherMessage.className = 'text-success mt-2';
                voucherMessage.innerText = data.message;
                voucherMessage.dataset.discount = data.discount_type === 'percentage' ? data.discount_value : (data.discount_value / (harga * jumlah) * 100);
                let total = harga * jumlah;
                if (voucherMessage.dataset.discount > 0) {
                    total = total * (1 - voucherMessage.dataset.discount / 100);
                }
                totalText.innerText = 'Rp ' + Math.round(total).toLocaleString('id-ID');
            } else {
                voucherMessage.style.display = 'block';
                voucherMessage.className = 'text-danger mt-2';
                voucherMessage.innerText = data.message;
                voucherMessage.dataset.discount = '0';
                totalText.innerText = 'Rp ' + (harga * jumlah).toLocaleString('id-ID');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            voucherMessage.style.display = 'block';
            voucherMessage.className = 'text-danger mt-2';
            voucherMessage.innerText = 'Terjadi kesalahan saat memeriksa voucher.';
            voucherMessage.dataset.discount = '0';
            totalText.innerText = 'Rp ' + (harga * jumlah).toLocaleString('id-ID');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.jumlahInput').forEach(function(input) {
            input.addEventListener('input', function () {
                const harga = parseInt(this.dataset.harga);
                const jumlah = parseInt(this.value);
                const sepatuId = this.closest('.modal').id.replace('checkoutModal', '').replace('addCartModal', '');
                const totalText = this.closest('.modal-body').querySelector('.totalHargaText');
                let discount = 0;
                const voucherMessage = document.getElementById('voucher_message_' + sepatuId);
                if (voucherMessage && voucherMessage.dataset.discount) {
                    discount = parseFloat(voucherMessage.dataset.discount);
                }
                if (!isNaN(jumlah)) {
                    let total = harga * jumlah;
                    if (discount > 0) {
                        total = total * (1 - discount / 100);
                    }
                    totalText.innerText = 'Rp ' + Math.round(total).toLocaleString('id-ID');
                } else {
                    totalText.innerText = 'Rp 0';
                }
            });
        });
    });
</script>
@endsection