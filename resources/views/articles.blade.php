<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Artikel - ChickPatrol</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Tailwind CSS via Vite -->
  @vite(['resources/css/app.css'])
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
  <style>
    :root {
      --primary-green: #69B578;
      --dark-green: #5a8c64;
      --cream: #F5E6D3;
      --yellow: #F4C430;
    }
    body { background:#FAFAF8; font-family: 'Inter', -apple-system, sans-serif; }
    
    .article-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      transition: all 0.3s;
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    
    .article-card:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      transform: translateY(-2px);
    }
    
    .article-title {
      font-size: 1.5rem;
      font-weight: 700;
      color: #2F2F2F;
      margin-bottom: 1rem;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .article-meta {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.75rem;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-top: auto;
      padding-top: 1rem;
      border-top: 1px solid #f3f4f6;
    }
    
    .read-more {
      color: var(--primary-green);
      text-decoration: none;
      font-weight: 500;
      font-size: 0.9rem;
      margin-top: 0.5rem;
      display: inline-block;
    }
    
    .read-more:hover {
      color: var(--dark-green);
    }
    
    .category-filter {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
      margin-bottom: 2rem;
    }
    
    .category-chip {
      padding: 0.5rem 1rem;
      background: white;
      border: 1px solid #e5e7eb;
      border-radius: 20px;
      color: #6c757d;
      text-decoration: none;
      font-size: 0.875rem;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      display: inline-block;
      transform: translateZ(0);
      will-change: background-color, color, border-color;
    }
    
    .category-chip:hover {
      background: #f0f9f2;
      color: var(--primary-green);
      border-color: var(--primary-green);
      transform: translateY(-1px);
    }
    
    .category-chip.active {
      background: var(--primary-green);
      color: white;
      border-color: var(--primary-green);
      transform: translateY(0);
      box-shadow: 0 2px 8px rgba(105, 181, 120, 0.3);
    }
    
    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      color: #9ca3af;
    }
    
    .empty-state i {
      font-size: 3rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }
    @media (max-width: 768px) {
      main {
        padding: 1rem !important;
      }
      .article-card {
        padding: 1rem !important;
      }
      .article-title {
        font-size: 1.25rem !important;
      }
      .article-image {
        height: 200px !important;
      }
      .grid {
        grid-template-columns: 1fr !important;
        gap: 1rem !important;
      }
    }
  </style>
</head>
<body class="min-h-screen">
  @include('partials.navbar')

  <main class="container py-5" style="max-width: 1200px;">
    <div class="mb-4">
      <h1 class="h2 mb-2">Artikel & Panduan</h1>
      <p class="text-muted">Tips, panduan, dan informasi seputar beternak ayam</p>
    </div>

    <!-- Category Filter -->
    <div class="category-filter">
      <a href="/articles" class="category-chip {{ !request('category') ? 'active' : '' }}">Semua</a>
      @foreach($categories as $category)
        <a href="/articles?category={{ $category->slug }}" class="category-chip {{ request('category') == $category->slug ? 'active' : '' }}">{{ $category->name }}</a>
      @endforeach
    </div>

    <!-- Articles Grid -->
    @if($articles->count() > 0)
      <div class="row g-4">
        @foreach($articles as $article)
          <div class="col-md-6 col-lg-4">
            <div class="article-card">
              @php
                $imageUrl = null;
                if ($article->featured_image) {
                  if (strpos($article->featured_image, 'http') === 0 || strpos($article->featured_image, 'https') === 0) {
                    $imageUrl = $article->featured_image;
                  } else {
                    // Handle both /storage/ and storage/ paths
                    $path = ltrim($article->featured_image, '/');
                    $imageUrl = asset($path);
                  }
                }
              @endphp
              @if($imageUrl)
                <a href="/articles/{{ $article->article_id }}">
                  <img src="{{ $imageUrl }}" alt="{{ $article->title }}" class="w-100 mb-2" style="height: 250px; object-fit: cover; border-radius: 8px;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                  <div style="display: none; height: 250px; background: #f3f4f6; border-radius: 8px; align-items: center; justify-content: center; color: #9ca3af;">
                    <i class="fa-solid fa-image fa-3x"></i>
                  </div>
                </a>
              @endif
              
              @if($article->categories && $article->categories->count() > 0)
                <div class="mb-3" style="margin-top: 0.5rem;">
                  @foreach($article->categories as $category)
                    <span class="badge bg-primary me-2" style="text-transform: uppercase; font-size: 0.7rem; padding: 0.4rem 0.8rem; font-weight: 600; letter-spacing: 0.5px; background-color: #3b82f6 !important; border: none;">{{ strtoupper($category->name) }}</span>
                  @endforeach
                </div>
              @endif
              
              <h3 class="article-title">
                <a href="/articles/{{ $article->article_id }}" class="text-decoration-none text-dark">
                  {{ $article->title }}
                </a>
              </h3>
              <div class="article-meta" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; margin-top: auto; padding-top: 1rem; border-top: 1px solid #f3f4f6;">
                <span style="font-weight: 600;">BY {{ strtoupper($article->user->name ?? 'ADMIN') }}</span>
                <span>•</span>
                <span>{{ strtoupper($article->created_at->format('d M Y')) }}</span>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <!-- Pagination -->
      <div class="mt-5">
        {{ $articles->withQueryString()->links() }}
      </div>
    @else
      <div class="empty-state">
        <i class="fa-solid fa-newspaper"></i>
        <h3>Tidak ada artikel</h3>
        <p>Belum ada artikel yang tersedia saat ini.</p>
      </div>
    @endif
  </main>

  <!-- Footer -->
  <footer class="bg-white mt-12 border-t border-gray-200">
    <div class="container py-4">
      <p class="text-center text-sm text-gray-600 mb-0">©2025, ChickPatrol. All Rights Reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/navbar.js') }}"></script>
</body>
</html>

