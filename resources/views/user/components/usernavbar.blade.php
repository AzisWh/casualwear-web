

<div class="search-popup">
    <div class="search-popup-container">

      <form role="search" method="get" class="form-group" action="">
        <input type="search" id="search-form" class="form-control border-0 border-bottom"
          placeholder="Type and press enter" value="" name="s" />
        <button type="submit" class="search-submit border-0 position-absolute bg-white"
          style="top: 15px;right: 15px;"><svg class="search" width="24" height="24">
            <use xlink:href="#search"></use>
          </svg></button>
      </form>

      <h5 class="cat-list-title">Browse Categories</h5>

      <ul class="cat-list">
        <li class="cat-list-item">
          <a href="#" title="Jackets">Jackets</a>
        </li>
        <li class="cat-list-item">
          <a href="#" title="T-shirts">T-shirts</a>
        </li>
        <li class="cat-list-item">
          <a href="#" title="Handbags">Handbags</a>
        </li>
        <li class="cat-list-item">
          <a href="#" title="Accessories">Accessories</a>
        </li>
        <li class="cat-list-item">
          <a href="#" title="Cosmetics">Cosmetics</a>
        </li>
        <li class="cat-list-item">
          <a href="#" title="Dresses">Dresses</a>
        </li>
        <li class="cat-list-item">
          <a href="#" title="Jumpsuits">Jumpsuits</a>
        </li>
      </ul>

    </div>
  </div>

  <div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
    <div class="offcanvas-header justify-content-center">
      <h5 class="offcanvas-title" id="offcanvasCartLabel">Your Cart</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <div class="order-md-last">
        @php
        use App\Models\CartModel;

        $cartItems = auth()->check()
          ? CartModel::with('sepatu')->where('user_id', auth()->id())->get()
          : collect();
        $cartCount = $cartItems->sum('jumlah');
        @endphp
        <h4 class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-primary">Your Cart</span>
          <span class="badge bg-primary rounded-pill">{{ $cartCount }}</span>
        </h4>
        
        @if ($cartCount > 0)
          <ul class="list-group mb-3">
            @foreach ($cartItems as $item)
              {{-- <li class="list-group-item d-flex justify-content-between lh-sm"> --}}
                {{-- <div class="card mb-3" style="max-width: 540px;">
                  <div class="row g-0">
                    <div class="col-md-4">
                      <img src="{{ asset('storage/' . $item->sepatu->image_sepatu) }}" alt="{{ $item->sepatu->title }}" class="me-2 rounded" style="width: 50px; height: 50px; object-fit: cover;">
                    </div>
                    <div class="col-md-8">
                      <div class="card-body">
                        <h6 class="my-0">{{ $item->sepatu->title }}</h6>
                        <p class="card-text"><small class="text-body-secondary">Qty: {{ $item->jumlah }}</small></p>
                        <form id="deleteForm_{{ $item->id }}" action="{{ route('cart.delete', $item->id) }}" method="POST" onsubmit="return false;">
                          @csrf
                          @method('DELETE')
                          <button type="button" class="btn btn-sm btn-danger ms-2" onclick="confirmDelete({{ $item->id }})">Hapus</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div> --}}
                <li class="list-group-item d-flex flex-column justify-content-between lh-sm p-3">
                  <div class="border rounded border-black ">
                    <div class="p-2">
                      <div class="d-flex align-items-center mb-2">
                        <img src="{{ asset('storage/' . $item->sepatu->image_sepatu) }}" alt="{{ $item->sepatu->title }}" class="me-3 rounded" style="width: 50px; height: 50px; object-fit: cover;">
                        <div>
                          <h6 class="my-0">Nama : {{ $item->sepatu->title }}</h6>
                          <small class="text-body-secondary">Qty: {{ $item->jumlah }}</small>
                        </div>
                      </div>
                      <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="text-body-secondary fw-bold">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</span>
                        <form id="deleteForm_{{ $item->id }}" action="{{ route('cart.delete', $item->id) }}" method="POST" onsubmit="return false;" class="mb-0">
                          @csrf
                          @method('DELETE')
                          <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $item->id }})">Hapus</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </li>
                {{-- <span class="text-body-secondary">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</span> --}}
              </li>
            @endforeach
            <li class="list-group-item d-flex justify-content-between">
              <span>Total (IDR)</span>
              <strong>Rp {{ number_format($cartItems->sum('total_harga'), 0, ',', '.') }}</strong>
            </li>
          </ul>
          <a href="{{ route('user.cart.index') }}" class="w-100 btn btn-primary btn-lg mb-2">Lihat Detail Keranjang</a>
          {{-- <a href="{{ route('checkout') }}" class="w-100 btn btn-success btn-lg">Continue to Checkout</a> --}}
        @else
          <p class="text-center text-muted">Keranjang kosong</p>
        @endif
      </div>
    </div>
  </div>

  <nav class="navbar navbar-expand-lg bg-light text-uppercase fs-6 p-3 border-bottom align-items-center">
    <div class="container-fluid">
      <div class="row justify-content-between align-items-center w-100">

        <div class="col-auto">
            <a class="navbar-brand" href="{{route('user.home')}}">
              <h5 class="widget-title text-uppercase pt-4">KickCasual</h5>
            </a>
        </div>

          <div class="col-auto">
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
              <span class="navbar-toggler-icon"></span>
            </button>
    
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
              <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
              </div>
    
              <div class="offcanvas-body">
                <ul class="navbar-nav justify-content-end flex-grow-1 gap-1 gap-md-5 pe-3">
                  <li class="nav-item">
                    <a class="nav-link {{ request()->is('user/home') || request()->is('/') ? 'active' : '' }}" href="{{ route('user.home') }}">Home</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link {{ request()->is('user/shop') ? 'active' : '' }}" href="{{ route('user.shop') }}">Product</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link {{ request()->is('user/checkout*') ? 'active' : '' }}" href="{{ route('user.checkout.index') }}">Checkout</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link {{ request()->is('user/shipping*') ? 'active' : '' }}" href="{{ route('user.shipping.index') }}">Shipping</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link {{ request()->is('contact') ? 'active' : '' }}" href="#">Contact</a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
    
          <!-- Icons -->
          <div class="col-3 col-lg-auto">
            <ul class="list-unstyled d-flex m-0">
              <li class="d-none d-lg-block">
                <a href="index.html" class="text-uppercase mx-3" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-controls="offcanvasCart">
                  Cart <span class="badge bg-primary rounded-pill">{{ $cartCount }}</span>
                </a>
              </li>
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
              <li class="d-lg-none mx-2">
                <a href="#">
                  <svg width="24" height="24" viewBox="0 0 24 24">
                    <use xlink:href="#heart"></use>
                  </svg>
                </a>
              </li>
              <li class="d-lg-none mx-2">
                <a href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-controls="offcanvasCart">
                  <svg width="24" height="24" viewBox="0 0 24 24">
                    <use xlink:href="#cart"></use>
                  </svg>
                </a>
              </li>
            </ul>
          </div>
    
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