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
            background: linear-gradient(to bottom, #FFFFFF 0%, #FAFAFA 100%);
            border-radius: 20px;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(105, 181, 120, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            padding: 3.5rem 3.5rem;
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        
        .brand-logo {
            background: linear-gradient(135deg, #69B578 0%, #5a9a68 50%, #4d8a5a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
            font-size: 2rem;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-bottom: 2.5rem;
            transition: color 0.2s ease;
            letter-spacing: -0.03em;
            line-height: 1.2;
        }
        
        .brand-logo:hover {
            opacity: 0.9;
        }
        
        .auth-title {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1F2937 0%, #374151 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-align: center;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
            line-height: 1.3;
        }
        
        .auth-subtitle {
            color: #6B7280;
            font-size: 0.95rem;
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 400;
        }
        
        .auth-subtitle a {
            background: linear-gradient(135deg, #69B578 0%, #5a9a68 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .auth-subtitle a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #69B578, #5a9a68);
            transition: width 0.3s ease;
            border-radius: 1px;
        }
        
        .auth-subtitle a:hover::after {
            width: 100%;
        }
        
        .auth-subtitle a:hover {
            filter: drop-shadow(0 2px 4px rgba(105, 181, 120, 0.2));
        }
        
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            letter-spacing: -0.01em;
        }
        
        .form-control {
            border: 1.5px solid #E5E7EB;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: #FFFFFF;
            color: #2F2F2F;
        }
        
        .form-control:hover {
            border-color: #D1D5DB;
        }
        
        .form-control:focus {
            border-color: #69B578;
            box-shadow: 0 0 0 3px rgba(105, 181, 120, 0.1);
            outline: none;
            background-color: #FFFFFF;
        }
        
        .form-control.is-invalid {
            border-color: #EF4444;
            background-color: #FEF2F2;
        }
        
        .form-control.is-invalid:focus {
            border-color: #EF4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }
        
        .form-control::placeholder {
            color: #9CA3AF;
            font-size: 0.9rem;
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
            color: #9CA3AF;
            font-size: 1rem;
            transition: all 0.2s ease;
            z-index: 10;
            padding: 0.25rem;
        }
        
        .password-toggle-icon:hover {
            color: #69B578;
            transform: translateY(-50%) scale(1.1);
        }
        
        .password-toggle-icon:active {
            transform: translateY(-50%) scale(0.95);
        }
        
        .password-input-wrapper .form-control {
            padding-right: 3rem;
        }
        
        .btn-primary {
            background-color: #69B578;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            color: #FFFFFF;
            transition: background-color 0.2s ease;
            width: 100%;
            margin-top: 0.5rem;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background-color: #5a9a68;
        }
        
        .btn-primary:active {
            background-color: #4d8a5a;
        }
        
        .btn-primary:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(105, 181, 120, 0.2);
        }
        
        .btn-primary:disabled {
            background-color: #D1D5DB;
            color: #9CA3AF;
            cursor: not-allowed;
        }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1.5px solid #E5E7EB;
        }
        
        .divider span {
            padding: 0 1rem;
            color: #6B7280;
            font-size: 0.85rem;
            background: white;
            font-weight: 500;
        }
        
        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: #EF4444;
            font-weight: 500;
        }
        
        .form-text {
            font-size: 0.8rem;
            color: #6B7280;
            margin-top: 0.5rem;
            line-height: 1.4;
        }
        
        .btn-oauth {
            border: 1.5px solid #E5E7EB;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            background: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            font-weight: 500;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            width: 100%;
            font-size: 0.9rem;
            color: #2F2F2F;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        
        .btn-oauth:hover {
            background-color: #F9FAFB;
            border-color: #69B578;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(105, 181, 120, 0.15);
            color: #69B578;
        }
        
        .btn-oauth:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        
        .btn-oauth:focus {
            outline: none;
            border-color: #69B578;
            box-shadow: 0 0 0 3px rgba(105, 181, 120, 0.1);
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
            font-size: 0.8rem;
            color: #6B7280;
            text-align: center;
            line-height: 1.5;
        }
        
        .text-small a {
            color: #69B578;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        
        .text-small a:hover {
            color: #5a9a68;
            text-decoration: underline;
        }
        
        /* Background Effects - Subtle & Professional */
        .auth-background {
            position: relative;
            min-height: 100vh;
            background: linear-gradient(135deg, #69B578 0%, #5a9a68 50%, #f8faf9 50%, #ffffff 100%);
            overflow: hidden;
        }
        
        /* SVG Background Container - Consistent styling */
        .svg-background-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        
        .svg-background {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0.95;
        }
        
        .auth-background::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 20s ease-in-out infinite;
            z-index: 0;
        }
        
        .auth-background::after {
            content: '';
            position: absolute;
            bottom: -15%;
            right: -5%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 25s ease-in-out infinite reverse;
            z-index: 0;
        }
        
        /* Subtle pattern overlay */
        .auth-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 2px 2px, rgba(255, 255, 255, 0.03) 1px, transparent 0);
            background-size: 40px 40px;
            z-index: 0;
            pointer-events: none;
        }
        
        /* Decorative circles - subtle */
        .auth-decoration {
            position: absolute;
            z-index: 0;
            pointer-events: none;
        }
        
        .decoration-circle-1 {
            top: 10%;
            left: 5%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 15s ease-in-out infinite;
        }
        
        .decoration-circle-2 {
            bottom: 20%;
            right: 8%;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.06) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 18s ease-in-out infinite reverse;
        }
        
        .decoration-circle-3 {
            top: 50%;
            right: 15%;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 12s ease-in-out infinite;
        }
        
        /* Smooth floating animation */
        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            33% {
                transform: translate(30px, -30px) scale(1.05);
            }
            66% {
                transform: translate(-20px, 20px) scale(0.95);
            }
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
    
    <!-- Vite - includes Firebase -->
    @vite(['resources/js/app.js'])
    
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
