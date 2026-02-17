<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import { Bar, Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js';
import { trans } from '@/lang';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'en');
const __ = (key) => trans(key, currentLocale.value);

const props = defineProps({
    stats: Object,
    prevStats: Object,
    charts: Object,
    topBarbers: Array,
    recentAppointments: Array,
    filters: Object,
});

const filterForm = ref({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
});

const applyFilters = () => {
    router.get(route('owner.dashboard'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

watch(filterForm, () => {
    applyFilters();
}, { deep: true });

const getPercentageChange = (current, prev) => {
    if (!prev || prev === 0) return current > 0 ? 100 : 0;
    return ((current - prev) / prev) * 100;
};

const formatCurrency = (value) => {
    let locale = 'en-US';
    if (currentLocale.value === 'fr' || currentLocale.value === 'ar') locale = 'fr-FR';
    return new Intl.NumberFormat(locale, { style: 'currency', currency: 'USD', currencyDisplay: 'narrowSymbol' }).format(value || 0);
};

// Charts Config
const trendData = computed(() => ({
    labels: props.charts.revenueTrend.map(item => item.label),
    datasets: [{
        label: __('revenue'),
        data: props.charts.revenueTrend.map(item => item.total),
        backgroundColor: '#f59e0b',
        borderRadius: 8,
    }]
}));

const serviceData = computed(() => ({
    labels: props.charts.serviceBreakdown.map(item => item.name),
    datasets: [{
        data: props.charts.serviceBreakdown.map(item => item.revenue),
        backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#f43f5e', '#8b5cf6'],
        borderWidth: 0,
    }]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#0f172a',
            padding: 12,
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 12 },
            callbacks: {
                label: function(context) {
                    return formatCurrency(context.raw);
                }
            }
        }
    },
    scales: {
        y: { display: false },
        x: { grid: { display: false }, ticks: { font: { weight: 'bold' } } }
    }
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 10, font: { weight: 'bold', size: 10 } } }
    },
    cutout: '70%'
};

const isRangeActive = (range) => {
    const today = new Date().toISOString().split('T')[0];
    if (range === 'today') return filterForm.value.start_date === today && filterForm.value.end_date === today;
    if (range === 'this_week') {
        const start = new Date();
        start.setDate(start.getDate() - 7);
        return filterForm.value.start_date === start.toISOString().split('T')[0];
    }
    return false;
};

const setQuickRange = (range) => {
    const end = new Date();
    let start = new Date();
    if (range === 'today') {
        // already set to now
    } else if (range === 'this_week') {
        start.setDate(end.getDate() - 7);
    } else if (range === 'this_month') {
        start.setDate(1);
    }
    
    filterForm.value.start_date = start.toISOString().split('T')[0];
    filterForm.value.end_date = end.toISOString().split('T')[0];
};
</script>

