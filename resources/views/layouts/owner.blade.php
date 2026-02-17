<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BarberSaaS') }} - Owner</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Inter:wght@100..900&family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
    
    <link rel="icon" type="image/png" href="/images/logo.png">

    <script>
        window.stripe_key = "{{ config('services.stripe.key') }}";
    </script>
    <!-- Scripts -->
    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-300">
    <div class="min-h-screen flex selection:bg-amber-100 selection:text-amber-900" id="owner-app">
        <!-- Sidebar -->
        <aside class="w-72 bg-slate-900 border-r border-white/5 flex flex-col sticky top-0 h-screen hidden md:flex">
            <div class="h-20 flex items-center px-6 border-b border-white/5 mb-6">
                <a href="{{ route('owner.dashboard') }}" class="flex items-center gap-3 active:scale-95 transition-transform duration-200">
                    <img src="/images/logo.png" alt="Logo" class="w-10 h-10 shadow-lg shadow-amber-500/20 rounded-xl" />
                    <span class="text-xl font-black text-white tracking-tighter uppercase italic">{{ auth()->user()->shop->name }}</span>
                </a>
            </div>

            <nav class="flex-1 px-4 space-y-1">
                <a href="{{ route('owner.dashboard') }}" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('owner.dashboard') ? 'bg-amber-500 text-slate-900 shadow-lg shadow-amber-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    {{ __('Dashboard') }}
                </a>
                <a href="{{ route('owner.barbers.index') }}" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('owner.barbers.*') ? 'bg-amber-500 text-slate-900 shadow-lg shadow-amber-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    {{ __('Barbers') }}
                </a>
                <a href="{{ route('owner.services.index') }}" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('owner.services.*') ? 'bg-amber-500 text-slate-900 shadow-lg shadow-amber-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M刀8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ __('Services') }}
                </a>
                <a href="{{ route('owner.customers.index') }}" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('owner.customers.*') ? 'bg-amber-500 text-slate-900 shadow-lg shadow-amber-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    {{ __('Customers') }}
                </a>
                <a href="{{ route('owner.calendar') }}" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('owner.calendar') ? 'bg-amber-500 text-slate-900 shadow-lg shadow-amber-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ __('Calendar') }}
                </a>
            </nav>
            
            <div class="p-4 border-t border-white/5">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center px-4 py-3 text-sm font-bold text-rose-500 hover:bg-rose-500/10 rounded-xl w-full transition-all">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        {{ __('Logout') }}
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Mobile Header -->
            <header class="md:hidden h-20 bg-slate-900 px-6 flex justify-between items-center shrink-0">
                 <div class="flex items-center gap-3">
                    <img src="/images/logo.png" alt="Logo" class="w-10 h-10 rounded-xl" />
                    <span class="text-xl font-black text-white uppercase italic tracking-tighter">{{ auth()->user()->shop->name }}</span>
                 </div>
                 <button class="p-2 rounded-xl text-slate-400 hover:bg-white/5 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                 </button>
            </header>

            <header class="hidden md:flex h-20 bg-white/80 dark:bg-slate-900/50 backdrop-blur-xl border-b border-slate-200 dark:border-white/5 sticky top-0 z-30 px-8 items-center justify-between shrink-0">
                <h1 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                    @yield('header')
                </h1>
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-white/5">
                        <a href="{{ route('language.switch', 'en') }}" class="text-[10px] font-black tracking-widest {{ app()->getLocale() == 'en' ? 'text-amber-500' : 'text-slate-400 hover:text-slate-600' }}">EN</a>
                        <span class="text-slate-200 dark:text-white/10 text-[10px]">/</span>
                        <a href="{{ route('language.switch', 'fr') }}" class="text-[10px] font-black tracking-widest {{ app()->getLocale() == 'fr' ? 'text-amber-500' : 'text-slate-400 hover:text-slate-600' }}">FR</a>
                        <span class="text-slate-200 dark:text-white/10 text-[10px]">/</span>
                        <a href="{{ route('language.switch', 'ar') }}" class="text-[10px] font-black tracking-widest {{ app()->getLocale() == 'ar' ? 'text-amber-500' : 'text-slate-400 hover:text-slate-600 font-arabic' }}">AR</a>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-xs font-black text-slate-900 dark:text-white uppercase">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] font-bold text-amber-500 uppercase tracking-widest">Owner</p>
                        </div>
                        <div class="h-10 w-10 rounded-xl bg-amber-500 flex items-center justify-center text-slate-900 font-black shadow-lg shadow-amber-500/20">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-8">
                 @if(session('success'))
                    <div class="mb-8 p-4 rounded-2xl bg-emerald-500/10 text-emerald-600 text-sm font-bold border border-emerald-500/20 flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ session('success') }}
                    </div>
                @endif
            
                @yield('content')
            </div>
        </main>
    </div>
    
    <!-- Vue Mount Point -->
    @stack('vue-components')
    
    @stack('scripts')
</body>
</html>
