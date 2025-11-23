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
        <form id="loginForm" action="{{ route('login.post') }}" method="POST">
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
                <input type="password" class="form-control" id="password" name="password" 
                       placeholder="Password" required>
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
            </button>
            
            <button type="button" class="btn-oauth" onclick="loginWithFacebook()">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 10C20 4.47715 15.5228 0 10 0C4.47715 0 0 4.47715 0 10C0 14.9912 3.65684 19.1283 8.4375 19.8785V12.8906H5.89844V10H8.4375V7.79688C8.4375 5.29063 9.93047 3.90625 12.2146 3.90625C13.3084 3.90625 14.4531 4.10156 14.4531 4.10156V6.5625H13.1922C11.95 6.5625 11.5625 7.3334 11.5625 8.125V10H14.3359L13.8926 12.8906H11.5625V19.8785C16.3432 19.1283 20 14.9912 20 10Z" fill="#1877F2"/>
                </svg>
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
            return;
        }
    });
    
    // OAuth functions
    function loginWithGoogle() {
        showWarning('Fitur login dengan Google akan segera tersedia!');
    }
    
    function loginWithFacebook() {
        showWarning('Fitur login dengan Facebook akan segera tersedia!');
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
