<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $article->title }} - ChickPatrol</title>
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
    }
    body { background:#FAFAF8; font-family: 'Inter', -apple-system, sans-serif; }
    
    .article-container {
      max-width: 800px;
      margin: 0 auto;
      padding: 2rem 1.5rem;
    }
    
    .article-header {
      margin-bottom: 2rem;
    }
    
    .article-title {
      font-size: 2rem;
      font-weight: 700;
      color: #2F2F2F;
      margin-bottom: 1rem;
      line-height: 1.3;
    }
    
    .article-meta {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      font-size: 0.9rem;
      color: #6c757d;
    }
    
    .article-content {
      background: white;
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      line-height: 1.8;
      color: #2F2F2F;
    }
    
    .article-content h1,
    .article-content h2,
    .article-content h3 {
      margin-top: 2rem;
      margin-bottom: 1rem;
      color: #2F2F2F;
    }
    
    .article-content p {
      margin-bottom: 1.5rem;
    }
    
    .article-content ul,
    .article-content ol {
      margin-bottom: 1.5rem;
      padding-left: 2rem;
    }
    
    .article-content img {
      max-width: 100%;
      height: auto;
      border-radius: 8px;
      margin: 1.5rem 0;
    }
    
    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      color: var(--primary-green);
      text-decoration: none;
      margin-bottom: 1.5rem;
      font-weight: 500;
    }
    
    .back-link:hover {
      color: var(--dark-green);
    }
  </style>
</head>
<body class="min-h-screen">
  @include('partials.navbar')

  <main class="article-container">
    <a href="/articles" class="back-link">
      <i class="fa-solid fa-arrow-left"></i>
      Kembali ke Daftar Artikel
    </a>
    
    <div class="article-header">
      <h1 class="article-title">{{ $article->title }}</h1>
      <div class="article-meta">
        @if($article->category)
          <div>
            <i class="fa-solid fa-tag me-1"></i>
            {{ $article->category->name }}
          </div>
        @endif
        <div>
          <i class="fa-solid fa-user me-1"></i>
          {{ $article->user->name ?? 'Admin' }}
        </div>
        <div>
          <i class="fa-solid fa-calendar me-1"></i>
          {{ $article->created_at->format('d M Y') }}
        </div>
      </div>
    </div>
    
    <div class="article-content">
      {!! $article->content !!}
    </div>
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

