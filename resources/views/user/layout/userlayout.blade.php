<!DOCTYPE html>
<html lang="en">

<head>
  <title>@yield('title', 'User Page')</title>
  <meta charset="utf-8">
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/logo-black.jpeg') }}" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="author" content="TemplatesJungle">
  <meta name="keywords" content="ecommerce,fashion,store">
  <meta name="description" content="Bootstrap 5 Fashion Store HTML CSS Template">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
  <link rel="stylesheet" href="{{asset('kaira/css/vendor.css')}}">
  {{-- <link rel="stylesheet" href="kaira/css/style.css"> --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
  <link rel="stylesheet" href="{{asset('kaira/style.css')}}">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Marcellus&display=swap"
    rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.6.9/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="homepage">
  
    @include('sweetalert::alert')
    @include('user.components.symbol')

    <div class="preloader text-white fs-6 text-uppercase overflow-hidden"></div>

    @include('user.components.usernavbar')

     <!-- Modal Edit Profile -->
     @auth
     <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
       <div class="modal-dialog">
         <div class="modal-content">
           <div class="modal-header">
             <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <div class="modal-body">
             <form id="editProfileForm" action="{{ route('user.profile.update') }}" method="POST">
               @csrf
               @method('PUT')
               <div class="mb-3">
                 <label for="nama_depan" class="form-label">Nama Depan</label>
                 <input type="text" class="form-control" id="nama_depan" name="nama_depan" value="{{ Auth::user()->nama_depan }}" required>
               </div>
               <div class="mb-3">
                 <label for="nama_belakang" class="form-label">Nama Belakang</label>
                 <input type="text" class="form-control" id="nama_belakang" name="nama_belakang" value="{{ Auth::user()->nama_belakang }}" required>
               </div>
               <div class="mb-3">
                 <label for="no_hp" class="form-label">No HP</label>
                 <input type="text" class="form-control" id="no_hp" name="no_hp" value="{{ Auth::user()->no_hp }}">
               </div>
               <div class="mb-3">
                 <label for="alamat_tinggal" class="form-label">Alamat Tinggal</label>
                 <textarea class="form-control" id="alamat_tinggal" name="alamat_tinggal" rows="3">{{ Auth::user()->alamat_tinggal }}</textarea>
               </div>
               <div class="mb-3">
                 <label for="asal_kota" class="form-label">Asal Kota</label>
                 <input type="text" class="form-control" id="asal_kota" name="asal_kota" value="{{ Auth::user()->asal_kota }}">
               </div>
               <div class="mb-3">
                 <label for="asal_provinsi" class="form-label">Asal Provinsi</label>
                 <input type="text" class="form-control" id="asal_provinsi" name="asal_provinsi" value="{{ Auth::user()->asal_provinsi }}">
               </div>
               <div class="mb-3">
                 <label for="kodepos" class="form-label">Kode Pos</label>
                 <input type="text" class="form-control" id="kodepos" name="kodepos" value="{{ Auth::user()->kodepos }}">
               </div>
               <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
             </form>
           </div>
         </div>
       </div>
     </div>
     @endauth

    <section class="">
        @yield('content')
    </section>

 {{-- footer --}}
 @include('user.components.userfooter')
 <script>
    $(document).ready(function() {
      // Cek apakah data alamat lengkap saat modal dibuka
      $('#editProfileModal').on('show.bs.modal', function() {
        const user = @json(Auth::user());
        const isAddressComplete = user.alamat_tinggal && user.asal_kota && user.asal_provinsi && user.kodepos;

        if (!isAddressComplete) {
          Swal.fire({
            title: 'Data Alamat Belum Lengkap',
            text: 'Silakan lengkapi data alamat Anda untuk mempermudah pengiriman.',
            icon: 'warning',
            showCancelButton: false,
            confirmButtonText: 'OK',
            allowOutsideClick: false
          }).then((result) => {
            if (result.isConfirmed) {
              $('#editProfileModal').find('input, textarea').focus();
            }
          });
        }
      });

      // Handle form submission
      $('#editProfileForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
          url: $(this).attr('action'),
          type: 'POST',
          data: $(this).serialize(),
          success: function(response) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: 'Profile berhasil diperbarui!',
              timer: 2000,
              showConfirmButton: false
            }).then(() => {
              $('#editProfileModal').modal('hide');
              location.reload();
            });
          },
          error: function(xhr) {
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: 'Terjadi kesalahan saat menyimpan data.',
              timer: 2000,
              showConfirmButton: false
            });
          }
        });
      });
    });
  </script>
  <script src="{{asset('kaira/js/jquery.min.js')}}"></script>
  <script src="{{asset('kaira/js/plugins.js')}}"></script>
  <script src="{{asset('kaira/js/SmoothScroll.js')}}"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
  <script src="{{asset('kaira/js/script.min.js')}}"></script>
  @yield('script')
</body>

</html>