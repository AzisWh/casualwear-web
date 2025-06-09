<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>@yield('title', 'User Page')</title>

    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/logo-black.jpeg') }}" />

    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />

    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="{{ asset('usertemplate/css/styles.css') }}" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.6.9/dist/sweetalert2.min.css" rel="stylesheet">
    {{-- <meta http-equiv="Content-Security-Policy" content="script-src 'self'" /> --}}
    {{-- <meta http-equiv="Content-Security-Policy" content="script-src 'self' 'unsafe-eval';"> --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

  </head>
  <body>
    @include('sweetalert::alert')
    {{-- Navbar --}}
    @include('user.components.navbar')

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

    {{-- Flash Messages --}}

    {{-- Header --}}
    @yield('header')

    {{-- Content --}}
    <main class="py-2">
      @yield('content')
    </main>

    {{-- Footer --}}
    @include('user.components.footer')

    <!-- Bootstrap core JS-->
    {{-- <script src="/lte/plugins/jquery/jquery.min.js"></script> --}}
    {{-- <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script> --}}
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.6.9/dist/sweetalert2.all.min.js"></script>
    <!-- Core theme JS-->
    <script src="{{ asset('usertemplate/js/scripts.js') }}"></script>
    {{-- midtrans --}}
    @yield('script')
    {{-- <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script> --}}
  </body>
</html>