<template>
    <Head :title="__('owner_dashboard')" />

    <AuthenticatedLayout>
        <template #header-title>{{ __('overview') }}</template>
        
        <div class="space-y-8 max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Filter Header -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 bg-white dark:bg-slate-900 p-8 rounded-[3rem] border border-slate-200 dark:border-white/5 premium-shadow">
                <div class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto">
                    <div class="relative w-full sm:w-48">
                        <label class="absolute -top-2 left-4 px-1 bg-white dark:bg-slate-900 text-[10px] font-black uppercase tracking-widest text-slate-400 z-10">{{ __('start_date') }}</label>
                        <input v-model="filterForm.start_date" type="date" class="w-full h-14 rounded-2xl border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-sm font-black text-slate-900 dark:text-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all outline-none px-4" />
                    </div>
                    <div class="relative w-full sm:w-48">
                        <label class="absolute -top-2 left-4 px-1 bg-white dark:bg-slate-900 text-[10px] font-black uppercase tracking-widest text-slate-400 z-10">{{ __('end_date') }}</label>
                        <input v-model="filterForm.end_date" type="date" class="w-full h-14 rounded-2xl border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-sm font-black text-slate-900 dark:text-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all outline-none px-4" />
                    </div>
                </div>
                <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-white/5 rounded-2xl">
                    <button @click="setQuickRange('today')" class="px-5 py-2.5 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all" :class="isRangeActive('today') ? 'bg-white dark:bg-white/10 text-amber-500 shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'">{{ __('today') }}</button>
                    <button @click="setQuickRange('this_week')" class="px-5 py-2.5 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all" :class="isRangeActive('this_week') ? 'bg-white dark:bg-white/10 text-amber-500 shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'">{{ __('this_week') }}</button>
                    <button @click="setQuickRange('this_month')" class="px-5 py-2.5 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all" :class="filterForm.start_date.endsWith('-01') ? 'bg-white dark:bg-white/10 text-amber-500 shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'">{{ __('this_month') }}</button>
                </div>
            </div>

            <!-- Primary Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Gross Revenue -->
                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200 dark:border-white/5 premium-shadow group hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-4 rounded-2xl bg-amber-500/10 text-amber-500 group-hover:bg-amber-500 group-hover:text-white transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div :class="['flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-black', getPercentageChange(stats.gross_revenue, prevStats.gross_revenue) >= 0 ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500']">
                            {{ getPercentageChange(stats.gross_revenue, prevStats.gross_revenue) >= 0 ? '↑' : '↓' }} {{ Math.abs(getPercentageChange(stats.gross_revenue, prevStats.gross_revenue)).toFixed(1) }}%
                        </div>
                    </div>
                    <h4 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ formatCurrency(stats.gross_revenue) }}</h4>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-2">{{ __('gross_revenue') }}</p>
                </div>

                <!-- Shop Share -->
                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200 dark:border-white/5 premium-shadow group hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-4 rounded-2xl bg-indigo-500/10 text-indigo-500 group-hover:bg-indigo-500 group-hover:text-white transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </div>
                        <div :class="['flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-black', getPercentageChange(stats.shop_share, prevStats.shop_share) >= 0 ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500']">
                            {{ getPercentageChange(stats.shop_share, prevStats.shop_share) >= 0 ? '↑' : '↓' }} {{ Math.abs(getPercentageChange(stats.shop_share, prevStats.shop_share)).toFixed(1) }}%
                        </div>
                    </div>
                    <h4 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ formatCurrency(stats.shop_share) }}</h4>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-2">{{ __('shop_share') }}</p>
                    <p class="text-[10px] text-slate-400 italic mt-1">{{ __('after_commission') }}</p>
                </div>

                <!-- Net Profit -->
                <div class="bg-slate-900 p-8 rounded-[2.5rem] premium-shadow group hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-4 rounded-2xl bg-emerald-500 text-slate-900 group-hover:scale-110 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div :class="['flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-black', getPercentageChange(stats.net_profit, prevStats.net_profit) >= 0 ? 'bg-emerald-500 text-slate-900' : 'bg-rose-500 text-slate-900']">
                            {{ getPercentageChange(stats.net_profit, prevStats.net_profit) >= 0 ? '↑' : '↓' }} {{ Math.abs(getPercentageChange(stats.net_profit, prevStats.net_profit)).toFixed(1) }}%
                        </div>
                    </div>
                    <h4 class="text-3xl font-black text-white tracking-tight">{{ formatCurrency(stats.net_profit) }}</h4>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-2">{{ __('net_profit') }}</p>
                    <p class="text-[10px] text-slate-500 italic mt-1">{{ __('after_expenses') }}</p>
                </div>

                <!-- Appointments -->
                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200 dark:border-white/5 premium-shadow group hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-4 rounded-2xl bg-amber-500/10 text-amber-500 group-hover:bg-amber-500 group-hover:text-white transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <div :class="['flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-black', getPercentageChange(stats.appointments_count, prevStats.appointments_count) >= 0 ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500']">
                            {{ getPercentageChange(stats.appointments_count, prevStats.appointments_count) >= 0 ? '↑' : '↓' }} {{ Math.abs(getPercentageChange(stats.appointments_count, prevStats.appointments_count)).toFixed(1) }}%
                        </div>
                    </div>
                    <h4 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ stats.appointments_count }}</h4>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-2">{{ __('appointments') }}</p>
                </div>
            </div>

            <!-- Secondary Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Expenses -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-white/5 premium-shadow flex items-center gap-4">
                    <div class="p-4 rounded-2xl bg-rose-500/10 text-rose-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h5 class="text-lg font-black text-slate-900 dark:text-white">{{ formatCurrency(stats.expenses) }}</h5>
                        <p class="text-[9px] font-black uppercase tracking-[0.1em] text-slate-400">{{ __('expenses') }}</p>
                    </div>
                </div>
                <!-- Payouts -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-white/5 premium-shadow flex items-center gap-4">
                    <div class="p-4 rounded-2xl bg-amber-500/10 text-amber-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                    </div>
                    <div>
                        <h5 class="text-lg font-black text-slate-900 dark:text-white">{{ formatCurrency(stats.payouts) }}</h5>
                        <p class="text-[9px] font-black uppercase tracking-[0.1em] text-slate-400">{{ __('payouts') }}</p>
                    </div>
                </div>
                <!-- New Customers -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-white/5 premium-shadow flex items-center gap-4">
                    <div class="p-4 rounded-2xl bg-indigo-500/10 text-indigo-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <div>
                        <h5 class="text-lg font-black text-slate-900 dark:text-white">{{ stats.new_customers }}</h5>
                        <p class="text-[9px] font-black uppercase tracking-[0.1em] text-slate-400">{{ __('new_customers') }}</p>
                    </div>
                </div>
                <!-- Liability -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-white/5 premium-shadow flex items-center gap-4">
                    <div class="p-4 rounded-2xl bg-slate-500/10 text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" /></svg>
                    </div>
                    <div>
                        <h5 class="text-lg font-black text-slate-900 dark:text-white">{{ formatCurrency(stats.commission_liability) }}</h5>
                        <p class="text-[9px] font-black uppercase tracking-[0.1em] text-slate-400">{{ __('commission_liability') }}</p>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Revenue Trend -->
                <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-8 rounded-[3rem] border border-slate-200 dark:border-white/5 premium-shadow">
                    <div class="flex items-center justify-between mb-10">
                        <div>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('revenue_trend') }}</h3>
                            <p class="text-xs text-slate-400 font-bold mt-1">{{ __('filtered_period') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                             <div class="h-2 w-2 rounded-full bg-amber-500"></div>
                             <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('revenue') }}</span>
                        </div>
                    </div>
                    <div class="h-[400px]">
                        <Bar :data="trendData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Service Breakdown -->
                <div class="bg-white dark:bg-slate-900 p-8 rounded-[3rem] border border-slate-200 dark:border-white/5 premium-shadow flex flex-col">
                    <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight mb-8">{{ __('top_services') }}</h3>
                    <div class="flex-1 min-h-[300px] flex items-center justify-center">
                        <Doughnut :data="serviceData" :options="doughnutOptions" />
                    </div>
                    <div class="mt-8 space-y-3">
                        <div v-for="(service, idx) in charts.serviceBreakdown" :key="idx" class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                            <div class="flex items-center gap-3">
                                <div class="h-2 w-2 rounded-full" :style="{ backgroundColor: serviceData.datasets[0].backgroundColor[idx] }"></div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-600 dark:text-slate-400 truncate max-w-[120px]">{{ service.name }}</span>
                            </div>
                            <span class="text-xs font-black text-slate-900 dark:text-white">{{ formatCurrency(service.revenue) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Tables -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-20">
                <!-- Top Barbers -->
                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200 dark:border-white/5 premium-shadow">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight mb-8">{{ __('top_performers') }}</h3>
                    <div class="space-y-6">
                        <div v-for="barber in topBarbers" :key="barber.id" class="flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <div class="h-12 w-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-lg group-hover:bg-amber-500 group-hover:text-white transition-all">
                                    {{ barber.name.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-sm font-black text-slate-900 dark:text-white">{{ barber.name }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('barber') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-black text-slate-900 dark:text-white block">{{ formatCurrency(barber.revenue) }}</span>
                                <span class="text-[10px] font-bold text-emerald-500 block">{{ formatCurrency(barber.revenue * 0.4) }} {{ __('est_share') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-white/5 premium-shadow overflow-hidden">
                    <div class="p-8 flex items-center justify-between border-b border-slate-100 dark:border-white/5">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('recent_activity') }}</h3>
                        <Link :href="route('owner.appointments.list')" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-white/5 text-[10px] font-black uppercase tracking-widest text-amber-500 hover:bg-amber-500 hover:text-white transition-all">{{ __('view_all') }}</Link>
                    </div>
                    
                    <!-- Desktop Table -->
                    <table class="hidden lg:table min-w-full divide-y divide-slate-100 dark:divide-white/5">
                        <thead class="bg-slate-50/50 dark:bg-black/20">
                            <tr>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('customer') }}</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('barber') }}</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('status') }}</th>
                                <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            <tr v-for="appt in recentAppointments" :key="appt.id" class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors group">
                                <td class="px-8 py-5">
                                    <span class="text-sm font-black text-slate-900 dark:text-white block">{{ appt.customer?.name || __('walk_in') }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 block mt-0.5">{{ appt.customer?.phone || '-' }}</span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-2">
                                        <div class="h-6 w-6 rounded-lg bg-slate-100 dark:bg-slate-800 text-[10px] font-black text-slate-500 flex items-center justify-center">{{ appt.barber?.name.charAt(0) }}</div>
                                        <span class="text-sm text-slate-500 font-bold">{{ appt.barber?.name }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <span :class="['px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest border', appt.status === 'completed' ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-amber-500/10 text-amber-500 border-amber-500/20']">
                                        {{ __(appt.status) }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <span class="text-sm font-black text-slate-900 dark:text-white block">{{ formatCurrency(appt.total_price) }}</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ appt.services_count }} services</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Mobile List -->
                    <div class="lg:hidden divide-y divide-slate-100 dark:divide-white/5">
                        <div v-for="appt in recentAppointments" :key="appt.id" class="p-6">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center font-black">
                                        {{ appt.barber?.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900 dark:text-white">{{ appt.customer?.name || __('walk_in') }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ appt.barber?.name }}</p>
                                    </div>
                                </div>
                                <span :class="['px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest border', appt.status === 'completed' ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-amber-500/10 text-amber-500 border-amber-500/20']">
                                    {{ __(appt.status) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center text-right">
                                <div class="text-left">
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">{{ appt.services_count }} services</p>
                                </div>
                                <span class="text-sm font-black text-slate-900 dark:text-white">{{ formatCurrency(appt.total_price) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.premium-shadow {
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
}
:dark .premium-shadow {
    box-shadow: 0 20px 60px -20px rgba(0,0,0,0.4);
}
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(0.5);
    cursor: pointer;
}
</style>

