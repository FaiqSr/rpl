@extends('layouts.auth')

@section('title', 'Lupa Password - ChickPatrol')

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
                        
                        <h2 class="auth-title">Lupa Password</h2>
                        <p class="auth-subtitle">
                            Verifikasi identitas Anda untuk reset password
                        </p>

                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('password.verify') }}" method="POST">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email atau No. Telepon</label>
                                <input type="text" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" 
                                       placeholder="Email atau No. Telepon" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="Nama lengkap sesuai registrasi" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">No. Telepon</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}" 
                                       placeholder="08xxxxxxxxxx" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                Verifikasi & Reset Password
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
@endsection

