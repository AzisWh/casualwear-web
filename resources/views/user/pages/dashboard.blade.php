@extends('user.layout.userlayout')

@section('title', 'Home Page')


@section('content')
<section id="billboard" class="bg-light py-5">
  <div class="container">
    <div class="row justify-content-center">
      <h1 class="section-title text-center mt-4" data-aos="fade-up">Welcome to KickCasual</h1>
      <div class="col-md-6 text-center" data-aos="fade-up" data-aos-delay="300">
        <p>
          Temukan gaya kasual terbaikmu di <strong>KickCasual</strong> — rumahnya sepatu stylish, nyaman, dan kekinian. Kami hadir untuk kamu yang ingin tampil simpel tapi tetap standout di setiap langkah. Dari jalan santai, hangout bareng teman, sampai ngedate santuy, sepatu kami siap nemenin!
        </p>
      </div>
    </div>
    <div class="row">
      <div class="swiper main-swiper py-4" data-aos="fade-up" data-aos-delay="600">
        <div class="swiper-wrapper d-flex border-animation-left">
          
          <!-- Sepatu Biru -->
          <div class="swiper-slide">
            <div class="banner-item image-zoom-effect">
              <div class="image-holder">
                <a href="#"><img src="{{asset('assets/img/sepatu-biru.jpeg')}}" alt="sepatu biru" class="img-fluid"></a>
              </div>
              <div class="banner-content py-4">
                <h5 class="element-title text-uppercase">
                  <a href="#" class="item-anchor">Blue Street Casual</a>
                </h5>
                <p>Tampil santai tapi tetap fresh. Sepatu biru ini cocok buat hangout sore dan gaya denim kasualmu.</p>
                <div class="btn-left">
                  <a href="{{route('user.shop')}}" class="btn-link fs-6 text-uppercase item-anchor text-decoration-none">Discover Now</a>
                </div>
              </div>
            </div>
          </div>
      
          <!-- Sepatu Luxury -->
          <div class="swiper-slide">
            <div class="banner-item image-zoom-effect">
              <div class="image-holder">
                <a href="#"><img src="{{asset('assets/img/luxury.webp')}}" alt="sepatu putih luxury" class="img-fluid"></a>
              </div>
              <div class="banner-content py-4">
                <h5 class="element-title text-uppercase">
                  <a href="#" class="item-anchor">White Luxe Edition</a>
                </h5>
                <p>Kesan mewah dan bersih dalam satu langkah. Sepatu putih ini buat kamu yang suka tampil classy tanpa ribet.</p>
                <div class="btn-left">
                  <a href="{{route('user.shop')}}" class="btn-link fs-6 text-uppercase item-anchor text-decoration-none">Discover Now</a>
                </div>
              </div>
            </div>
          </div>
      
          <!-- Sepatu Kuning -->
          <div class="swiper-slide">
            <div class="banner-item image-zoom-effect">
              <div class="image-holder">
                <a href="#"><img src="{{asset('assets/img/sepatu-yellow.jpeg')}}" alt="sepatu kuning" class="img-fluid"></a>
              </div>
              <div class="banner-content py-4">
                <h5 class="element-title text-uppercase">
                  <a href="#" class="item-anchor">Sunset Vibe</a>
                </h5>
                <p>Warna kuning cerah buat kamu yang berani beda. Tampil energik setiap saat, kapan pun kamu melangkah.</p>
                <div class="btn-left">
                  <a href="{{route('user.shop')}}" class="btn-link fs-6 text-uppercase item-anchor text-decoration-none">Discover Now</a>
                </div>
              </div>
            </div>
          </div>
      
          <!-- Sepatu Cream -->
          <div class="swiper-slide">
            <div class="banner-item image-zoom-effect">
              <div class="image-holder">
                <a href="#"><img src="{{asset('assets/img/sepatu-cream.jpeg')}}" alt="sepatu cream" class="img-fluid"></a>
              </div>
              <div class="banner-content py-4">
                <h5 class="element-title text-uppercase">
                  <a href="#" class="item-anchor">Everyday Comfort</a>
                </h5>
                <p>Netral dan nyaman dipakai seharian. Sepatu cream ini cocok untuk kamu yang suka gaya minimalis stylish.</p>
                <div class="btn-left">
                  <a href="{{route('user.shop')}}" class="btn-link fs-6 text-uppercase item-anchor text-decoration-none">Discover Now</a>
                </div>
              </div>
            </div>
          </div>
      
          <!-- Sepatu Biru Navy -->
          <div class="swiper-slide">
            <div class="banner-item image-zoom-effect">
              <div class="image-holder">
                <a href="#"><img src="{{asset('assets/img/sepatu-birunavy.jpeg')}}" alt="sepatu biru navy" class="img-fluid"></a>
              </div>
              <div class="banner-content py-4">
                <h5 class="element-title text-uppercase">
                  <a href="#" class="item-anchor">Navy Move</a>
                </h5>
                <p>Tegas dan kalem, warna navy ini bikin tampilanmu makin mantap. Cocok buat aktivitas sehari-hari yang dinamis.</p>
                <div class="btn-left">
                  <a href="{{route('user.shop')}}" class="btn-link fs-6 text-uppercase item-anchor text-decoration-none">Discover Now</a>
                </div>
              </div>
            </div>
          </div>
      
        </div>
        <div class="swiper-pagination"></div>
      </div>
      
      <div class="icon-arrow icon-arrow-left"><svg width="50" height="50" viewBox="0 0 24 24">
          <use xlink:href="#arrow-left"></use>
        </svg></div>
      <div class="icon-arrow icon-arrow-right"><svg width="50" height="50" viewBox="0 0 24 24">
          <use xlink:href="#arrow-right"></use>
        </svg></div>
    </div>
  </div>
