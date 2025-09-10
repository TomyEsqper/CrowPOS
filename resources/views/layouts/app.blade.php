<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CrowPOS') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: 'Figtree', sans-serif;
        }
        
        .tenant-branding {
            --primary-color: {{ function_exists('tenant') && tenant('branding.primary_color') ? tenant('branding.primary_color') : '#3B82F6' }};
            --secondary-color: {{ function_exists('tenant') && tenant('branding.secondary_color') ? tenant('branding.secondary_color') : '#1E40AF' }};
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
    </style>

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="tenant-branding">
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <h1 class="text-xl font-bold text-gray-900">
                                {{ function_exists('tenant') && tenant('name') ? tenant('name') : config('app.name') }}
                            </h1>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        @auth
                            <span class="text-gray-700">{{ Auth::user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-500 hover:text-gray-700">
                                    Logout
                                </button>
                            </form>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script nonce="{{ csp_nonce() }}">
        // Global configuration for Livewire and Alpine
        window.Livewire = window.Livewire || {};
        window.Alpine = window.Alpine || {};
        
        // Tenant-specific configuration
        window.tenantConfig = {
            id: '{{ function_exists("tenant") && tenant("id") ? tenant("id") : "" }}',
            name: '{{ function_exists("tenant") && tenant("name") ? tenant("name") : "" }}',
            branding: @json(function_exists('tenant') && tenant('branding') ? tenant('branding') : [])
        };
    </script>

    @livewireScripts
    @stack('scripts')
</body>
</html>
