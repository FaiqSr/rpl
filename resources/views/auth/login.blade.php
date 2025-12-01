@extends('layouts.auth')

@section('title', 'Masuk - ChickPatrol')

@section('content')
<div class="position-relative" style="min-height: 100vh; background: linear-gradient(to bottom right, #69B578 0%, #69B578 60%, #f0f0f0 60%, #ffffff 100%);">
    <!-- Curved green background element -->
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; overflow: hidden; z-index: 0;">
        <svg viewBox="0 0 1440 800" style="position: absolute; width: 100%; height: 100%;" preserveAspectRatio="none">
            <path d="M 0 0 L 0 800 L 600 800 Q 700 400 600 0 Z" fill="#69B578" opacity="1"/>
        </svg>
    </div>
    
    <div class="container-fluid p-0" style="position: relative; z-index: 1;">
        <div class="row g-0" style="min-height: 100vh;">
            <!-- Form Card -->
            <div class="col-12 d-flex align-items-center justify-content-center py-5">
                <div class="w-100 px-4" style="max-width: 500px;">
                    <div class="auth-card" style="box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);">
                        <a href="{{ route('home') }}" class="brand-logo">ChickPatrol</a>
                        
                        <h2 class="auth-title">Masuk</h2>
                        <p class="auth-subtitle">
                            belum punya akun? <a href="{{ route('register') }}">Daftar</a>
                        </p>
        
        <!-- Login Form -->
        <form id="loginForm" action="{{ route('login.post') }}" method="POST" autocomplete="off">
            @csrf
            
            <div class="mb-3">
                <label for="email" class="form-label">Alamat Email atau No. Telepon</label>
                <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" 
                       value="{{ old('email') }}" placeholder="No Telp atau E-mail" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="password-input-wrapper">
                <input type="password" class="form-control" id="password" name="password" 
                       placeholder="Password" required>
                    <i class="fas fa-eye password-toggle-icon" id="togglePassword" onclick="togglePasswordVisibility('password', 'togglePassword')"></i>
                </div>
            </div>
            
            <div class="mb-3 text-end">
                <a href="{{ route('password.request') }}" class="text-decoration-none" style="color: var(--primary-green); font-size: 0.875rem;">
                    Lupa Password?
                </a>
            </div>
            
            <button type="submit" class="btn btn-primary">
                Masuk
            </button>
        </form>
        
        <div class="text-center my-3">
            <small class="text-muted">
                belum punya akun? <a href="{{ route('register') }}">Daftar</a>
            </small>
        </div>
        
        <div class="divider">
            <span>atau</span>
        </div>
        
        <!-- OAuth Buttons -->
        <div class="oauth-buttons">
            <button type="button" class="btn-oauth" onclick="loginWithGoogle()">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19.8055 10.2292C19.8055 9.55219 19.7499 8.86694 19.6305 8.19824H10.2V12.0491H15.6014C15.3773 13.2909 14.6571 14.3897 13.6025 15.088V17.5863H16.825C18.7173 15.8438 19.8055 13.2728 19.8055 10.2292Z" fill="#4285F4"/>
                    <path d="M10.2 20C12.9491 20 15.2709 19.1045 16.8286 17.5863L13.6061 15.088C12.7091 15.698 11.5573 16.0418 10.2036 16.0418C7.54409 16.0418 5.28864 14.2837 4.50182 11.9163H1.17273V14.4927C2.77818 17.6855 6.33818 20 10.2 20Z" fill="#34A853"/>
                    <path d="M4.49818 11.9163C4.07 10.6745 4.07 9.33001 4.49818 8.08819V5.51182H1.17273C-0.390909 8.62728 -0.390909 12.3773 1.17273 15.4927L4.49818 11.9163Z" fill="#FBBC04"/>
                    <path d="M10.2 3.95818C11.6218 3.93637 13.0036 4.47092 14.0364 5.45637L16.8945 2.60182C15.1873 0.990002 12.9345 0.0981838 10.2 0.120002C6.33818 0.120002 2.77818 2.43455 1.17273 5.51183L4.49818 8.08819C5.28136 5.71637 7.54045 3.95818 10.2 3.95818Z" fill="#EA4335"/>
                </svg>
                <span>Masuk dengan Google</span>
            </button>
        </div>
        
        <div class="text-small mt-3">
            Dengan mendaftar, saya menyetujui
            <a href="#">Syarat & Ketentuan</a> serta <a href="#">Kebijakan Privasi</a>
        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Form validation and submission
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        // Basic validation
        if (!email || !password) {
            e.preventDefault();
            showError('Email/No. Telepon dan password harus diisi!');
            return false;
        }
        
        // Let form submit normally with CSRF token from @csrf directive
        return true;
    });
    
    // OAuth functions
    function loginWithGoogle() {
        showWarning('Fitur login dengan Google akan segera tersedia!');
    }
    
    // Toggle password visibility
    function togglePasswordVisibility(inputId, iconId) {
        const passwordInput = document.getElementById(inputId);
        const toggleIcon = document.getElementById(iconId);
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
    
    // Show success message if redirected with success
    @if(session('success'))
        showSuccess('{{ session('success') }}');
    @endif
    
    // Show error message if redirected with error
    @if(session('error'))
        showError('{{ session('error') }}');
    @endif
    
    // Show validation errors
    @if($errors->any())
        showError('{{ $errors->first() }}');
    @endif
</script>
@endpush
@endsection