</section>

<section class="features py-5">
  <div class="container">
    <div class="row">
      <div class="col-md-3 text-center" data-aos="fade-in" data-aos-delay="0">
        <div class="py-5">
          <svg width="38" height="38" viewBox="0 0 24 24">
            <use xlink:href="#calendar"></use>
          </svg>
          <h4 class="element-title text-capitalize my-3">Book An Appointment</h4>
          <p>At imperdiet dui accumsan sit amet nulla risus est ultricies quis.</p>
        </div>
      </div>
      <div class="col-md-3 text-center" data-aos="fade-in" data-aos-delay="300">
        <div class="py-5">
          <svg width="38" height="38" viewBox="0 0 24 24">
            <use xlink:href="#shopping-bag"></use>
          </svg>
          <h4 class="element-title text-capitalize my-3">Pick up in store</h4>
          <p>At imperdiet dui accumsan sit amet nulla risus est ultricies quis.</p>
        </div>
      </div>
      <div class="col-md-3 text-center" data-aos="fade-in" data-aos-delay="600">
        <div class="py-5">
          <svg width="38" height="38" viewBox="0 0 24 24">
            <use xlink:href="#gift"></use>
          </svg>
          <h4 class="element-title text-capitalize my-3">Special packaging</h4>
          <p>At imperdiet dui accumsan sit amet nulla risus est ultricies quis.</p>
        </div>
      </div>
      <div class="col-md-3 text-center" data-aos="fade-in" data-aos-delay="900">
        <div class="py-5">
          <svg width="38" height="38" viewBox="0 0 24 24">
            <use xlink:href="#arrow-cycle"></use>
          </svg>
          <h4 class="element-title text-capitalize my-3">free global returns</h4>
          <p>At imperdiet dui accumsan sit amet nulla risus est ultricies quis.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="collection bg-light position-relative py-5">
  <div class="container">
    <div class="row">
      <div class="title-xlarge text-uppercase txt-fx domino">Collection</div>
      <div class="collection-item d-flex flex-wrap my-5">
        <div class="col-md-6 column-container">
          <div class="image-holder">
            <img src="{{asset('assets/img/sepatu-biru.jpeg')}}" alt="collection" class="product-image img-fluid">
          </div>
        </div>
        <div class="col-md-6 column-container bg-white">
          <div class="collection-content p-5 m-0 m-md-5">
            <h3 class="element-title text-uppercase">Timeless Casual Drop</h3>
            <p>
              Step into timeless style with our latest casual collection from KickCasual. 
              Designed for comfort and everyday wear, these shoes are perfect for any occasion — whether you're heading to class,
              meeting friends, or enjoying a laid-back weekend. Clean lines and soft tones make this your go-to pair for effortless street style.
            </p>
            <a href="{{route('user.shop')}}" class="btn btn-dark text-uppercase mt-3">Shop Collection</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection