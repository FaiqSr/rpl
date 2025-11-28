<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>ChickPatrol Store</title>
  <!-- Bootstrap 5 (minimal usage) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Tailwind CSS via Vite -->
  @vite(['resources/css/app.css'])
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" />
  <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
  <style>
    :root {
      --primary-green: #69B578;
      --dark-green: #5a8c64;
      --cream: #F5E6D3;
      --yellow: #F4C430;
      --light-yellow: #FFF8DC;
      --brown: #6B5D4F;
      --light-green: #8FBC8F;
    }
    body { background:#FAFAF8; font-family: 'Inter', -apple-system, sans-serif; }
    .skeleton { background:#F5E6D3; border-radius:12px; }
    .chip { 
      background:#FFF8DC; 
      border:1px solid #F4C430; 
      color: #6B5D4F; 
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      transform: translateZ(0);
      will-change: background-color, color, border-color;
    }
    .chip.active { 
      border-color:#F4C430; 
      background: #F4C430; 
      color:#2F2F2F; 
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(244, 196, 48, 0.3);
    }
    .card-border { border:1px solid #e6ebe7; transition: all 0.2s; }
    .card-border:hover { border-color: #F4C430; box-shadow: 0 2px 8px rgba(244, 196, 48, 0.15); }
    .footer-bg { background:#F5E6D3; }
    .product-card { 
      text-decoration: none !important; 
      border-bottom: none !important;
      outline: none !important;
      display: flex !important;
      flex-direction: column !important;
      height: 100% !important;
      min-height: 280px !important;
    }
    .product-card * { 
      text-decoration: none !important; 
      border-bottom: none !important;
    }
    .product-card:hover { 
      text-decoration: none !important; 
      border-bottom: none !important;
    }
    .product-card:focus { 
      text-decoration: none !important; 
      border-bottom: none !important;
      outline: none !important;
    }
    .product-card a { 
      text-decoration: none !important; 
      border-bottom: none !important;
    }
    .product-card img {
      width: 100% !important;
      height: 144px !important;
      object-fit: cover !important;
      object-position: center !important;
      flex-shrink: 0 !important;
    }
    .product-card-content {
      display: flex !important;
      flex-direction: column !important;
      flex-grow: 1 !important;
      justify-content: flex-start !important;
      gap: 0 !important;
      margin: 0 !important;
      padding: 0 !important;
    }
    .product-card-content > div {
      margin: 0 !important;
      padding: 0 !important;
    }
    .product-card-content > div > * {
      margin: 0 !important;
      padding: 0 !important;
    }
    .product-card-content .flex.items-center {
      padding: 0 !important;
    }
    .product-card-content .flex.items-center > * {
      margin: 0 !important;
      padding: 0 !important;
    }
    /* Allow margin-top for rating - more specific selector */
    .product-card-content > div > .flex.items-center.gap-1 {
      margin: 0 !important;
      margin-top: 28px !important;
      padding: 0 !important;
    }
    .product-card-content .text-xs,
    .product-card-content .text-sm,
    .product-card-content .text-base {
      margin: 0 !important;
      padding: 0 !important;
    }
    @media (min-width: 768px) {
      #productGrid {
        grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
      }
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
      main {
        padding: 1rem !important;
      }
      
      /* Banner Section */
      section[style*="width: 1205px"] {
        width: 100% !important;
        height: auto !important;
        max-width: 100% !important;
      }
      
      section[style*="width: 1205px"] > div {
        flex-direction: column !important;
        gap: 0.5rem !important;
      }
      
      section[style*="width: 1205px"] > div > div[style*="width: 365px"] {
        width: 100% !important;
        height: 200px !important;
      }
      
      section[style*="width: 1205px"] > div > div[style*="width: 836px"] {
        width: 100% !important;
        height: 200px !important;
      }
      
      /* Categories Section */
      section.mb-6[style*="margin-top: 2rem"] {
        margin-top: 1rem !important;
        margin-bottom: 1rem !important;
      }
      
      .categories-container {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
        justify-content: flex-start !important;
        align-items: flex-start !important;
        width: 1205px !important;
        max-width: 1205px !important;
        margin: 0 auto 1.5rem auto !important;
        padding: 0 !important;
        box-sizing: border-box !important;
      }
      
      @media (max-width: 1200px) {
        .categories-container {
          max-width: 100% !important;
          flex-wrap: wrap !important;
        }
      }
      
      .category-item {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        text-align: center !important;
        margin-bottom: 1rem !important;
        width: 213px !important;
        max-width: 213px !important;
        flex: 0 0 213px !important;
        flex-shrink: 0 !important;
        box-sizing: border-box !important;
        margin-right: 35px !important;
      }
      
      .category-item:nth-child(5n) {
        margin-right: 0 !important;
      }
      
      @media (min-width: 1200px) {
        .categories-container {
          flex-wrap: wrap !important;
          justify-content: flex-start !important;
        }
        .category-item {
          margin-right: 35px !important;
        }
        .category-item:nth-child(5n) {
          margin-right: 0 !important;
        }
      }
      
      .category-image {
        width: 213px !important;
        height: 125px !important;
        max-width: 213px !important;
        max-height: 125px !important;
        min-width: 213px !important;
        min-height: 125px !important;
        border-radius: 8px !important;
        object-fit: cover !important;
        object-position: center !important;
        margin-bottom: 0.75rem !important;
        display: block !important;
        flex-shrink: 0 !important;
        box-sizing: border-box !important;
      }
      
      .category-name {
        width: 213px !important;
        max-width: 213px !important;
        word-wrap: break-word !important;
        margin: 0 auto !important;
        line-height: 1.4 !important;
        font-size: 0.875rem !important;
        text-align: center !important;
        display: block !important;
      }
      
      @media (min-width: 1200px) {
        .categories-container {
          justify-content: flex-start !important;
          width: 1205px !important;
          max-width: 1205px !important;
          flex-wrap: nowrap !important;
        }
        .category-item {
          width: 213px !important;
          max-width: 213px !important;
          flex: 0 0 213px !important;
          min-width: 213px !important;
          margin-right: 35px !important;
        }
        .category-item:nth-child(5n) {
          margin-right: 0 !important;
        }
        .category-image {
          width: 213px !important;
          height: 125px !important;
          max-width: 213px !important;
          max-height: 125px !important;
          min-width: 213px !important;
          min-height: 125px !important;
        }
        .category-name {
          width: 213px !important;
          max-width: 213px !important;
        }
      }
      
      @media (min-width: 768px) and (max-width: 1199px) {
        .categories-container {
          justify-content: flex-start !important;
          gap: 1rem !important;
        }
        .category-item {
          width: calc(25% - 0.75rem) !important;
          margin-right: 0 !important;
        }
        .category-image {
          width: 100% !important;
          max-width: 100% !important;
          height: 125px !important;
        }
        .category-name {
          max-width: 100% !important;
        }
      }
      
      @media (max-width: 767px) {
        .categories-container {
          gap: 1rem !important;
          justify-content: flex-start !important;
        }
        .category-item {
          width: calc(50% - 0.5rem) !important;
          margin-right: 0 !important;
        }
        .category-image {
          width: 100% !important;
          max-width: 100% !important;
          height: 100px !important;
        }
        .category-name {
          max-width: 100% !important;
        }
      }
      
      @media (max-width: 480px) {
        .category-item {
          width: 100% !important;
        }
        .category-image {
          height: 120px !important;
        }
      }
      
      /* Product Grid */
      #productGrid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 0.75rem !important;
      }
      
      .product-card {
        min-height: 240px !important;
      }
      
      .product-card img {
        height: 120px !important;
      }
    }
    
    @media (max-width: 480px) {
      section.mb-6[style*="margin-top: 2rem"] > div[style*="width: 1205px"] > div {
        width: 100% !important;
      }
      
      #productGrid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 0.5rem !important;
      }
    }
  </style>
</head>
<body class="min-h-screen">
  @include('partials.navbar')

  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Hero banners - 3 Part Layout with Carousel -->
    @php
      $squareBanners = isset($banners['square']) ? $banners['square'] : collect([]);
      $rectTopBanners = isset($banners['rectangle_top']) ? $banners['rectangle_top'] : collect([]);
      $rectBottomBanners = isset($banners['rectangle_bottom']) ? $banners['rectangle_bottom'] : collect([]);
      $hasAnyBanner = $squareBanners->count() > 0 || $rectTopBanners->count() > 0 || $rectBottomBanners->count() > 0;
    @endphp
    @if($hasAnyBanner)
    <section class="mb-6" style="width: 1205px; height: 365px; border-radius: 8px; opacity: 1;">
      <div style="display: flex; gap: 4px; width: 100%; height: 100%;">
        <!-- Banner Persegi (Kiri) -->
        <div style="width: 365px; height: 365px; border-radius: 8px; overflow: hidden; position: relative;">
          @if($squareBanners->count() > 0)
            <div id="squareBannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000" style="width: 100%; height: 100%;">
              <div class="carousel-inner" style="width: 100%; height: 100%;">
                @foreach($squareBanners as $index => $banner)
                  <div class="carousel-item {{ $index === 0 ? 'active' : '' }}" style="width: 100%; height: 100%; position: relative;">
                    @if($banner->link_url)
                      <a href="{{ $banner->link_url }}" style="display: block; width: 100%; height: 100%;">
                    @endif
                    <img src="{{ $banner->image_url }}" 
                         alt="{{ $banner->title ?? 'Banner' }}" 
                         style="width: 365px; height: 365px; border-radius: 8px; object-fit: cover; object-position: center; image-rendering: auto; -webkit-backface-visibility: hidden; -webkit-transform: translateZ(0); transform: translateZ(0); will-change: transform;"
                         loading="eager"
                         fetchpriority="high"
                         decoding="async"
                         onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzNjUiIGhlaWdodD0iMzY1IiB2aWV3Qm94PSIwIDAgMzY1IDM2NSI+PHJlY3Qgd2lkdGg9IjM2NSIgaGVpZ2h0PSIzNjUiIGZpbGw9IiNmOGQ3ZGEiIHJ4PSI4Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGRvbWluYW50LWJhc2VsaW5lPSJtaWRkbGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiM3MjFjMjQiIGZvbnQtc2l6ZT0iMTRweCIgZm9udC1mYW1pbHk9InNhbnMtc2VyaWYiPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='">
                    @if($banner->title)
                      <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent); padding: 12px 16px; border-radius: 0 0 8px 8px;">
                        <h3 style="color: white; font-size: 16px; font-weight: 600; margin: 0; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">{{ $banner->title }}</h3>
      </div>
                    @endif
                    @if($banner->link_url)
                      </a>
                    @endif
                  </div>
                @endforeach
              </div>
              @if($squareBanners->count() > 1)
              <button class="carousel-control-prev" type="button" data-bs-target="#squareBannerCarousel" data-bs-slide="prev" style="left: 5px;">
                <span class="carousel-control-prev-icon"></span>
              </button>
              <button class="carousel-control-next" type="button" data-bs-target="#squareBannerCarousel" data-bs-slide="next" style="right: 5px;">
                <span class="carousel-control-next-icon"></span>
        </button>
              @endif
            </div>
          @else
            <div style="width: 365px; height: 365px; background: #f9fafb; border-radius: 8px;"></div>
          @endif
        </div>
        
        <!-- Banner Persegi Panjang (Kanan) -->
        <div style="display: flex; flex-direction: column; gap: 4px; width: 836px; height: 365px;">
          <!-- Banner Persegi Panjang Atas -->
          <div style="width: 836px; height: 180.5px; border-radius: 8px; overflow: hidden; position: relative;">
            @if($rectTopBanners->count() > 0)
              <div id="rectTopBannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000" style="width: 100%; height: 100%;">
                <div class="carousel-inner" style="width: 100%; height: 100%;">
                  @foreach($rectTopBanners as $index => $banner)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}" style="width: 100%; height: 100%; position: relative;">
                      @if($banner->link_url)
                        <a href="{{ $banner->link_url }}" style="display: block; width: 100%; height: 100%;">
                      @endif
                      <img src="{{ $banner->image_url }}" 
                           alt="{{ $banner->title ?? 'Banner' }}" 
                           style="width: 836px; height: 180.5px; border-radius: 8px; object-fit: cover; object-position: center; image-rendering: auto; -webkit-backface-visibility: hidden; -webkit-transform: translateZ(0); transform: translateZ(0); will-change: transform;"
                           loading="eager"
                           fetchpriority="high"
                           decoding="async"
                           onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MzYiIGhlaWdodD0iMTgwIiB2aWV3Qm94PSIwIDAgODM2IDE4MCI+PHJlY3Qgd2lkdGg9IjgzNiIgaGVpZ2h0PSIxODAiIGZpbGw9IiNmOGQ3ZGEiIHJ4PSI4Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGRvbWluYW50LWJhc2VsaW5lPSJtaWRkbGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiM3MjFjMjQiIGZvbnQtc2l6ZT0iMTRweCIgZm9udC1mYW1pbHk9InNhbnMtc2VyaWYiPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='">
                      @if($banner->title)
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent); padding: 10px 16px; border-radius: 0 0 8px 8px;">
                          <h3 style="color: white; font-size: 15px; font-weight: 600; margin: 0; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">{{ $banner->title }}</h3>
                        </div>
                      @endif
                      @if($banner->link_url)
                        </a>
                      @endif
                    </div>
                  @endforeach
                </div>
                @if($rectTopBanners->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#rectTopBannerCarousel" data-bs-slide="prev" style="left: 5px;">
                  <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#rectTopBannerCarousel" data-bs-slide="next" style="right: 5px;">
                  <span class="carousel-control-next-icon"></span>
                </button>
                @endif
              </div>
            @else
              <div style="width: 836px; height: 180.5px; background: #f9fafb; border-radius: 8px;"></div>
            @endif
      </div>
      
          <!-- Banner Persegi Panjang Bawah -->
          <div style="width: 836px; height: 180.5px; border-radius: 8px; overflow: hidden; position: relative;">
            @if($rectBottomBanners->count() > 0)
              <div id="rectBottomBannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000" style="width: 100%; height: 100%;">
                <div class="carousel-inner" style="width: 100%; height: 100%;">
                  @foreach($rectBottomBanners as $index => $banner)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}" style="width: 100%; height: 100%; position: relative;">
                      @if($banner->link_url)
                        <a href="{{ $banner->link_url }}" style="display: block; width: 100%; height: 100%;">
                      @endif
                      <img src="{{ $banner->image_url }}" 
                           alt="{{ $banner->title ?? 'Banner' }}" 
                           style="width: 836px; height: 180.5px; border-radius: 8px; object-fit: cover; object-position: center; image-rendering: auto; -webkit-backface-visibility: hidden; -webkit-transform: translateZ(0); transform: translateZ(0); will-change: transform;"
                           loading="eager"
                           fetchpriority="high"
                           decoding="async"
                           onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MzYiIGhlaWdodD0iMTgwIiB2aWV3Qm94PSIwIDAgODM2IDE4MCI+PHJlY3Qgd2lkdGg9IjgzNiIgaGVpZ2h0PSIxODAiIGZpbGw9IiNmOGQ3ZGEiIHJ4PSI4Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGRvbWluYW50LWJhc2VsaW5lPSJtaWRkbGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiM3MjFjMjQiIGZvbnQtc2l6ZT0iMTRweCIgZm9udC1mYW1pbHk9InNhbnMtc2VyaWYiPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='">
                      @if($banner->title)
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent); padding: 10px 16px; border-radius: 0 0 8px 8px;">
                          <h3 style="color: white; font-size: 15px; font-weight: 600; margin: 0; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">{{ $banner->title }}</h3>
      </div>
                      @endif
                      @if($banner->link_url)
                        </a>
                      @endif
                    </div>
                  @endforeach
                </div>
                @if($rectBottomBanners->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#rectBottomBannerCarousel" data-bs-slide="prev" style="left: 5px;">
                  <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#rectBottomBannerCarousel" data-bs-slide="next" style="right: 5px;">
                  <span class="carousel-control-next-icon"></span>
                </button>
                @endif
              </div>
        @else
              <div style="width: 836px; height: 180.5px; background: #f9fafb; border-radius: 8px;"></div>
        @endif
      </div>
    </div>
      </div>
    </section>
    @endif

    <!-- Categories dengan foto -->
    @if(isset($homepageCategories) && $homepageCategories && $homepageCategories->count() > 0)
    <section class="mb-6" style="margin-top: 2rem; margin-bottom: 2rem;">
      <h2 class="text-sm font-semibold text-gray-700 mb-4">Kategori</h2>
      <div class="categories-container" style="display: flex !important; flex-direction: row !important; flex-wrap: wrap !important; width: 1205px !important; max-width: 1205px !important; margin: 0 auto 1.5rem auto !important; align-items: flex-start !important; justify-content: flex-start !important;">
        @foreach($homepageCategories as $index => $category)
          <div class="category-item" 
               onclick="filterByHomepageCategory('{{ $category->slug }}')"
               style="cursor: pointer; width: 213px !important; flex: 0 0 213px !important; flex-shrink: 0 !important; margin-bottom: 1rem !important; margin-right: {{ ($loop->iteration % 5 == 0) ? '0' : '35' }}px !important; display: flex !important; flex-direction: column !important; align-items: center !important; text-align: center !important;">
            <img src="{{ $category->image_url }}" 
                 alt="{{ $category->name }}" 
                 class="category-image"
                 style="width: 213px !important; height: 125px !important; object-fit: cover !important; object-position: center !important; border-radius: 8px !important; border: 1px solid #e5e7eb !important; display: block !important; flex-shrink: 0 !important; max-width: 213px !important; max-height: 125px !important; margin-bottom: 0.75rem !important;"
                 onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMTMiIGhlaWdodD0iMTI1IiB2aWV3Qm94PSIwIDAgMjEzIDEyNSI+PHJlY3Qgd2lkdGg9IjIxMyIgaGVpZ2h0PSIxMjUiIGZpbGw9IiNmOGQ3ZGEiIHJ4PSI4Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGRvbWluYW50LWJhc2VsaW5lPSJtaWRkbGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiM3MjFjMjQiIGZvbnQtc2l6ZT0iMTRweCIgZm9udC1mYW1pbHk9InNhbnMtc2VyaWYiPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='">
            <span class="text-sm text-gray-700 font-medium category-name" style="width: 213px !important; text-align: center !important; margin: 0 auto !important;">{{ $category->name }}</span>
      </div>
        @endforeach
      </div>
    </section>
    @endif

    <!-- For You section -->
    <section class="mb-4">
      <h2 class="text-lg font-bold text-gray-800 mb-4">For You</h2>

      <!-- Product grid -->
        @isset($products)
        @if($products->count() > 0)
      <div id="productGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
            @foreach($products as $product)
              <a href="{{ route('product.detail', $product->product_id) }}" class="product-card card-border bg-white rounded-lg p-3 block hover:shadow-md transition-all duration-200" data-name="{{ strtolower($product->name) }}" data-slug="{{ strtolower($product->slug ?? '') }}" data-category="{{ strtolower($product->category_id ?? '') }}">
              @php
                $img = optional($product->images->first())->url ?? null;
              @endphp
              @if($img)
                  <img src="{{ $img }}" alt="{{ $product->name }}" class="rounded-md" style="width: 100%; height: 144px; object-fit: cover; object-position: center; flex-shrink: 0; margin: 0; margin-bottom: 0.5rem; padding: 0; display: block;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                  <div class="skeleton rounded-md" style="display: none; width: 100%; height: 144px; align-items: center; justify-content: center; background: #f3f4f6; flex-shrink: 0; margin: 0; margin-bottom: 0.5rem; padding: 0;">
                    <i class="fa-solid fa-image text-gray-400" style="font-size: 1.5rem;"></i>
                  </div>
              @else
                  <div class="skeleton rounded-md flex items-center justify-center" style="width: 100%; height: 144px; background: #f3f4f6; flex-shrink: 0; margin: 0; margin-bottom: 0.5rem; padding: 0;">
                    <i class="fa-solid fa-image text-gray-400" style="font-size: 1.5rem;"></i>
                  </div>
              @endif
              <div class="product-card-content" style="margin: 0; padding: 0;">
                <div style="margin: 0; padding: 0;">
                  <div class="text-sm font-semibold text-gray-900 line-clamp-2" style="min-height: 2.5rem; max-height: 2.5rem; margin: 0; padding: 0; text-decoration: none !important; border-bottom: none !important; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.2;" title="{{ $product->name }}">{{ $product->name }}</div>
                  <div class="text-xs text-gray-600" style="margin: 0; padding: 0; text-decoration: none !important; border-bottom: none !important; line-height: 1.2; margin-top: 2px;">{{ $product->unit ?? '-' }}</div>
                  <div class="flex items-center" style="margin: 0; padding: 0; margin-top: 4px;">
                    <span class="text-base font-bold text-emerald-600" style="text-decoration: none !important; border-bottom: none !important; line-height: 1;">Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}</span>
                  </div>
              @php
                // Use accessor from Product model which already filters reviews with valid order_id
                $avgRating = $product->average_rating;
                $totalReviews = $product->total_reviews;
              @endphp
              @if($totalReviews > 0)
                  <div class="flex items-center gap-1" style="margin: 0 !important; margin-top: 20px !important; padding: 0;">
                <div class="flex items-center" style="line-height: 1;">
                  @for($i = 1; $i <= 5; $i++)
                        <i class="fa-star {{ $i <= round($avgRating) ? 'fa-solid text-warning' : 'fa-regular text-gray-300' }}" style="font-size: 11px;"></i>
                  @endfor
                    </div>
                    <span class="text-xs text-gray-600" style="line-height: 1;">({{ $totalReviews }})</span>
                  </div>
                  @else
                  <div style="margin: 0; padding: 0; height: 1rem; margin-top: 32px;"></div>
                  @endif
                </div>
              </div>
            </a>
            @endforeach
              </div>
        @else
          <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 60vh; width: 100%; text-align: center;">
            <i class="fa-solid fa-box-open text-gray-300 mb-4" style="font-size: 4rem;"></i>
            <h3 class="text-lg font-semibold text-gray-600 mb-2">Stok Produk Kosong</h3>
            <p class="text-sm text-gray-500">Maaf, produk dalam kategori ini sedang tidak tersedia.</p>
            </div>
        @endif
      @else
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 60vh; width: 100%; text-align: center;">
          <i class="fa-solid fa-box-open text-gray-300 mb-4" style="font-size: 4rem;"></i>
          <h3 class="text-lg font-semibold text-gray-600 mb-2">Stok Produk Kosong</h3>
          <p class="text-sm text-gray-500">Maaf, produk dalam kategori ini sedang tidak tersedia.</p>
      </div>
        @endisset

      <!-- Pagination -->
      @isset($products)
      <div class="mt-6">{{ $products->withQueryString()->links() }}</div>
      @endisset
    </section>
  </main>

  <!-- Footer -->
  <footer class="bg-white mt-12 border-t border-gray-200">
    <div class="w-full py-8">
      <p class="text-center text-sm text-gray-600">©2025, ChickPatrol. All Rights Reserved.</p>
    </div>
  </footer>

  <!-- Chat Modal for Buyer -->
  @if(Auth::check())
  <div class="modal fade" id="chatModal" tabindex="-1" role="dialog" aria-labelledby="chatModalLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content" style="height: 600px; display: flex; flex-direction: column;">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fa-solid fa-comments me-2"></i>Chat dengan Penjual
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0" style="flex: 1; display: flex; flex-direction: column; overflow: hidden;">
          <!-- Chat Messages -->
          <div id="buyerChatMessages" style="flex: 1; overflow-y: auto; padding: 1rem; background: #f8f9fa; display: flex; flex-direction: column; gap: 0.5rem; min-height: 0;">
            <div class="text-center p-4 text-gray-500">
              <i class="fa-solid fa-spinner fa-spin"></i> Memuat pesan...
            </div>
          </div>
          <!-- Chat Input -->
          <div class="border-top p-3 bg-white">
            <div class="input-group">
              <input type="text" id="buyerChatInput" class="form-control" placeholder="Ketik pesan disini..." onkeypress="if(event.key==='Enter') sendBuyerMessage()">
              <button class="btn btn-success" onclick="sendBuyerMessage()">
                <i class="fa-solid fa-paper-plane"></i> Kirim
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  <script src="{{ asset('js/navbar.js') }}"></script>
  <script>
    // Update cart count
    @if(Auth::check())
    function updateCartCount() {
      fetch('{{ route("cart.count") }}')
        .then(res => res.json())
        .then(data => {
          const badge = document.getElementById('cartBadge');
          if (badge) {
            if (data.count > 0) {
              badge.textContent = data.count;
              badge.style.display = 'inline-block';
            } else {
              badge.style.display = 'none';
            }
          }
        });
    }
    
    // Update chat unread count
    function updateChatCount() {
      const csrfToken = document.querySelector('meta[name="csrf-token"]');
      if (!csrfToken) {
        console.error('CSRF token not found');
        return;
      }
      
      fetch('/api/chat/unread-count', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken.content
        },
        credentials: 'same-origin'
      })
        .then(res => {
          if (!res.ok) {
            throw new Error('Network response was not ok');
          }
          return res.json();
        })
        .then(data => {
          const badge = document.getElementById('chatBadge');
          if (badge) {
            const unreadCount = data.unread_count || 0;
            if (unreadCount > 0) {
              badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
              badge.style.display = 'inline-block';
            } else {
              badge.style.display = 'none';
            }
          }
        })
        .catch(err => {
          console.error('Error loading chat count:', err);
          const badge = document.getElementById('chatBadge');
          if (badge) {
            badge.style.display = 'none';
          }
        });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
      updateCartCount();
      updateChatCount();
      // Refresh chat count every 30 seconds
      setInterval(updateChatCount, 30000);
    });
    @endif
  </script>
  <script>
    // Navbar functions (toggleCategory, toggleProfileDropdown) are now in navbar.js
    // Category tab logic
    // Article items - Load dari database
    const articleItems = [
      { title: 'Semua Artikel', url: '/articles' }
    ];
    
    @if(isset($articleCategories) && $articleCategories && $articleCategories->count() > 0)
      @foreach($articleCategories as $cat)
        articleItems.push({
          title: '{{ addslashes($cat->name) }}',
          url: '/articles?category={{ $cat->slug }}'
        });
      @endforeach
    @endif
    
    // Belanja items - Load dari homepage categories (untuk filter)
    const belanjaItems = [];
    @if(isset($homepageCategories) && $homepageCategories && $homepageCategories->count() > 0)
      @foreach($homepageCategories as $cat)
        belanjaItems.push({
          title: '{{ addslashes($cat->name) }}',
          slug: '{{ addslashes($cat->slug) }}'
        });
      @endforeach
    @else
      // Fallback jika belum ada kategori
      belanjaItems.push(
        { title: 'Daging Ayam Segar', slug: 'daging-ayam-segar' },
        { title: 'Olahan', slug: 'olahan' },
        { title: 'Alat-alat', slug: 'alat-alat' },
        { title: 'Pakan Ayam', slug: 'pakan' },
        { title: 'Obat & Vitamin', slug: 'obat-vitamin' },
        { title: 'Peralatan Kandang', slug: 'peralatan-kandang' }
      );
    @endif

    function renderCategory(items) {
      const left = document.getElementById('catColLeft');
      const right = document.getElementById('catColRight');
      if (!left || !right) return;
      
      // Check if items have 'url' (for articles) or 'slug' (for belanja/filter)
      if (items.length > 0 && items[0].url) {
        // Article items - use URL
        left.innerHTML = items.map(item => 
          `<a href="${item.url}" class="cat-link">${item.title}</a>`
        ).join('');
      } else {
        // Belanja items - use filter
        left.innerHTML = items.map(item => 
          `<a href="#" class="cat-link" onclick="event.preventDefault(); filterByHomepageCategory('${item.slug}'); document.getElementById('categoryDropdown').classList.remove('show');">${item.title}</a>`
        ).join('');
      }
      right.innerHTML = '';
    }

    function setActiveTab(tab) {
      const a = document.getElementById('catTabArticle');
      const b = document.getElementById('catTabBelanja');
      if (!a || !b) return;
      if (tab === 'article') { a.classList.add('active'); b.classList.remove('active'); renderCategory(articleItems); }
      else { b.classList.add('active'); a.classList.remove('active'); renderCategory(belanjaItems); }
    }

    document.addEventListener('DOMContentLoaded', () => {
      // initial render
      renderCategory(articleItems);
      const a = document.getElementById('catTabArticle');
      const b = document.getElementById('catTabBelanja');
      if (a) a.addEventListener('click', () => setActiveTab('article'));
      if (b) b.addEventListener('click', () => setActiveTab('belanja'));
      // close dropdown when selecting a link
      document.getElementById('categoryDropdown')?.addEventListener('click', (e) => {
        const link = e.target.closest('.cat-link');
        if (link) {
          document.getElementById('categoryDropdown').classList.remove('show');
        }
      });
    });

    // Product display is now fully handled by server-side rendering
    
    // Search filter - real-time filtering
    function filterProducts() {
      const search = document.getElementById('searchInput').value.toLowerCase();
      const cards = document.querySelectorAll('.product-card');
      let visibleCount = 0;
      cards.forEach(card => {
        const name = card.getAttribute('data-name') || '';
        const slug = card.getAttribute('data-slug') || '';
        const matches = name.includes(search) || slug.includes(search);
        card.style.display = matches ? '' : 'none';
        if (matches) visibleCount++;
      });
      
      // Show message if no results
      let noResultsMsg = document.getElementById('noResultsMessage');
      if (search && visibleCount === 0) {
        if (!noResultsMsg) {
          noResultsMsg = document.createElement('div');
          noResultsMsg.id = 'noResultsMessage';
          noResultsMsg.className = 'text-center py-8 text-gray-500';
          noResultsMsg.innerHTML = '<i class="fa-solid fa-search mb-2" style="font-size: 2rem;"></i><p>Tidak ada produk yang ditemukan untuk "' + search + '"</p>';
          document.getElementById('productGrid').appendChild(noResultsMsg);
        }
      } else if (noResultsMsg) {
        noResultsMsg.remove();
      }
    }
    
    // Handle search form submission
    function handleSearch(event) {
      const search = document.getElementById('searchInput').value.trim();
      if (search) {
        // Let form submit naturally with query string
        return true;
      }
      event.preventDefault();
      return false;
    }
    
    // Filter by homepage category (redirect to filtered page)
    function filterByHomepageCategory(categorySlug) {
      // Redirect to homepage with category filter
      const url = new URL(window.location.href);
      url.searchParams.set('category', categorySlug);
      url.searchParams.delete('page'); // Reset pagination
      window.location.href = url.toString();
    }
    
    // Category filter (for existing chip buttons if any)
    let currentCategory = '';
    function filterByCategory(category) {
      currentCategory = category;
      const cards = document.querySelectorAll('.product-card');
      const chips = document.querySelectorAll('.chip');
      
      // Update chip active state
      chips.forEach(c => {
        c.classList.remove('bg-emerald-100','text-emerald-700','font-semibold');
        const chipText = c.textContent.trim().toLowerCase();
        if (category === '' && chipText === 'semua') {
          c.classList.add('bg-emerald-100','text-emerald-700','font-semibold');
        } else if (category !== '' && chipText.includes(category)) {
          c.classList.add('bg-emerald-100','text-emerald-700','font-semibold');
        }
      });
      
      // Filter cards
      let visibleCount = 0;
      cards.forEach(card => {
        const slug = card.getAttribute('data-slug') || '';
        const name = card.getAttribute('data-name') || '';
        let matches = false;
        
        if (category === '') {
          matches = true;
        } else {
          // Map category to search patterns
          const categoryPatterns = {
            'daging': ['daging', 'ayam-segar', 'ayam-utuh'],
            'telur': ['telur'],
            'ayam': ['ayam-utuh', 'ayam-segar'],
            'jeroan': ['jeroan', 'hati', 'ampela']
          };
          
          if (categoryPatterns[category]) {
            matches = categoryPatterns[category].some(pattern => 
              slug.includes(pattern) || name.includes(pattern)
            );
          } else {
            matches = slug.includes(category) || name.includes(category);
          }
        }
        
        card.style.display = matches ? '' : 'none';
        if (matches) visibleCount++;
      });
      
      // Show message if no results
      let noResultsMsg = document.getElementById('noResultsMessage');
      if (category && visibleCount === 0) {
        if (!noResultsMsg) {
          noResultsMsg = document.createElement('div');
          noResultsMsg.id = 'noResultsMessage';
          noResultsMsg.className = 'col-span-full text-center py-8 text-gray-500';
          noResultsMsg.innerHTML = '<i class="fa-solid fa-box-open mb-2" style="font-size: 2rem;"></i><p>Tidak ada produk dalam kategori ini</p>';
          document.getElementById('productGrid').appendChild(noResultsMsg);
    }
      } else if (noResultsMsg) {
        noResultsMsg.remove();
      }
    }
  </script>
  
  @if(Auth::check())
  <style>
    /* WhatsApp-style chat messages - Buyer Chat */
    #buyerChatMessages {
      display: flex !important;
      flex-direction: column !important;
      gap: 0.5rem !important;
      width: 100% !important;
    }
    
    #buyerChatMessages .chat-message {
      display: flex !important;
      flex-direction: column !important;
      margin-bottom: 0.5rem !important;
      width: 100% !important;
      box-sizing: border-box !important;
    }
    
    /* Message Left (Received from Admin) - White background, LEFT aligned */
    #buyerChatMessages .chat-message.message-left {
      align-self: flex-start !important;
      max-width: 70% !important;
      align-items: flex-start !important;
      margin-right: auto !important;
      margin-left: 0 !important;
    }
    
    /* Message Right (Sent by Buyer) - Green background, RIGHT aligned */
    #buyerChatMessages .chat-message.message-right {
      align-self: flex-end !important;
      max-width: 70% !important;
      align-items: flex-end !important;
      margin-left: auto !important;
      margin-right: 0 !important;
    }
    
    .message-sender-name {
      font-size: 0.75rem;
      color: #6c757d;
      margin-bottom: 0.25rem;
      font-weight: 500;
      padding: 0 0.5rem;
    }
    
    .message-bubble {
      padding: 0.625rem 0.875rem;
      border-radius: 12px;
      font-size: 0.875rem;
      line-height: 1.4;
      word-wrap: break-word;
      max-width: 100%;
      box-shadow: 0 1px 2px rgba(0,0,0,0.1);
      display: inline-block;
    }
    
    /* Left message (received from admin) - white background */
    #buyerChatMessages .message-left .message-bubble {
      background: #ffffff !important;
      color: #2F2F2F !important;
      border: 1px solid #e5e7eb !important;
    }
    
    /* Right message (sent by buyer) - green background like WhatsApp */
    #buyerChatMessages .message-right .message-bubble {
      background: #dcf8c6 !important;
      color: #2F2F2F !important;
      border: none !important;
    }
    
    .message-time {
      font-size: 0.6875rem;
      color: #6c757d;
      margin-top: 0.25rem;
      padding: 0 0.5rem;
    }
    
    .message-left .message-time {
      text-align: left;
    }
    
    .message-right .message-time {
      text-align: right;
    }
  </style>
  <script>
    // Set current user for chat
    window.currentUser = @json(Auth::user());
    // Set currentUserId for WhatsApp-style positioning
    window.currentUserId = @json(Auth::user()?->user_id);
  </script>
  <script src="{{ asset('js/chat-buyer.js') }}"></script>
  @endif
</body>
</html>
