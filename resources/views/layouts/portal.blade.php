<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Application Portal - IntelliCampus</title>
    
    <!-- Fonts & Styles -->
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --primary-color: #1e3c72;
            --primary-dark: #162c54;
            --primary-light: #2a5298;
            --accent-color: #f39c12;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --header-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }

        /* Header Styles */
        .portal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            min-height: var(--header-height);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            height: var(--header-height);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .portal-brand {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .portal-brand i {
            font-size: 1.75rem;
            color: var(--accent-color);
        }

        .portal-brand h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .application-info {
            background: rgba(255,255,255,0.15);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
        }

        .btn-header {
            background: transparent;
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-header:hover {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.5);
            transform: translateY(-1px);
        }

        /* Progress Section */
        .progress-section {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 2rem 0;
        }

        .progress-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Horizontal Step Indicator */
        .step-indicator {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            padding: 0;
            list-style: none;
            position: relative;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50px;
            right: 50px;
            height: 2px;
            background: #e5e7eb;
            z-index: 1;
        }

        .step-item {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 2;
            padding: 0 10px;
        }

        .step-item.completed .step-connector,
        .step-item.active .step-connector {
            background: var(--success-color);
        }

        .step-connector {
            position: absolute;
            top: 20px;
            left: 50%;
            right: -50%;
            height: 2px;
            background: #e5e7eb;
            z-index: 1;
        }

        .step-item:last-child .step-connector {
            display: none;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 3px solid #e5e7eb;
            color: #9ca3af;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            position: relative;
            z-index: 3;
            transition: all 0.3s ease;
        }

        .step-item.active .step-circle {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3);
            transform: scale(1.1);
        }

        .step-item.completed .step-circle {
            background: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }

        .step-label {
            font-size: 0.9rem;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .step-item.active .step-label {
            color: var(--primary-color);
            font-weight: 600;
        }

        .step-item.completed .step-label {
            color: var(--success-color);
        }

        .step-description {
            font-size: 0.75rem;
            color: #9ca3af;
            display: none;
        }

        .step-item.active .step-description {
            display: block;
            color: #6b7280;
        }

        /* Progress Bar */
        .progress-bar-wrapper {
            max-width: 800px;
            margin: 0 auto;
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .progress-text {
            font-size: 0.9rem;
            color: #4b5563;
            font-weight: 500;
        }

        .progress-percentage {
            font-size: 1rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .progress {
            height: 10px;
            border-radius: 20px;
            background: #f3f4f6;
            overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--primary-light) 0%, var(--primary-color) 100%);
            transition: width 0.5s ease;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Main Content Area */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        .content-wrapper {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            padding: 3rem;
            min-height: 500px;
        }

        .content-header {
            margin-bottom: 2.5rem;
        }

        .content-title {
            font-size: 2rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.75rem;
            letter-spacing: -0.5px;
        }

        .content-subtitle {
            font-size: 1.1rem;
            color: #6b7280;
            line-height: 1.6;
        }

        /* Alert Messages */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        .alert-success {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }

        .alert-danger {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        /* Form Styles (if needed) */
        .form-section {
            margin-bottom: 2.5rem;
        }

        .form-section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f3f4f6;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .header-container,
            .progress-container,
            .main-content {
                padding: 0 1.5rem;
            }

            .content-wrapper {
                padding: 2rem;
            }

            .step-description {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            .portal-brand h1 {
                font-size: 1.2rem;
            }

            .application-info {
                display: none;
            }

            .step-indicator {
                overflow-x: auto;
                padding-bottom: 1rem;
                margin-bottom: 1rem;
            }

            .step-indicator::before {
                display: none;
            }

            .step-item {
                min-width: 100px;
                flex: 0 0 auto;
            }

            .step-circle {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }

            .step-label {
                font-size: 0.8rem;
            }

            .content-wrapper {
                padding: 1.5rem;
                border-radius: 12px;
            }

            .content-title {
                font-size: 1.5rem;
            }

            .content-subtitle {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .header-container {
                padding: 0 1rem;
            }

            .portal-brand i {
                font-size: 1.5rem;
            }

            .btn-header {
                padding: 0.4rem 1rem;
                font-size: 0.85rem;
            }

            .main-content {
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <header class="portal-header">
        <div class="header-container">
            <div class="portal-brand">
                <i class="fas fa-graduation-cap"></i>
                <h1>IntelliCampus Application</h1>
            </div>
            <div class="header-actions">
                @if(isset($application))
                    <div class="application-info">
                        <i class="fas fa-id-badge me-1"></i>
                        {{ $application->application_number }}
                    </div>
                @endif
                <button class="btn-header" id="saveExitBtn">
                    <i class="fas fa-save me-1"></i> Save & Exit
                </button>
            </div>
        </div>
    </header>

    {{-- Progress Section --}}
    <section class="progress-section">
        <div class="progress-container">
            {{-- Horizontal Steps --}}
            @if(isset($steps))
            <ul class="step-indicator">
                @foreach($steps as $index => $step)
                <li class="step-item {{ $step['status'] }}">
                    @if($index < count($steps) - 1)
                    <div class="step-connector"></div>
                    @endif
                    <div class="step-circle">
                        @if($step['status'] == 'completed')
                            <i class="fas fa-check"></i>
                        @else
                            {{ $index + 1 }}
                        @endif
                    </div>
                    <div class="step-label">{{ $step['label'] }}</div>
                    @if(isset($step['description']))
                    <div class="step-description">{{ $step['description'] }}</div>
                    @endif
                </li>
                @endforeach
            </ul>
            @endif

            {{-- Progress Bar --}}
            @if(isset($progress))
            <div class="progress-bar-wrapper">
                <div class="progress-info">
                    <span class="progress-text">Application Progress</span>
                    <span class="progress-percentage">{{ $progress }}%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ $progress }}%"></div>
                </div>
            </div>
            @endif
        </div>
    </section>

    {{-- Main Content --}}
    <main class="main-content">
        {{-- Flash Messages --}}
        @include('layouts.flash-messages')

        {{-- Content Wrapper --}}
        <div class="content-wrapper">
            @if(isset($pageTitle) || isset($pageSubtitle))
            <div class="content-header">
                @if(isset($pageTitle))
                <h2 class="content-title">{{ $pageTitle }}</h2>
                @endif
                @if(isset($pageSubtitle))
                <p class="content-subtitle">{{ $pageSubtitle }}</p>
                @endif
            </div>
            @endif

            {{-- Page Content --}}
            @yield('content')
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <script>
        // Save & Exit functionality
        document.getElementById('saveExitBtn')?.addEventListener('click', function() {
            if (confirm('Your progress will be saved. You can continue later using your application link. Proceed?')) {
                const form = document.querySelector('form');
                if (form) {
                    const formData = new FormData(form);
                    formData.append('save_and_exit', true);
                    
                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    }).then(() => {
                        window.location.href = '/';
                    });
                } else {
                    window.location.href = '/';
                }
            }
        });
    </script>

    {{-- This is crucial - yield the scripts section from child views --}}
    @yield('scripts')
    @stack('scripts')
</body>
</html>