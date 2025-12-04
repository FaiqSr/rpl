@extends('layouts.auth')

@section('title', 'Reset Password - ChickPatrol')

@section('content')
<div class="position-relative" style="min-height: 100vh; background: linear-gradient(to bottom right, #69B578 0%, #69B578 60%, #f0f0f0 60%, #ffffff 100%);">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; overflow: hidden; z-index: 0;">
        <svg viewBox="0 0 1440 800" style="position: absolute; width: 100%; height: 100%;" preserveAspectRatio="none">
            <path d="M 0 0 L 0 800 L 600 800 Q 700 400 600 0 Z" fill="#69B578" opacity="1"/>
        </svg>
    </div>
    
    <div class="container-fluid p-0" style="position: relative; z-index: 1;">
        <div class="row g-0" style="min-height: 100vh;">
            <div class="col-12 d-flex align-items-center justify-content-center py-5">
                <div class="w-100 px-4" style="max-width: 500px;">
                    <div class="auth-card" style="box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);">
                        <a href="{{ route('home') }}" class="brand-logo">ChickPatrol</a>
                        
                        <h2 class="auth-title">Reset Password</h2>
                        <p class="auth-subtitle">
                            Masukkan password baru Anda
                        </p>

                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form id="resetPasswordForm" action="{{ route('password.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user_id }}">
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" placeholder="Minimal 8 karakter: huruf kapital, kecil, angka, simbol" required minlength="8">
                                <small class="form-text text-muted">Password harus minimal 8 karakter dengan kombinasi huruf kapital, huruf kecil, angka, dan simbol</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" 
                                       placeholder="Ulangi password baru" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                Reset Password
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="{{ route('login') }}" class="text-decoration-none" style="color: var(--primary-green);">
                                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password_confirmation').value;
        
        // Validate password match
        if (password !== confirmPassword) {
            showError('Password dan konfirmasi password tidak cocok!');
            return;
        }
        
        // Validate password length
        if (password.length < 8) {
            showError('Password minimal 8 karakter!');
            return;
        }
        
        // Validate password strength: must contain uppercase, lowercase, number, and symbol
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumber = /\d/.test(password);
        const hasSymbol = /[@$!%*?&]/.test(password);
        
        if (!hasUpperCase || !hasLowerCase || !hasNumber || !hasSymbol) {
            showError('Password harus mengandung huruf kapital, huruf kecil, angka, dan simbol!');
            return;
        }
        
        // Submit form
        this.submit();
    });
</script>
@endpush
@endsection

