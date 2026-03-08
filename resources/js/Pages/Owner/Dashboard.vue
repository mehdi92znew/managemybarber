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
    snapshot: Object,
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
    
    const isMonthStart = () => {
        const start = new Date();
        start.setDate(1);
        return filterForm.value.start_date === start.toISOString().split('T')[0] && filterForm.value.end_date === today;
    };
    
    const isWeekStart = () => {
        const start = new Date();
        start.setDate(start.getDate() - 7);
        return filterForm.value.start_date === start.toISOString().split('T')[0] && filterForm.value.end_date === today;
    };

    if (range === 'today') {
        return filterForm.value.start_date === today && filterForm.value.end_date === today;
    }
    
    if (range === 'this_week') {
        return isWeekStart();
    }
    
    if (range === 'this_month') {
        return isMonthStart() && !isWeekStart();
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

const greeting = computed(() => {
    const hour = new Date().getHours();
    const locale = currentLocale.value;
    if (hour < 12) return trans('good_morning', locale);
    if (hour < 18) return trans('good_afternoon', locale);
    return trans('good_evening', locale);
});
</script>

<template>
    <Head :title="__('owner_dashboard')" />

    <AuthenticatedLayout>
        <template #header-title>{{ __('dashboard') }}</template>
        <template #header>
            <div class="flex flex-col">
                <span class="text-xs uppercase tracking-widest text-amber-500 font-bold mb-1">{{ __('owner_panel') }}</span>
                <span>{{ greeting }}, {{ $page.props.auth.user.name.split(' ')[0] }}</span>
            </div>
        </template>
        <template #header-subtitle>{{ __('owner_status_desc') }}</template>

        <template #header-actions>
            <Link :href="route('owner.calendar.daily')" class="flex items-center gap-2 px-6 py-3 bg-slate-900 dark:bg-amber-500 text-white dark:text-slate-950 text-sm font-bold rounded-2xl hover:scale-105 transition-all duration-200 premium-shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                {{ __('daily_planning') }}
            </Link>
        </template>
        
        <!-- TOP ROW: Date Filters & Real-Time Snapshots -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-10">
            <!-- Filter Bar taking 2 cols -->
            <div class="xl:col-span-2 flex flex-col lg:flex-row justify-center lg:justify-between items-start lg:items-center gap-6 bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-[2.5rem] border border-slate-200 dark:border-white/5 premium-shadow h-full">
                <div class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto">
                    <div class="relative w-full sm:w-40 xl:w-48">
                        <label class="absolute -top-2 left-4 px-1 bg-white dark:bg-slate-900 text-[9px] font-black uppercase tracking-widest text-slate-400 z-10">{{ __('start_date') }}</label>
                        <input v-model="filterForm.start_date" type="date" class="w-full h-14 rounded-xl border border-slate-200 dark:border-white/10 bg-transparent text-xs font-black text-slate-900 dark:text-white focus:border-amber-500 focus:ring-0 transition-all outline-none px-4" />
                    </div>
                    <div class="relative w-full sm:w-40 xl:w-48">
                        <label class="absolute -top-2 left-4 px-1 bg-white dark:bg-slate-900 text-[9px] font-black uppercase tracking-widest text-slate-400 z-10">{{ __('end_date') }}</label>
                        <input v-model="filterForm.end_date" type="date" class="w-full h-14 rounded-xl border border-slate-200 dark:border-white/10 bg-transparent text-xs font-black text-slate-900 dark:text-white focus:border-amber-500 focus:ring-0 transition-all outline-none px-4" />
                    </div>
                </div>
                
                <div class="flex flex-wrap justify-center items-center gap-2">
                    <button @click="setQuickRange('today')" class="px-4 py-3 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all" :class="isRangeActive('today') ? 'bg-amber-500 text-white' : 'bg-slate-100 dark:bg-white/5 text-slate-500 hover:text-slate-900 dark:hover:text-white'">{{ __('today') }}</button>
                    <button @click="setQuickRange('this_week')" class="px-4 py-3 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all" :class="isRangeActive('this_week') ? 'bg-amber-500 text-white' : 'bg-slate-100 dark:bg-white/5 text-slate-500 hover:text-slate-900 dark:hover:text-white'">{{ __('this_week') }}</button>
                    <button @click="setQuickRange('this_month')" class="px-4 py-3 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all" :class="isRangeActive('this_month') ? 'bg-amber-500 text-white' : 'bg-slate-100 dark:bg-white/5 text-slate-500 hover:text-slate-900 dark:hover:text-white'">{{ __('this_month') }}</button>
                </div>
            </div>
            
            <!-- Snapshot widgets -->
            <div class="flex flex-col gap-6">
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-[2.5rem] p-8 shadow-xl shadow-amber-500/20 text-white group relative overflow-hidden flex-1 flex flex-col justify-center">
                    <div class="absolute -right-8 -top-8 p-8 transform group-hover:scale-125 transition-transform duration-500 opacity-20">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-2 block">{{ __('today_revenue') }}</span>
                    <div class="flex items-baseline gap-2">
                        <h4 class="text-3xl font-black tracking-tighter">{{ formatCurrency(snapshot.todayRevenue) }}</h4>
                        <span class="text-xs font-bold opacity-80 backdrop-blur-md px-2 py-0.5 rounded bg-white/20">{{ snapshot.todayAppointments }} {{ __('appointments') }}</span>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 shadow-lg border border-slate-200 dark:border-white/5 flex-1 flex flex-col justify-center relative overflow-hidden group">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 block">{{ __('week_revenue') }}</span>
                    <h4 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ formatCurrency(snapshot.weekRevenue) }}</h4>
                </div>
            </div>
        </div>

        <!-- MAIN OVERVIEW SECTION (Filtered) -->
        <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight mb-6 px-2">{{ __('period_overview') }}</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">
            <!-- Net Profit (Hero Card) -->
            <div class="relative bg-slate-900 rounded-[2.5rem] p-8 premium-shadow overflow-hidden group hover:-translate-y-2 transition-all duration-300 xl:col-span-2">
                <div class="absolute -top-[50%] -right-[10%] w-[60%] h-[200%] bg-gradient-to-l from-emerald-500/20 to-transparent blur-3xl transform rotate-12 group-hover:rotate-45 transition-transform duration-1000"></div>
                <div class="relative z-10 flex flex-col justify-between h-full">
                    <div class="flex items-start justify-between mb-8">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-400/80 mb-2 block">{{ __('net_profit') }}</span>
                            <h4 class="text-4xl sm:text-5xl font-black text-white tracking-tighter">{{ formatCurrency(stats.net_profit) }}</h4>
                        </div>
                        <div class="p-4 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div :class="['flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-black shadow-inner border', getPercentageChange(stats.net_profit, prevStats.net_profit) >= 0 ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border-rose-500/30']">
                            {{ getPercentageChange(stats.net_profit, prevStats.net_profit) >= 0 ? '↑' : '↓' }} {{ Math.abs(getPercentageChange(stats.net_profit, prevStats.net_profit)).toFixed(1) }}% {{ __('vs_previous') }}
                        </div>
                        <p class="text-[10px] text-white/40 font-medium">{{ __('after_expenses') }}</p>
                    </div>
                </div>
            </div>

            <!-- Gross Revenue -->
            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 border border-slate-200 dark:border-white/5 premium-shadow group hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 block">{{ __('gross_revenue') }}</span>
                        <h4 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ formatCurrency(stats.gross_revenue) }}</h4>
                    </div>
                    <div class="h-10 w-10 flex-shrink-0 bg-slate-100 dark:bg-white/5 rounded-xl flex items-center justify-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
                <div :class="['inline-flex items-center gap-1 w-fit px-2 py-1 rounded-lg text-[10px] font-black', getPercentageChange(stats.gross_revenue, prevStats.gross_revenue) >= 0 ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500']">
                    {{ getPercentageChange(stats.gross_revenue, prevStats.gross_revenue) >= 0 ? '↑' : '↓' }} {{ Math.abs(getPercentageChange(stats.gross_revenue, prevStats.gross_revenue)).toFixed(1) }}%
                </div>
            </div>

            <!-- Commission Liability -->
            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 border border-slate-200 dark:border-white/5 premium-shadow group hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-rose-400 mb-2 block">{{ __('commission_liability') }}</span>
                        <h4 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ formatCurrency(stats.commission_liability) }}</h4>
                    </div>
                    <div class="h-10 w-10 flex-shrink-0 bg-rose-500/10 rounded-xl flex items-center justify-center text-rose-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" /></svg>
                    </div>
                </div>
                <div :class="['inline-flex items-center gap-1 w-fit px-2 py-1 rounded-lg text-[10px] font-black', getPercentageChange(stats.commission_liability, prevStats.commission_liability) >= 0 ? 'bg-rose-500/10 text-rose-500' : 'bg-emerald-500/10 text-emerald-500']">
                    {{ getPercentageChange(stats.commission_liability, prevStats.commission_liability) >= 0 ? '↑' : '↓' }} {{ Math.abs(getPercentageChange(stats.commission_liability, prevStats.commission_liability)).toFixed(1) }}%
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-10">
            <!-- Revenue Trend Chart -->
            <div class="xl:col-span-2 bg-white dark:bg-slate-900 p-8 rounded-[3rem] border border-slate-200 dark:border-white/5 premium-shadow">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('revenue_trend') }}</h3>
                    </div>
                    <div class="flex items-center gap-2">
                            <div class="h-2 w-2 rounded-full bg-amber-500"></div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('revenue') }}</span>
                    </div>
                </div>
                <div class="h-[350px]">
                    <Bar :data="trendData" :options="chartOptions" />
                </div>
            </div>

            <!-- Operations Stats -->
            <div class="flex flex-col gap-6">
                <!-- Serviced Breakdown -->
                <div class="flex-1 bg-white dark:bg-slate-900 p-8 rounded-[3rem] border border-slate-200 dark:border-white/5 premium-shadow flex flex-col justify-center">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight mb-6">{{ __('top_services') }}</h3>
                    <div class="h-[180px] w-full relative mb-4">
                        <Doughnut v-if="charts.serviceBreakdown.length > 0" :data="serviceData" :options="doughnutOptions" />
                        <div v-else class="absolute inset-0 flex items-center justify-center text-slate-400 text-xs font-bold">{{ __('no_services') }}</div>
                    </div>
                </div>

                <!-- Appointments Mini -->
                <div class="bg-indigo-500 text-white p-8 rounded-[3rem] shadow-xl shadow-indigo-500/20 relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 text-white/20 transform group-hover:scale-110 transition-transform">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <h5 class="text-4xl font-black mb-1 relative z-10">{{ stats.appointments_count }}</h5>
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-80 relative z-10">{{ __('appointments') }}</p>
                </div>
            </div>
        </div>

        <!-- BOTTOM AREA: Heat & Activity -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 pb-10">
            <!-- Top Barbers Leaderboard -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-[3rem] border border-slate-200 dark:border-white/5 premium-shadow">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('top_performers') }}</h3>
                </div>
                <div class="space-y-4">
                    <div v-if="topBarbers.length === 0" class="text-center py-6 text-slate-400 font-bold text-sm">
                        {{ __('no_performance_data') }}
                    </div>
                    <div v-for="(barber, index) in topBarbers" :key="barber.id" class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 group hover:border-amber-500/30 transition-colors">
                        <div class="flex items-center gap-4">
                            <!-- Rank Medal -->
                            <div :class="[
                                'h-10 w-10 shrink-0 rounded-full flex items-center justify-center font-black text-sm shadow-inner border',
                                index === 0 ? 'bg-amber-100 text-amber-600 border-amber-200 dark:bg-amber-500/20 dark:text-amber-400 dark:border-amber-500/30' : 
                                index === 1 ? 'bg-slate-200 text-slate-600 border-slate-300 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600' :
                                index === 2 ? 'bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800' :
                                'bg-slate-100 text-slate-400 border-slate-200 dark:bg-slate-800 dark:border-slate-700'
                            ]">
                                #{{ index + 1 }}
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-900 dark:text-white">{{ barber.name }}</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('barber') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-black text-slate-900 dark:text-white block">{{ formatCurrency(barber.revenue) }}</span>
                            <span class="text-[9px] font-bold text-slate-500 block uppercase">{{ __('generated') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity List -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-[3rem] border border-slate-200 dark:border-white/5 premium-shadow flex flex-col">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('recent_activity') }}</h3>
                    <Link :href="route('owner.appointments.list')" class="text-[10px] font-black uppercase tracking-widest text-amber-500 hover:text-amber-600">{{ __('view_all') }}</Link>
                </div>
                
                <div class="flex-1 space-y-5">
                    <div v-if="recentAppointments.length === 0" class="flex flex-col items-center justify-center h-full text-center py-6">
                        <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <p class="text-sm font-bold text-slate-500">{{ __('no_appointments') }}</p>
                    </div>

                    <div v-for="appt in recentAppointments" :key="appt.id" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 group">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-xl bg-slate-200 dark:bg-slate-800 text-[10px] font-black text-slate-500 flex items-center justify-center shrink-0">
                                {{ appt.barber?.name.charAt(0) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm font-black text-slate-900 dark:text-white">{{ appt.customer?.name || __('walk_in') }}</span>
                                    <span :class="['px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest', appt.status === 'completed' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-amber-500/10 text-amber-500']">
                                        {{ __(appt.status) }}
                                    </span>
                                </div>
                                <div class="text-[10px] font-bold text-slate-500">{{ __('with') }} {{ appt.barber?.name }}</div>
                            </div>
                        </div>
                        <div class="text-left sm:text-right pl-14 sm:pl-0">
                            <span class="text-sm font-black text-slate-900 dark:text-white block">{{ formatCurrency(appt.total_price) }}</span>
                            <span class="text-[9px] font-bold text-slate-400 uppercase">{{ appt.services_count }} services</span>
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
