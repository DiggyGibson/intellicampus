<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Application Portal - IntelliCampus</title>
    
    <!-- Fonts & Styles -->
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Figtree', sans-serif;
        }
        
        .portal-header {
            background: #1e3c72;
            color: white;
            padding: 1rem 0;
        }
        
        .portal-header h4 {
            margin: 0;
        }
        
        .progress {
            height: 5px;
            border-radius: 0;
        }
        
        .application-container {
            max-width: 900px;
            margin: 2rem auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 2rem;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding: 0;
            list-style: none;
        }
        
        .step-indicator li {
            flex: 1;
            text-align: center;
            position: relative;
        }
        
        .step-indicator li:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 15px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #dee2e6;
            z-index: -1;
        }
        
        .step-indicator li.active::after,
        .step-indicator li.completed::after {
            background: #28a745;
        }
        
        .step-indicator .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #dee2e6;
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
        }
        
        .step-indicator li.active .step-number {
            background: #007bff;
        }
        
        .step-indicator li.completed .step-number {
            background: #28a745;
        }
    </style>
</head>
<body>
    {{-- Portal Header --}}
    <header class="portal-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h4>
                    <i class="fas fa-graduation-cap me-2"></i>
                    IntelliCampus Application Portal
                </h4>
                <div>
                    @if(isset($application))
                        <span class="me-3">ID: {{ $application->application_number }}</span>
                    @endif
                    <button class="btn btn-outline-light btn-sm" id="saveExitBtn">
                        <i class="fas fa-save me-1"></i> Save & Exit
                    </button>
                </div>
            </div>
        </div>
    </header>

    {{-- Progress Bar --}}
    @if(isset($progress))
    <div class="progress">
        <div class="progress-bar" style="width: {{ $progress }}%"></div>
    </div>
    @endif

    {{-- Main Content --}}
    <div class="container">
        <div class="application-container">
            {{-- Step Indicator --}}
            @if(isset($steps))
            <ul class="step-indicator">
                @foreach($steps as $index => $step)
                <li class="{{ $step['status'] }}">
                    <span class="step-number">
                        @if($step['status'] == 'completed')
                            <i class="fas fa-check"></i>
                        @else
                            {{ $index + 1 }}
                        @endif
                    </span>
                    <div class="step-label">{{ $step['label'] }}</div>
                </li>
                @endforeach
            </ul>
            @endif

            {{-- Flash Messages --}}
            @include('layouts.flash-messages')

            {{-- Page Content --}}
            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.getElementById('saveExitBtn').addEventListener('click', function() {
            if (confirm('Your progress will be saved. You can continue later using your application link. Proceed?')) {
                // Save form data via AJAX
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
    
    @stack('scripts')
</body>
</html>