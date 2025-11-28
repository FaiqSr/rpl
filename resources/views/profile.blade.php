<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Profil Saya - ChickPatrol</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Tailwind CSS via Vite -->
  @vite(['resources/css/app.css'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" />
  <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
  <style>
    body { background:#FAFAF8; font-family:'Inter',sans-serif; }
    .profile-card { background:white; border:1px solid #e9ecef; border-radius:12px; padding:2rem; max-width:720px; margin:2rem auto; }
    .profile-header { display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem; }
    .avatar { width:64px; height:64px; border-radius:50%; background:#e9ecef; display:flex; align-items:center; justify-content:center; font-size:28px; color:#6c757d; }
    .section-title { font-size:0.85rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:#6c757d; margin-top:1.5rem; }
    .btn-primary { background:#69B578; border:none; }
    .btn-primary:hover { background:#5aa267; }
    
    @media (max-width: 768px) {
      .profile-card {
        margin: 1rem !important;
        padding: 1.5rem !important;
      }
      .profile-header {
        flex-direction: column;
        text-align: center;
      }
      .row {
        margin: 0 !important;
      }
      .col-md-6 {
        margin-bottom: 1rem;
      }
    }
  </style>
</head>
<body>
  @include('partials.navbar')

  <div class="profile-card">
    <div class="profile-header">
      <div class="avatar"><i class="fa-regular fa-user"></i></div>
      <div>
        <h1 class="h5 mb-1">Profil Saya</h1>
        <p class="text-muted mb-0">Atur data untuk otomatis digunakan saat pemesanan</p>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success py-2 px-3">{{ session('success') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger py-2 px-3">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" class="row g-3">
      @csrf
      <div class="col-12">
        <label class="form-label">Nama Lengkap</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Nomor Telepon</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', auth()->user()->phone) }}" placeholder="08xxxxxxxxxx">
      </div>
      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" value="{{ auth()->user()->email }}" disabled>
      </div>
      <div class="col-12">
        <label class="form-label">Alamat</label>
        <textarea name="address" rows="3" class="form-control" placeholder="Nama Jalan, RT/RW, Kota">{{ old('address', auth()->user()->address) }}</textarea>
      </div>
      <div class="col-12 d-flex justify-content-end gap-2 mt-2">
        <a href="{{ route('home') }}" class="btn btn-outline-secondary">Batal</a>
        <button class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>

    <div class="section-title">Penggunaan Otomatis</div>
    <p class="text-muted small mb-0">Data nama, telepon, dan alamat akan otomatis mengisi form pesanan produk sehingga kamu tidak perlu mengetik ulang.</p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/navbar.js') }}"></script>
</body>
</html>
