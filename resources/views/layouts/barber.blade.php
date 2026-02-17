<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BarberSaaS') }} - Barber</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script>
        window.stripe_key = "{{ config('services.stripe.key') }}";
    </script>
    <!-- Scripts -->
    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ auth()->user()->shop->name }}</span>
                        </div>
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ route('barber.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('barber.dashboard') ? 'border-indigo-400 text-gray-900 dark:text-white focus:outline-none focus:border-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300' }}">
                                {{ __('Dashboard') }}
                            </a>
                            <a href="{{ route('barber.calendar') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('barber.calendar') ? 'border-indigo-400 text-gray-900 dark:text-white focus:outline-none focus:border-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300' }}">
                                {{ __('Calendar') }}
                            </a>
                        </div>
                    </div>
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="mr-4 flex space-x-2 text-xs">
                             <a href="{{ route('language.switch', 'en') }}" class="{{ app()->getLocale() == 'en' ? 'font-bold text-indigo-600' : 'text-gray-500' }}">EN</a>
                             <span class="text-gray-300">|</span>
                             <a href="{{ route('language.switch', 'fr') }}" class="{{ app()->getLocale() == 'fr' ? 'font-bold text-indigo-600' : 'text-gray-500' }}">FR</a>
                             <span class="text-gray-300">|</span>
                             <a href="{{ route('language.switch', 'ar') }}" class="{{ app()->getLocale() == 'ar' ? 'font-bold text-indigo-600' : 'text-gray-500' }}">AR</a>
                        </div>
                        <div class="ml-3 relative">
                             <div class="text-sm font-medium text-gray-500">{{ auth()->user()->name }} ({{ __('Barber') }})</div>
                        </div>
                         <form method="POST" action="{{ route('logout') }}" class="ml-4">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-900">{{ __('Logout') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="flex-1 py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if(session('success'))
                    <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg shadow-sm" role="alert">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                @endif
                
                @yield('content')
            </div>
        </main>
    </div>
    @stack('vue-components')
    @stack('scripts')
</body>
</html>
