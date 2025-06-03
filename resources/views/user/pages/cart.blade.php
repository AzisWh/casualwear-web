@extends('user.layout.main')

@section('title', 'Keranjang Belanja')

@section('content')
<div class="container mt-5">
    <h1>Keranjang Belanja</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($cartItems->isEmpty())
        <p>Keranjang kosong.</p>
    @else
        <form action="{{ route('user.checkout.store') }}" method="POST" id="checkoutForm">
            @csrf
            <div class="row">
                @foreach($cartItems as $item)
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="{{ asset('storage/' . $item->sepatu->image_sepatu) }}" class="card-img-top" alt="{{ $item->sepatu->title }}" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">{{ $item->sepatu->title }}</h5>
                                <p class="card-text">Qty: {{ $item->jumlah }}</p>
                                <p class="card-text">Harga: Rp {{ number_format($item->total_harga, 0, ',', '.') }}</p>
                                <div class="form-check">
                                    <input type="checkbox" name="selected_items[]" value="{{ $item->id }}" class="form-check-input" id="item_{{ $item->id }}">
                                    <label class="form-check-label" for="item_{{ $item->id }}">Checkout</label>
                                </div>
                                <form action="{{ route('cart.delete', $item->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus item ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm mt-2">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="submit" class="btn btn-primary mt-3" id="checkoutButton" disabled>Checkout Terpilih</button>
        </form>
    @endif
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#checkoutButton').prop('disabled', $('input[name="selected_items[]"]:checked').length === 0);

        $('input[name="selected_items[]"]').change(function() {
            $('#checkoutButton').prop('disabled', $('input[name="selected_items[]"]:checked').length === 0);
        });

        $('#checkoutForm').submit(function(e) {
            if ($('input[name="selected_items[]"]:checked').length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Pilih setidaknya satu item untuk checkout.',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
</script>
@endsection