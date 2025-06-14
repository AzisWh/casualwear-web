@extends('user.layout.userlayout')

@section('title', 'Keranjang Belanja')

@section('content')
<div class="bg-light">
    <div class="container pt-5">
        <h1>Keranjang Belanja</h1>
    
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
    
        @if($cartItems->isEmpty())
            <p>Keranjang kosong.</p>
        @else
            <div class="row">
                @foreach($cartItems as $item)
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="{{ asset('storage/' . $item->sepatu->image_sepatu) }}" class="card-img-top" alt="{{ $item->sepatu->title }}" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">{{ $item->sepatu->title }}</h5>
                                <p class="card-text">Qty: {{ $item->jumlah }}</p>
                                <p class="card-text">Harga: Rp {{ number_format($item->total_harga, 0, ',', '.') }}</p>
    
                                {{-- Tombol Checkout --}}
                                <form action="{{ route('user.cart.checkout', $item->id) }}" method="POST" class="d-inline singleCheckoutForm">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">Checkout</button>
                                </form>
    
                                {{-- Tombol Hapus --}}
                                <form action="{{ route('cart.delete', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus item ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@section('script')
{{-- Pastikan SweetAlert dimuat --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.querySelectorAll('.singleCheckoutForm').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                icon: 'info',
                title: 'Checkout',
                text: 'Anda akan melanjutkan checkout untuk item ini.',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Lanjutkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection
