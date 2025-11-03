<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chick Patrol - Smart Poultry Monitoring & Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#F9D71C',
                        'dark': '#2F2F2F',
                        'accent': '#69B578',
                    },
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'fadeIn': 'fadeIn 1s ease-in-out',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #F9D71C 0%, #FFE55C 50%, #FFFFFF 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="font-inter bg-white">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 md:px-6">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                        <span class="text-2xl">🐥</span>
                    </div>
                    <div>
                        <span class="text-xl font-bold text-dark">Chick Patrol</span>
                        <div class="text-xs text-gray-500">Smart Poultry System</div>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#beranda" class="text-dark hover:text-primary font-medium transition duration-200">Beranda</a>
                    <a href="#fitur" class="text-dark hover:text-primary font-medium transition duration-200">Fitur</a>
                    <a href="#artikel" class="text-dark hover:text-primary font-medium transition duration-200">Artikel</a>
                    <a href="#marketplace" class="text-dark hover:text-primary font-medium transition duration-200">Marketplace</a>
                    <a href="{{ url('/login') }}" class="bg-primary hover:bg-yellow-400 text-dark font-semibold py-2 px-6 rounded-lg transition duration-200 transform hover:-translate-y-0.5 shadow-md">
                        Login Peternak
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button type="button" class="text-dark hover:text-primary">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="beranda" class="gradient-bg py-16 md:py-24">
        <div class="container mx-auto px-4 md:px-6">
            <div class="flex flex-col lg:flex-row items-center justify-between">
                <!-- Hero Content -->
                <div class="lg:w-1/2 mb-12 lg:mb-0 text-center lg:text-left animate-fadeIn">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-dark mb-6 leading-tight">
                        Smart Poultry
                        <span class="block text-dark">Monitoring & Control</span>
                    </h1>
                    <p class="text-lg text-gray-700 mb-8 leading-relaxed max-w-2xl">
                        Sistem cerdas berbasis IoT dan Machine Learning untuk mengoptimalkan produktivitas peternakan ayam broiler Anda. Pantau kondisi kandang secara real-time dan kelola bisnis ternak dengan efisien.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ url('/login') }}" class="bg-dark hover:bg-gray-800 text-white font-semibold py-3 px-8 rounded-lg text-center transition duration-200 transform hover:-translate-y-1 shadow-lg">
                            Mulai Pantau Sekarang
                        </a>
                        <a href="#marketplace" class="bg-white hover:bg-gray-50 text-dark font-semibold py-3 px-8 rounded-lg border border-gray-300 text-center transition duration-200 transform hover:-translate-y-1 shadow-sm">
                            Jelajahi Marketplace
                        </a>
                    </div>
                </div>

                <!-- Hero Illustration -->
                <div class="lg:w-1/2 flex justify-center animate-float">
                    <div class="relative">
                        <div class="bg-white rounded-3xl p-8 shadow-2xl border border-gray-100">
                            <div class="grid grid-cols-2 gap-6">
                                <!-- IoT Monitoring Card -->
                                <div class="bg-gradient-to-br from-primary to-yellow-200 rounded-2xl p-6 text-center">
                                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-microchip text-2xl text-dark"></i>
                                    </div>
                                    <h3 class="font-bold text-dark mb-2">IoT Monitoring</h3>
                                    <p class="text-sm text-dark">Real-time Sensors</p>
                                </div>
                                
                                <!-- Robot Card -->
                                <div class="bg-gradient-to-br from-accent to-green-300 rounded-2xl p-6 text-center">
                                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-robot text-2xl text-accent"></i>
                                    </div>
                                    <h3 class="font-bold text-dark mb-2">Robot Otonom</h3>
                                    <p class="text-sm text-dark">Auto Cleaning</p>
                                </div>
                                
                                <!-- Analytics Card -->
                                <div class="bg-gradient-to-br from-blue-400 to-blue-300 rounded-2xl p-6 text-center">
                                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-chart-line text-2xl text-blue-500"></i>
                                    </div>
                                    <h3 class="font-bold text-dark mb-2">AI Analytics</h3>
                                    <p class="text-sm text-dark">Smart Insights</p>
                                </div>
                                
                                <!-- Marketplace Card -->
                                <div class="bg-gradient-to-br from-purple-400 to-purple-300 rounded-2xl p-6 text-center">
                                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-store text-2xl text-purple-500"></i>
                                    </div>
                                    <h3 class="font-bold text-dark mb-2">Marketplace</h3>
                                    <p class="text-sm text-dark">Direct Selling</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="py-20 bg-white">
        <div class="container mx-auto px-4 md:px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-dark mb-4">
                    Solusi <span class="text-primary">Lengkap</span> untuk Peternakan Modern
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Integrasikan teknologi canggih untuk meningkatkan produktivitas dan efisiensi peternakan ayam broiler Anda
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- IoT Monitoring Feature -->
                <div class="bg-gray-50 rounded-3xl p-8 card-hover">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-primary rounded-2xl flex items-center justify-center mr-4">
                            <i class="fas fa-satellite-dish text-dark text-xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-dark">Monitoring Lingkungan IoT</h3>
                    </div>
                    <p class="text-gray-600 mb-6">
                        Pantau kondisi kandang secara real-time dengan sensor IoT yang akurat dan terintegrasi.
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white rounded-xl p-4 text-center">
                            <i class="fas fa-thermometer-half text-red-500 text-2xl mb-2"></i>
                            <div class="font-semibold text-dark">Suhu</div>
                            <div class="text-sm text-gray-500">Real-time monitoring</div>
                        </div>
                        <div class="bg-white rounded-xl p-4 text-center">
                            <i class="fas fa-tint text-blue-500 text-2xl mb-2"></i>
                            <div class="font-semibold text-dark">Kelembapan</div>
                            <div class="text-sm text-gray-500">Optimal humidity</div>
                        </div>
                        <div class="bg-white rounded-xl p-4 text-center">
                            <i class="fas fa-wind text-green-500 text-2xl mb-2"></i>
                            <div class="font-semibold text-dark">Amonia</div>
                            <div class="text-sm text-gray-500">Gas level control</div>
                        </div>
                        <div class="bg-white rounded-xl p-4 text-center">
                            <i class="fas fa-sun text-yellow-500 text-2xl mb-2"></i>
                            <div class="font-semibold text-dark">Cahaya</div>
                            <div class="text-sm text-gray-500">Light intensity</div>
                        </div>
                    </div>
                </div>

                <!-- Marketplace Feature -->
                <div class="bg-gray-50 rounded-3xl p-8 card-hover">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-accent rounded-2xl flex items-center justify-center mr-4">
                            <i class="fas fa-shopping-cart text-white text-xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-dark">Marketplace Hasil Ternak</h3>
                    </div>
                    <p class="text-gray-600 mb-6">
                        Platform terintegrasi untuk menjual hasil ternak dan membeli kebutuhan peternakan dengan mudah.
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white rounded-xl p-4 text-center">
                            <i class="fas fa-drumstick-bite text-red-400 text-2xl mb-2"></i>
                            <div class="font-semibold text-dark">Daging Ayam</div>
                            <div class="text-sm text-gray-500">Fresh quality</div>
                        </div>
                        <div class="bg-white rounded-xl p-4 text-center">
                            <i class="fas fa-egg text-yellow-400 text-2xl mb-2"></i>
                            <div class="font-semibold text-dark">Telur</div>
                            <div class="text-sm text-gray-500">Farm fresh</div>
                        </div>
                        <div class="bg-white rounded-xl p-4 text-center">
                            <i class="fas fa-seedling text-green-500 text-2xl mb-2"></i>
                            <div class="font-semibold text-dark">Pakan</div>
                            <div class="text-sm text-gray-500">Quality feed</div>
                        </div>
                        <div class="bg-white rounded-xl p-4 text-center">
                            <i class="fas fa-tools text-blue-500 text-2xl mb-2"></i>
                            <div class="font-semibold text-dark">Peralatan</div>
                            <div class="text-sm text-gray-500">Farming tools</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Articles Section -->
    <section id="artikel" class="py-20 bg-gray-50">
        <div class="container mx-auto px-4 md:px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-dark mb-4">
                    Artikel & <span class="text-primary">Berita Terkini</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Update terbaru seputar peternakan ayam broiler, teknologi agrotech, dan tips manajemen kandang
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Article 1 -->
                <div class="bg-white rounded-2xl overflow-hidden card-hover shadow-lg">
                    <div class="h-48 bg-gradient-to-r from-primary to-yellow-300 flex items-center justify-center">
                        <i class="fas fa-robot text-white text-6xl"></i>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-dark mb-3">Robot Otonom untuk Kebersihan Kandang</h3>
                        <p class="text-gray-600 mb-4">
                            Teknologi robot terbaru yang dapat membersihkan kandang secara otomatis, mengurangi tenaga kerja dan meningkatkan kebersihan.
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500"><i class="fas fa-calendar mr-2"></i>15 Mar 2025</span>
                            <a href="#" class="text-primary hover:text-yellow-600 font-semibold">Baca Selengkapnya →</a>
                        </div>
                    </div>
                </div>

                <!-- Article 2 -->
                <div class="bg-white rounded-2xl overflow-hidden card-hover shadow-lg">
                    <div class="h-48 bg-gradient-to-r from-accent to-green-400 flex items-center justify-center">
                        <i class="fas fa-chart-line text-white text-6xl"></i>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-dark mb-3">Optimasi Produksi dengan IoT Monitoring</h3>
                        <p class="text-gray-600 mb-4">
                            Cara memanfaatkan data real-time dari sensor IoT untuk meningkatkan efisiensi produksi ayam broiler hingga 30%.
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500"><i class="fas fa-calendar mr-2"></i>10 Mar 2025</span>
                            <a href="#" class="text-primary hover:text-yellow-600 font-semibold">Baca Selengkapnya →</a>
                        </div>
                    </div>
                </div>

                <!-- Article 3 -->
                <div class="bg-white rounded-2xl overflow-hidden card-hover shadow-lg">
                    <div class="h-48 bg-gradient-to-r from-blue-400 to-blue-500 flex items-center justify-center">
                        <i class="fas fa-shield-alt text-white text-6xl"></i>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-dark mb-3">Pencegahan Penyakit pada Ayam Broiler</h3>
                        <p class="text-gray-600 mb-4">
                            Strategi efektif mencegah penyakit dengan monitoring lingkungan dan deteksi dini menggunakan machine learning.
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500"><i class="fas fa-calendar mr-2"></i>5 Mar 2025</span>
                            <a href="#" class="text-primary hover:text-yellow-600 font-semibold">Baca Selengkapnya →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="marketplace" class="py-20 bg-dark text-white">
        <div class="container mx-auto px-4 md:px-6 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">
                Siap <span class="text-primary">Mengoptimalkan</span> Peternakan Anda?
            </h2>
            <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
                Bergabunglah dengan ratusan peternak yang telah meningkatkan produktivitas hingga 40% dengan sistem Chick Patrol.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ url('/login') }}" class="bg-primary hover:bg-yellow-400 text-dark font-bold py-4 px-8 rounded-lg text-lg transition duration-200 transform hover:-translate-y-1 shadow-2xl">
                    Login Peternak <i class="fas fa-arrow-right ml-2"></i>
                </a>
                <a href="#" class="bg-transparent border-2 border-white hover:bg-white hover:bg-opacity-10 text-white font-bold py-4 px-8 rounded-lg text-lg transition duration-200 transform hover:-translate-y-1">
                    <i class="fas fa-store mr-2"></i>Jelajahi Marketplace
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4 md:px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                            <span class="text-xl">🐥</span>
                        </div>
                        <span class="text-2xl font-bold">Chick Patrol</span>
                    </div>
                    <p class="text-gray-400 mb-6 max-w-md">
                        Platform teknologi terdepan untuk transformasi digital peternakan ayam broiler di Indonesia dengan sistem IoT dan AI.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-primary rounded-full flex items-center justify-center transition duration-200">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-primary rounded-full flex items-center justify-center transition duration-200">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-primary rounded-full flex items-center justify-center transition duration-200">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-primary rounded-full flex items-center justify-center transition duration-200">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-bold mb-6">Tautan Cepat</h3>
                    <ul class="space-y-3">
                        <li><a href="#beranda" class="text-gray-400 hover:text-primary transition duration-200">Beranda</a></li>
                        <li><a href="#fitur" class="text-gray-400 hover:text-primary transition duration-200">Fitur</a></li>
                        <li><a href="#artikel" class="text-gray-400 hover:text-primary transition duration-200">Artikel</a></li>
                        <li><a href="#marketplace" class="text-gray-400 hover:text-primary transition duration-200">Marketplace</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h3 class="text-lg font-bold mb-6">Kontak</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <i class="fas fa-envelope text-primary mr-3"></i>
                            <span class="text-gray-400">hello@chickpatrol.id</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone text-primary mr-3"></i>
                            <span class="text-gray-400">+62 21 1234 5678</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt text-primary mr-3"></i>
                            <span class="text-gray-400">Jakarta, Indonesia</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-400">
                    © 2025 Chick Patrol. All rights reserved. | Developed with <i class="fas fa-heart text-red-500 mx-1"></i> for Indonesian Poultry Industry
                </p>
            </div>
        </div>
    </footer>
</body>
</html>