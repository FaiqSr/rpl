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
      font-size: 1.25rem;
      font-weight: 600;
      color: #2F2F2F;
      margin-bottom: 0.75rem;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .article-excerpt {
      color: #6c757d;
      font-size: 0.9rem;
      line-height: 1.6;
      margin-bottom: 1rem;
      flex-grow: 1;
      display: -webkit-box;
      -webkit-line-clamp: 4;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .article-meta {
      display: flex;
      align-items: center;
      gap: 1rem;
      font-size: 0.85rem;
      color: #9ca3af;
      margin-top: auto;
      padding-top: 1rem;
      border-top: 1px solid #f3f4f6;
    }
    
    .article-author {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .article-date {
      display: flex;
      align-items: center;
      gap: 0.5rem;
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
              <h3 class="article-title">
                <a href="/articles/{{ $article->article_id }}" class="text-decoration-none text-dark">
                  {{ $article->title }}
                </a>
              </h3>
              <div class="article-excerpt">
                {{ Str::limit(strip_tags($article->content), 150) }}
              </div>
              <a href="/articles/{{ $article->article_id }}" class="read-more">
                Baca Selengkapnya <i class="fa-solid fa-arrow-right ms-1"></i>
              </a>
              <div class="article-meta">
                @if($article->category)
                  <div class="article-category" style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-tag"></i>
                    <span>{{ $article->category->name }}</span>
                  </div>
                @endif
                <div class="article-author">
                  <i class="fa-solid fa-user"></i>
                  <span>{{ $article->user->name ?? 'Admin' }}</span>
                </div>
                <div class="article-date">
                  <i class="fa-solid fa-calendar"></i>
                  <span>{{ $article->created_at->format('d M Y') }}</span>
                </div>
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

