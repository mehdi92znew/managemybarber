<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { trans } from '@/lang';

const sidebarOpen = ref(false);
const isCollapsed = ref(false);
const page = usePage();
const user = computed(() => page.props.auth.user);
const currentLocale = computed(() => page.props.locale || 'en');

const toggleSidebar = () => {
    if (window.innerWidth < 768) {
        sidebarOpen.value = !sidebarOpen.value;
    } else {
        isCollapsed.value = !isCollapsed.value;
    }
};

const navigation = computed(() => {
    const role = user.value.role;
    const links = [];

    // Dashboard (Common)
    if (role === 'super_admin') {
        links.push({ name: trans('dashboard', currentLocale.value), route: 'admin.dashboard', icon: 'HomeIcon' });
        links.push({ name: trans('shops', currentLocale.value), route: 'admin.shops.index', icon: 'BuildingStorefrontIcon' });
    } else    if (role === 'owner') {
        links.push({ name: trans('dashboard', currentLocale.value), route: 'owner.dashboard', icon: 'HomeIcon' });
        links.push({ name: trans('appointments', currentLocale.value), route: 'owner.appointments.list', icon: 'CalendarIcon' });
        links.push({ name: trans('calendar', currentLocale.value), route: 'owner.calendar', icon: 'ClockIcon' });
        links.push({ name: trans('daily_planning', currentLocale.value), route: 'owner.calendar.daily', icon: 'ViewColumnsIcon' });
        links.push({ name: trans('customers', currentLocale.value), route: 'owner.customers.index', icon: 'UsersIcon' });
        links.push({ name: trans('services', currentLocale.value), route: 'owner.services.index', icon: 'ScissorsIcon' }); // Assuming Scissors
        links.push({ name: trans('bills', currentLocale.value), route: 'owner.bills.index', icon: 'BanknotesIcon' });
        links.push({ name: trans('payouts', currentLocale.value), route: 'owner.barber-payouts.index', icon: 'WalletIcon' });
        links.push({ name: trans('reports', currentLocale.value), route: 'owner.barber-report', icon: 'ChartBarIcon' });
        links.push({ name: trans('barbers', currentLocale.value), route: 'owner.barbers.index', icon: 'UserGroupIcon' });
    } else if (role === 'barber') {
        links.push({ name: trans('dashboard', currentLocale.value), route: 'barber.dashboard', icon: 'HomeIcon' });
        links.push({ name: trans('calendar', currentLocale.value), route: 'barber.calendar', icon: 'CalendarIcon' });
        links.push({ name: trans('payouts', currentLocale.value), route: 'barber.payouts.index', icon: 'WalletIcon' });
    }

    return links;
});

