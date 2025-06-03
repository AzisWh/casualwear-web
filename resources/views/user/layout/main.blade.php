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
  </head>
  <body>
    @include('sweetalert::alert')
    {{-- Navbar --}}
    @include('user.components.navbar')

    {{-- Header --}}
    @yield('header')

    {{-- Content --}}
    <main>
      @yield('content')
    </main>

    {{-- Footer --}}
    @include('user.components.footer')

    <!-- Bootstrap core JS-->
    <script src="/lte/plugins/jquery/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.6.9/dist/sweetalert2.all.min.js"></script>
    <!-- Core theme JS-->
    <script src="{{ asset('usertemplate/js/scripts.js') }}"></script>
    {{-- midtrans --}}
    @yield('script')
    {{-- <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script> --}}
  </body>
</html>
