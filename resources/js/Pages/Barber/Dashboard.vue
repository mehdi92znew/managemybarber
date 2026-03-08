<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  LineElement,
  PointElement,
  Filler
} from 'chart.js';
import { Line } from 'vue-chartjs';
import { computed, onMounted, ref, watch } from 'vue';
import { trans } from '@/lang';
import { router } from '@inertiajs/vue3';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, LineElement, PointElement, Filler);

const page = usePage();

const isMobile = ref(false);
const checkMobile = () => { isMobile.value = window.innerWidth < 1024; };
onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

const props = defineProps({
    stats: Object,
    nextAppointment: Object,
    todaySchedule: Array,
    commissionChart: Array,
    filters: Object,
    auth: Object
});

const greeting = computed(() => {
    const hour = new Date().getHours();
    const locale = page.props.locale || 'en';
    if (hour < 12) return trans('good_morning', locale);
    if (hour < 18) return trans('good_afternoon', locale);
    return trans('good_evening', locale);
});

const filterForm = ref({
    start_date: props.filters?.start_date,
    end_date: props.filters?.end_date,
});

const applyFilters = () => {
    router.get(route('barber.dashboard'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

watch(filterForm, () => {
    applyFilters();
}, { deep: true });

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

const chartData = computed(() => {
    const locale = page.props.locale || 'en';
    if (!props.commissionChart || props.commissionChart.length === 0) {
        return { labels: [], datasets: [] };
    }
    const labels = props.commissionChart.map(point => point.day);
    const data = props.commissionChart.map(point => point.total);

    return {
        labels: labels,
        datasets: [{
            label: trans('daily_commission_label', locale),
            data: data,
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            borderColor: '#f59e0b',
            borderWidth: 3,
            pointBackgroundColor: '#f59e0b',
            pointBorderColor: '#fff',
            pointHoverRadius: 6,
            pointRadius: 4,
            tension: 0.4,
            fill: true
        }]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            beginAtZero: true,
            grid: { color: 'rgba(0,0,0,0.05)' },
            ticks: { font: { weight: 'bold' } }
        },
        x: {
            grid: { display: false },
            ticks: { font: { weight: 'bold' } }
        }
    },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#1e293b',
            padding: 12,
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 12 },
            displayColors: false,
            callbacks: {
                label: function(context) {
                    return formatCurrency(context.raw);
                }
            }
        }
    }
};

const formatCurrency = (value) => {
    let locale = 'en-US';
    if (page.props.locale === 'fr' || page.props.locale === 'ar') locale = 'fr-FR';
    return new Intl.NumberFormat(locale, { style: 'currency', currency: 'USD', currencyDisplay: 'narrowSymbol' }).format(value || 0);
};

const formatTime = (date) => {
    if (!date) return '-';
    const d = new Date(date);
    const dateLocale = (page.props.locale === 'fr' || page.props.locale === 'ar') ? 'fr-FR' : 'en-US';
    return d.toLocaleTimeString(dateLocale, { hour: '2-digit', minute: '2-digit' });
};

const getStatusColor = (status) => {
    switch (status) {
        case 'scheduled': return 'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400';
        case 'completed': return 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400';
        case 'cancelled': return 'bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400';
        case 'no_show': return 'bg-slate-100 text-slate-600 dark:bg-slate-500/20 dark:text-slate-400';
        default: return 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400';
    }
};
</script>

