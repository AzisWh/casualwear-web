@php
  use App\Models\CartModel;

  $cartItems = auth()->check()
    ? CartModel::with('sepatu')->where('user_id', auth()->id())->get()
    : collect();
  $cartCount = $cartItems->sum('jumlah');
@endphp

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container px-4 px-lg-5">
      <a class="navbar-brand" href="{{ url('/') }}">Start Bootstrap</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
        aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
          <li class="nav-item"><a class="nav-link active" aria-current="page" href="{{route('user.home')}}">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="{{route('user.checkout.index')}}">Checkout</a></li>
          <li class="nav-item"><a class="nav-link" href="{{route('user.shipping.index')}}">Shipping</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button"
              data-bs-toggle="dropdown" aria-expanded="false">Shop</a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="#">All Products</a></li>
              <li><hr class="dropdown-divider" /></li>
              <li><a class="dropdown-item" href="#">Popular Items</a></li>
              <li><a class="dropdown-item" href="#">New Arrivals</a></li>
            </ul>
          </li>
        </ul>

        <div class="d-flex me-3 position-relative">
          @auth
          <div class="dropdown">
            <button class="btn btn-outline-dark dropdown-toggle" type="button" id="cartDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi-cart-fill me-1"></i>
              Cart
              <span class="badge bg-dark text-white ms-1 rounded-pill">{{ $cartCount }}</span>
            </button>

            @if ($cartCount > 0)
            <ul class="dropdown-menu dropdown-menu-end dropdown-cart" aria-labelledby="cartDropdown">
              @foreach ($cartItems as $item)
              <li class="mb-2 d-flex align-items-start px-2">
                <img src="{{ asset('storage/' . $item->sepatu->image_sepatu) }}" alt="{{ $item->sepatu->title }}" class="me-2 rounded" style="width: 50px; height: 50px; object-fit: cover;">
                <div class="flex-grow-1">
                  <strong>{{ $item->sepatu->title }}</strong><br>
                  <small>Qty: {{ $item->jumlah }}</small><br>
                  <small>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</small>
                </div>
                <form id="deleteForm_{{ $item->id }}" action="{{ route('cart.delete', $item->id) }}" method="POST" onsubmit="return false;">
                  @csrf
                  @method('DELETE')
                  <button type="button" class="btn btn-sm btn-danger ms-2" onclick="confirmDelete({{ $item->id }})">Hapus</button>
                </form>
              </li>
              @endforeach
              <li><hr class="dropdown-divider"></li>
              <li class="text-center px-3 mb-2">
                <a href="{{route('user.cart.index')}}" class="btn btn-sm btn-primary w-100">Lihat Keranjang</a>
              </li>
            </ul>
            @else
            <ul class="dropdown-menu dropdown-menu-end dropdown-cart" aria-labelledby="cartDropdown">
              <li class="px-3"><small class="text-muted">Keranjang kosong</small></li>
            </ul>
            @endif
          </div>
          @else
          <button class="btn btn-outline-dark" type="button" disabled>
            <i class="bi-cart-fill me-1"></i>
            Cart
            <span class="badge bg-dark text-white ms-1 rounded-pill">0</span>
          </button>
          @endauth
        </div>

        @auth
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              Hello, {{ Auth::user()->nama_depan }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</a></li>
              <li>
                <form action="{{ route('logout.action') }}" method="POST" class="d-inline">
                  @csrf
                  <button type="submit" class="dropdown-item">Logout</button>
                </form>
              </li>
            </ul>
          </div>
        @else
          <a href="{{ route('register') }}" class="btn btn-outline-primary btn-sm me-2">Sign Up</a>
          <a href="{{ route('login') }}" class="btn btn-outline-success btn-sm">Login</a>
        @endauth
      </div>
    </div>
</nav>

<script>
  function confirmDelete(id) {
    Swal.fire({
      title: 'Apakah Anda yakin?',
      text: 'Item ini akan dihapus dari keranjang!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, Hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        document.getElementById('deleteForm_' + id).submit();
      }
    });
  }
</script>