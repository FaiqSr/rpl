<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Dashboard - ChickPatrol Seller</title>
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Tailwind CSS via Vite -->
  @vite(['resources/css/app.css'])
  
  <!-- Google Fonts - Inter (Premium Typography) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
  
  <style>
    * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    body { background: #F8F9FB; margin: 0; }
    
    
    .main-content {
      margin-left: 220px;
      padding: 1.5rem;
    }
    
    .page-header {
      margin-bottom: 1.5rem;
    }
    
    .page-header h1 {
      font-size: 1.5rem;
      font-weight: 600;
      color: #2F2F2F;
      margin: 0;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 1rem;
      margin-bottom: 2rem;
    }
    
    .stat-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 10px;
      padding: 1.25rem;
    }
    
    .stat-card-label {
      font-size: 0.75rem;
      color: #6c757d;
      margin-bottom: 0.5rem;
      display: block;
    }
    
    .stat-card-value {
      font-size: 1.75rem;
      font-weight: 700;
      color: #2F2F2F;
      margin: 0;
    }
    
    .content-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 10px;
      padding: 1.5rem;
    }
    
    .content-card-title {
      font-size: 1rem;
      font-weight: 600;
      color: #2F2F2F;
      margin-bottom: 1rem;
    }
    
    .performa-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.25rem 0.75rem;
      background: #f8f9fa;
      border-radius: 6px;
      font-size: 0.75rem;
      color: #6c757d;
    }
    
    .performa-value {
      font-weight: 700;
      color: #2F2F2F;
    }
  </style>
</head>
<body>
  @include('layouts.sidebar')
  
  <!-- Main Content -->
  <main class="main-content">
    <div class="page-header">
      <h1>Home</h1>
    </div>
    
    <!-- Stats Grid -->
    <div class="stats-grid">
      <div class="stat-card">
        <span class="stat-card-label">Penjualan Hari Ini</span>
        <h2 class="stat-card-value">3</h2>
      </div>
      
      <div class="stat-card">
        <span class="stat-card-label">Penjualan Perbulan</span>
        <h2 class="stat-card-value">30</h2>
      </div>
      
      <div class="stat-card">
        <span class="stat-card-label">Produk Aktif</span>
        <h2 class="stat-card-value">2</h2>
      </div>
      
      <div class="stat-card">
        <span class="stat-card-label">Alat Aktif</span>
        <h2 class="stat-card-value">1</h2>
      </div>
      
      <div class="stat-card">
        <span class="stat-card-label">Ulasan Baru</span>
        <h2 class="stat-card-value">12</h2>
      </div>
      
      <div class="stat-card">
        <span class="stat-card-label">Chat Baru</span>
        <h2 class="stat-card-value">12</h2>
      </div>
      
      <div class="stat-card">
        <span class="stat-card-label">Total Pendapatan Perbulan</span>
        <h2 class="stat-card-value">12.000.00</h2>
      </div>
    </div>
    
    <!-- Produk Terpopuler -->
    <div class="content-card">
      <h3 class="content-card-title">Produk Terpopuler</h3>
      <div class="text-muted" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
        <p class="mb-0">Data produk terpopuler akan ditampilkan di sini</p>
      </div>
    </div>
  </main>
  
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  <script src="{{ asset('js/dashboard-alerts.js') }}"></script>
  
  <script>
    // SweetAlert Helper Functions
    window.showSuccess = function(message) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: message,
            confirmButtonColor: '#22C55E',
            confirmButtonText: 'OK'
        });
    };
    
    window.showError = function(message) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: message,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'OK'
        });
    };
    
    // Toggle Submenu
    
    // Show success message if redirected with success
    @if(session('success'))
        showSuccess('{{ session('success') }}');
    @endif
    
    // Show error message if redirected with error
    @if(session('error'))
        showError('{{ session('error') }}');
    @endif
  </script>
</body>
</html>
