<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ChickPatrol - Authentication')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css'])
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background-color: #ffffff;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
        }
        
        .green-bg-left {
            position: absolute;
            top: 0;
            left: 0;
            width: 40%;
            height: 100%;
            background: #ffffff;
            clip-path: polygon(0 0, 100% 0, 70% 100%, 0 100%);
            z-index: 0;
        }
        
        .green-bg-right {
            position: absolute;
            top: 0;
            right: 0;
            width: 40%;
            height: 100%;
            background: #ffffff;
            clip-path: polygon(30% 0, 100% 0, 100% 100%, 0 100%);
            z-index: 0;
        }
        
        .auth-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 3rem 3.5rem;
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 1;
        }
        
        .brand-logo {
            color: #69B578;
            font-weight: 700;
            font-size: 1.5rem;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .auth-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2F2F2F;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        
        .auth-subtitle {
            color: #6c757d;
            font-size: 0.875rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .auth-subtitle a {
            color: #69B578;
            text-decoration: none;
            font-weight: 500;
        }
        
        .auth-subtitle a:hover {
            text-decoration: underline;
        }
        
        .form-label {
            font-weight: 500;
            color: #2F2F2F;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        
        .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 0.65rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            border-color: #69B578;
            box-shadow: 0 0 0 0.2rem rgba(105, 181, 120, 0.15);
        }
        
        .form-control::placeholder {
            color: #adb5bd;
            font-size: 0.875rem;
        }
        
        .password-input-wrapper {
            position: relative;
        }
        
        .password-toggle-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            font-size: 1rem;
            transition: color 0.2s ease;
            z-index: 10;
        }
        
        .password-toggle-icon:hover {
            color: #69B578;
        }
        
        .password-input-wrapper .form-control {
            padding-right: 3rem;
        }
        
        .btn-primary {
            background-color: #2F2F2F;
            border: none;
            border-radius: 6px;
            padding: 0.65rem 1.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            width: 100%;
            margin-top: 0.5rem;
        }
        
        .btn-primary:hover {
            background-color: #1a1a1a;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.25rem 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .divider span {
            padding: 0 1rem;
            color: #6c757d;
            font-size: 0.8rem;
        }
        
        .btn-oauth {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 0.65rem 1rem;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
            width: 100%;
            font-size: 0.875rem;
            color: #2F2F2F;
        }
        
        .btn-oauth:hover {
            background-color: #f8f9fa;
            border-color: #adb5bd;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .btn-oauth img,
        .btn-oauth svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        
        .oauth-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .text-small {
            font-size: 0.75rem;
            color: #6c757d;
            text-align: center;
        }
        
        .text-small a {
            color: #69B578;
            text-decoration: none;
        }
        
        .text-small a:hover {
            text-decoration: underline;
        }
        
        /* Curved Green Background for Register Page */
        .green-section {
            position: relative;
            background: linear-gradient(135deg, #69B578 0%, #5a9a68 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .green-section::before {
            content: '';
            position: absolute;
            top: -10%;
            left: -10%;
            width: 60%;
            height: 60%;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            transform: translate(-20%, -20%);
        }
        
        .green-section::after {
            content: '';
            position: absolute;
            bottom: -15%;
            right: -15%;
            width: 70%;
            height: 70%;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(15%, 15%);
        }
        
        /* Curved white overlay on left side */
        .register-curve-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 50%;
            height: 100%;
            background: #ffffff;
            clip-path: ellipse(100% 100% at 0% 50%);
            z-index: 0;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    @yield('content')
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios@1.6.0/dist/axios.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // CSRF Token setup for AJAX
        document.addEventListener('DOMContentLoaded', function() {
            const token = document.querySelector('meta[name="csrf-token"]');
            if (token && typeof axios !== 'undefined') {
                window.axios = axios;
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
            }
        });
        
        // SweetAlert Helper Functions
        window.showSuccess = function(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                confirmButtonColor: '#69B578',
                confirmButtonText: 'OK'
            });
        };
        
        window.showError = function(message) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: message,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'OK'
            });
        };
        
        window.showWarning = function(message) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: message,
                confirmButtonColor: '#ffc107',
                confirmButtonText: 'OK'
            });
        };
        
        window.showConfirm = function(title, text, callback) {
            Swal.fire({
                icon: 'question',
                title: title,
                text: text,
                showCancelButton: true,
                confirmButtonColor: '#69B578',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed && callback) {
                    callback();
                }
            });
        };
    </script>
    
    @stack('scripts')
</body>
</html>