const isRouteActive = (routeName) => {
    return route().current(routeName);
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 flex selection:bg-amber-100 selection:text-amber-900">
        
        <!-- Mobile Sidebar Overlay -->
        <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm md:hidden transition-all duration-300"></div>

        <!-- Sidebar -->
        <aside 
            :class="[
                'fixed inset-y-0 left-0 z-50 bg-slate-900 dark:bg-black/40 dark:backdrop-blur-2xl border-r border-slate-800 dark:border-white/5 transform transition-all duration-300 ease-in-out md:translate-x-0 md:static md:inset-auto',
                isCollapsed ? 'md:w-20' : 'md:w-72',
                'w-72',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
        >
            <!-- Logo Section -->
            <div class="flex items-center justify-between h-20 px-6 border-b border-white/5 overflow-hidden">
                <Link :href="route('dashboard')" class="flex items-center gap-3 active:scale-95 transition-transform duration-200 shrink-0">
                    <ApplicationLogo class="w-10 h-10 shadow-lg shadow-amber-500/20 rounded-xl" />
                    <span v-if="!isCollapsed" class="text-xl font-black text-white tracking-tighter uppercase italic transition-all duration-300 whitespace-nowrap">Barber<span class="text-amber-500">App</span></span>
                </Link>
                <!-- Desktop Collapse Button -->
                <button @click="isCollapsed = !isCollapsed" class="hidden md:flex p-2 rounded-xl text-slate-500 hover:bg-white/5 hover:text-white transition-all transform" :class="isCollapsed ? 'rotate-180' : ''">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
                </button>
                <!-- Mobile Close Button -->
                <button @click="sidebarOpen = false" class="md:hidden p-2 rounded-xl text-slate-400 hover:bg-white/5 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Navigation Section -->
            <div class="flex-1 overflow-y-auto py-6 px-4 space-y-8">
                <div>
                    <p v-if="!isCollapsed" class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] px-4 mb-4 whitespace-nowrap">{{ __('main_menu', currentLocale) }}</p>
                    <nav class="space-y-1">
                        <Link
                            v-for="item in navigation"
                            :key="item.name"
                            :href="route(item.route)"
                            :title="isCollapsed ? item.name : ''"
                            :class="[
                                isRouteActive(item.route)
                                    ? 'bg-amber-500/10 text-amber-500 shadow-[inset_0_0_20px_rgba(245,158,11,0.05)]'
                                    : 'text-slate-400 hover:bg-white/5 hover:text-white',
                                'group flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 overflow-hidden'
                            ]"
                        >
                            <div :class="[
                                'p-2 rounded-lg transition-colors duration-200',
                                isCollapsed ? 'mx-auto' : 'mr-3',
                                isRouteActive(item.route) ? 'bg-amber-500 text-slate-900' : 'bg-slate-800 text-slate-400 group-hover:bg-slate-700 group-hover:text-white'
                            ]">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path v-if="item.icon === 'HomeIcon'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    <path v-else-if="item.icon === 'CalendarIcon'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    <path v-else-if="item.icon === 'UsersIcon'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    <path v-else-if="item.icon === 'UserGroupIcon'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    <path v-else-if="item.icon === 'BuildingStorefrontIcon'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    <path v-else-if="item.icon === 'ScissorsIcon'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z" />
                                    <path v-else-if="item.icon === 'BanknotesIcon'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    <path v-else-if="item.icon === 'WalletIcon'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    <path v-else-if="item.icon === 'ChartBarIcon'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    <path v-else-if="item.icon === 'ClockIcon'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    <path v-else-if="item.icon === 'ViewColumnsIcon'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2m0 10V7" />
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </div>
                            <span v-if="!isCollapsed" class="whitespace-nowrap transition-all duration-300">{{ item.name }}</span>
                        </Link>
                    </nav>
                </div>
            </div>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-white/5 overflow-hidden">
                <div class="bg-slate-800/40 rounded-2xl p-4 flex items-center transition-all duration-300" :class="isCollapsed ? 'justify-center' : 'gap-3'">
                    <div class="h-10 w-10 shrink-0 rounded-full bg-amber-500 flex items-center justify-center text-slate-900 font-bold shadow-lg shadow-amber-500/20">
                        {{ user.name.charAt(0) }}
                    </div>
                    <div v-if="!isCollapsed" class="min-w-0 transition-all duration-300">
                        <p class="text-sm font-bold text-white truncate">{{ user.name }}</p>
                        <p class="text-[10px] text-slate-400 truncate">{{ user.email }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- Header -->
            <header class="h-20 bg-white/80 dark:bg-slate-900/50 backdrop-blur-xl border-b border-slate-200 dark:border-white/5 z-30 sticky top-0">
                <div class="h-full px-6 flex items-center justify-between">
                    
                    <!-- Search or Left Title -->
                    <div class="flex items-center gap-4">
                        <button @click="toggleSidebar" class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-white/5 transition-all active:scale-90">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path v-if="isCollapsed || sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h2 class="text-sm font-medium text-slate-400 uppercase tracking-widest hidden md:block">
                            {{ __('overview', currentLocale) }}
                        </h2>
                    </div>

                    <!-- Right Side -->
                    <div class="flex items-center gap-3">
                        
                        <!-- Language Switcher -->
                        <Dropdown align="right" width="24">
                            <template #trigger>
                                <button class="h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 transition-all">
                                    <span class="uppercase">{{ currentLocale }}</span>
                                    <svg class="h-3 w-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                            </template>
                            <template #content>
                                <a :href="route('language.switch', 'en')" class="block px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-amber-500 hover:text-white transition-colors">English</a>
                                <a :href="route('language.switch', 'fr')" class="block px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-amber-500 hover:text-white transition-colors">Français</a>
                                <a :href="route('language.switch', 'ar')" class="block px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-amber-500 hover:text-white transition-colors">العربية</a>
                            </template>
                        </Dropdown>

                        <!-- User Profile -->
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="h-10 w-10 rounded-xl overflow-hidden border-2 border-slate-100 dark:border-white/10 p-0.5 transition-transform active:scale-95">
                                    <div class="h-full w-full rounded-[9px] bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400 font-bold text-xs uppercase">
                                        {{ user.name.charAt(0) }}
                                    </div>
                                </button>
                            </template>
                            <template #content>
                                <div class="px-4 py-3 border-b border-slate-100 dark:border-white/5">
                                    <p class="text-xs font-bold text-slate-900 dark:text-white">{{ user.name }}</p>
                                    <p class="text-[10px] text-slate-500 truncate">{{ user.email }}</p>
                                </div>
                                <DropdownLink :href="route('profile.edit')" class="text-xs font-semibold">
                                    {{ trans('profile', currentLocale) }}
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button" class="text-xs font-semibold text-red-500">
                                    {{ trans('logout', currentLocale) }}
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <!-- Main Scroll Area -->
            <main class="flex-1 overflow-y-auto px-4 sm:px-6 py-4 sm:py-8">
                <div class="max-w-7xl mx-auto">
                    <!-- Dynamic Page Header -->
                    <header v-if="$slots.header" class="mb-6 sm:mb-10">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                                    <slot name="header" />
                                </h1>
                                <p v-if="$slots['header-subtitle']" class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">
                                    <slot name="header-subtitle" />
                                </p>
                            </div>
                            <div class="flex items-center gap-2 sm:gap-3">
                                <slot name="header-actions" />
                            </div>
                        </div>
                    </header>

                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
