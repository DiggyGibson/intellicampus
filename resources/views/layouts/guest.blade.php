<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'IntelliCampus') }} - @yield('title', 'Welcome')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    
    <style>
        /* Public Layout Styles */
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f8f9fa;
        }
        
        /* Public Navbar */
        .public-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            padding: 1rem 0;
        }
        
        .public-navbar .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e3c72;
        }
        
        .public-navbar .nav-link {
            color: #495057;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: color 0.3s ease;
        }
        
        .public-navbar .nav-link:hover {
            color: #1e3c72;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        
        .hero-section h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        /* Footer */
        .public-footer {
            background: #2c3e50;
            color: white;
            padding: 3rem 0 1rem;
            margin-top: 4rem;
        }
        
        .public-footer a {
            color: #ecf0f1;
            text-decoration: none;
        }
        
        .public-footer a:hover {
            color: #3498db;
        }
        
        /* Main Content */
        .public-content {
            min-height: 60vh;
            padding: 2rem 0;
        }
    </style>
</head>
<body>
    {{-- Public Navigation Bar --}}
    <nav class="navbar navbar-expand-lg public-navbar sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-graduation-cap me-2"></i>
                IntelliCampus
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="publicNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Admissions
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admissions">How to Apply</a></li>
                            <li><a class="dropdown-item" href="/admissions/requirements">Requirements</a></li>
                            <li><a class="dropdown-item" href="/admissions/programs">Programs</a></li>
                            <li><a class="dropdown-item" href="/admissions/deadlines">Deadlines</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/apply/status">Check Application Status</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/academics">Academics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/campus-life">Campus Life</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/contact">Contact</a>
                    </li>
                </ul>
                
                <div class="d-flex ms-3">
                    <a href="/apply" class="btn btn-primary me-2">
                        <i class="fas fa-edit me-1"></i> Apply Now
                    </a>
                    <a href="/login" class="btn btn-outline-primary">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    <div class="container mt-3">
        @include('layouts.flash-messages')
    </div>

    {{-- Main Content --}}
    <main class="public-content">
        @yield('content')
    </main>

    {{-- Public Footer --}}
    <footer class="public-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5>About IntelliCampus</h5>
                    <p>Empowering education through innovative technology and comprehensive academic management.</p>
                    <div class="social-links mt-3">
                        <a href="#" class="me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/admissions">Admissions</a></li>
                        <li class="mb-2"><a href="/academics">Academics</a></li>
                        <li class="mb-2"><a href="/campus-life">Campus Life</a></li>
                        <li class="mb-2"><a href="/news">News & Events</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6>Resources</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/library">Library</a></li>
                        <li class="mb-2"><a href="/calendar">Academic Calendar</a></li>
                        <li class="mb-2"><a href="/directory">Directory</a></li>
                        <li class="mb-2"><a href="/careers">Careers</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h6>Contact Information</h6>
                    <p>
                        <i class="fas fa-map-marker-alt me-2"></i> 123 University Avenue<br>
                        <span class="ms-4">Education City, EC 12345</span>
                    </p>
                    <p><i class="fas fa-phone me-2"></i> +1 (555) 123-4567</p>
                    <p><i class="fas fa-envelope me-2"></i> info@intellicampus.edu</p>
                </div>
            </div>
            
            <hr class="border-secondary my-4">
            
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; {{ date('Y') }} IntelliCampus. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="/privacy" class="me-3">Privacy Policy</a>
                    <a href="/terms" class="me-3">Terms of Use</a>
                    <a href="/accessibility">Accessibility</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>