<template>
    <Head :title="__('barber_dashboard')" />

    <AuthenticatedLayout>
        <template #header-title>{{ __('dashboard') }}</template>
        <template #header>
            <div class="flex flex-col">
                <span class="text-xs uppercase tracking-widest text-amber-500 font-bold mb-1">{{ __('barber_panel') }}</span>
                <span>{{ greeting }}, {{ $page.props.auth.user.name.split(' ')[0] }}</span>
            </div>
        </template>
        <template #header-subtitle>{{ __('barber_status_desc') }}</template>

        <template #header-actions>
            <Link :href="route('barber.appointments.list')" class="flex items-center gap-2 px-6 py-3 bg-slate-900 dark:bg-amber-500 text-white dark:text-slate-950 text-sm font-bold rounded-2xl hover:scale-105 transition-all duration-200 premium-shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                {{ __('appointments') }}
            </Link>
        </template>

        <!-- Main Dashboard Content -->
        
        <!-- TOP SECTION: Next Appointment & Quick Stats -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-10">
            <!-- Filters Bar (Now at the top) -->
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

            <!-- Mini Daily Summary stats -->
            <div class="flex flex-col gap-6">
                <!-- Today's Commission -->
                <div class="bg-amber-500 rounded-[2.5rem] p-8 shadow-xl shadow-amber-500/20 text-slate-900 group relative overflow-hidden flex-1 flex flex-col justify-center">
                    <div class="absolute top-0 right-0 p-8 transform group-hover:scale-125 transition-transform duration-500 opacity-20">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    </div>
                    <span class="text-xs font-black uppercase tracking-widest opacity-60 mb-2 block">{{ __('today_earnings') }}</span>
                    <h4 class="text-4xl font-black tracking-tighter">{{ formatCurrency(stats.todayCommission) }}</h4>
                </div>
                
                <!-- This Week's Commission -->
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 shadow-lg border border-slate-200 dark:border-white/5 flex-1 flex flex-col justify-center">
                    <span class="text-xs font-black uppercase tracking-widest text-slate-400 mb-2 block">{{ __('week_earnings') }}</span>
                    <h4 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ formatCurrency(stats.weekCommission) }}</h4>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- LEFT COLUMN: Today's Full Schedule Timeline -->
            <div class="lg:col-span-1 glass-card premium-shadow rounded-[3rem] p-8 bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 h-auto">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ __('today_schedule') }}</h3>
                    <span class="px-3 py-1 bg-amber-500/10 text-amber-500 rounded-lg text-[10px] font-black uppercase">{{ todaySchedule.length }} {{ __('total') }}</span>
                </div>

                <div v-if="todaySchedule.length === 0" class="flex flex-col items-center justify-center py-12 text-center border-2 border-dashed border-slate-200 dark:border-white/10 rounded-3xl">
                    <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <p class="text-sm font-bold text-slate-500">{{ __('no_appointments_today') }}</p>
                </div>

                <div v-else class="relative space-y-6 before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 dark:before:via-white/10 before:to-transparent">
                    <div v-for="appt in todaySchedule" :key="appt.id" class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                        <!-- Icon -->
                        <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white dark:border-slate-900 bg-slate-100 dark:bg-slate-800 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow-sm relative z-10 transition-transform hover:scale-110" :class="appt.status === 'completed' ? 'text-emerald-500' : 'text-slate-400'">
                            <svg v-if="appt.status === 'completed'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        
                        <!-- Card -->
                        <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-2xl border border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/5 shadow-sm group-hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-black text-sm text-slate-900 dark:text-white">{{ formatTime(appt.start_time) }}</span>
                                <span :class="['px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest', getStatusColor(appt.status)]">{{ __(appt.status) }}</span>
                            </div>
                            <div class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">{{ appt.customer?.name || __('guest') }}</div>
                            <div class="text-xs text-slate-500 font-medium truncate">
                                {{ appt.services?.map(s => s.name).join(', ') || __('no_services') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Filters, Chart & Performance Summary -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Summary Cards for the filtered period -->
                <div class="grid grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-slate-900 rounded-[2rem] p-6 sm:p-8 border border-slate-200 dark:border-white/5 flex items-center gap-6 premium-shadow">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 block mb-1">{{ __('period_earnings') }}</span>
                            <h4 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ formatCurrency(stats.filteredCommission) }}</h4>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 rounded-[2rem] p-6 sm:p-8 border border-slate-200 dark:border-white/5 flex items-center gap-6 premium-shadow">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 block mb-1">{{ __('completed_jobs') }}</span>
                            <h4 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ stats.completedCount }}</h4>
                        </div>
                    </div>
                </div>

                <!-- Chart -->
                <div class="glass-card premium-shadow rounded-[3rem] p-8 bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ __('commission_trend') }}</h3>
                    </div>
                    <div class="h-[300px]">
                        <Line v-if="commissionChart && commissionChart.length > 0" :data="chartData" :options="chartOptions" />
                        <div v-else class="h-full flex items-center justify-center text-slate-400 font-bold italic text-sm">
                            {{ __('no_commission_data') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
