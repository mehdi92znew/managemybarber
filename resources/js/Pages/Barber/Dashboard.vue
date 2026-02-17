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
import { computed, onMounted, ref } from 'vue';
import { trans } from '@/lang';

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
    commissionChart: Array,
    appointments: Array,
    auth: Object
});

const greeting = computed(() => {
    const hour = new Date().getHours();
    const locale = page.props.locale || 'en';
    if (hour < 12) return trans('good_morning', locale);
    if (hour < 18) return trans('good_afternoon', locale);
    return trans('good_evening', locale);
});

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

const formatDate = (date) => {
    if (!date) return '-';
    const d = new Date(date);
    const dateLocale = (page.props.locale === 'fr' || page.props.locale === 'ar') ? 'fr-FR' : 'en-US';
    return d.toLocaleDateString(dateLocale, { weekday: 'short', month: 'short', day: 'numeric' });
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
            <Link :href="route('barber.calendar')" class="flex items-center gap-2 px-6 py-3 bg-slate-900 dark:bg-amber-500 text-white dark:text-slate-950 text-sm font-bold rounded-2xl hover:scale-105 transition-all duration-200 premium-shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                {{ __('open_calendar') }}
            </Link>
        </template>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <!-- Appointments Today -->
            <div class="glass-card premium-shadow rounded-3xl p-6 group hover:translate-y-[-4px] transition-all duration-300 bg-white dark:bg-slate-900">
                <div class="flex items-center justify-between mb-6">
                     <div class="p-3 rounded-2xl bg-amber-500/10 text-amber-500 group-hover:bg-amber-500 group-hover:text-white transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('today') }}</span>
                </div>
                <h4 class="text-3xl font-black text-slate-900 dark:text-white">{{ stats.todayAppointments }}</h4>
                <p class="text-xs font-bold text-slate-500 mt-1">{{ __('appointments_today') }}</p>
            </div>

            <!-- Upcoming count -->
            <div class="glass-card premium-shadow rounded-3xl p-6 group hover:translate-y-[-4px] transition-all duration-300 bg-white dark:bg-slate-900">
                <div class="flex items-center justify-between mb-6">
                     <div class="p-3 rounded-2xl bg-indigo-500/10 text-indigo-500 group-hover:bg-indigo-500 group-hover:text-white transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('pipeline') }}</span>
                </div>
                <h4 class="text-3xl font-black text-slate-900 dark:text-white">{{ stats.upcomingCount }}</h4>
                <p class="text-xs font-bold text-slate-500 mt-1">{{ __('upcoming_appointments') }}</p>
            </div>

            <!-- Month Commission -->
            <div class="glass-card premium-shadow rounded-3xl p-6 group hover:translate-y-[-4px] transition-all duration-300 bg-white dark:bg-slate-900">
                <div class="flex items-center justify-between mb-6">
                     <div class="p-3 rounded-2xl bg-emerald-500/10 text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('this_month') }}</span>
                </div>
                <h4 class="text-3xl font-black text-slate-900 dark:text-white">{{ formatCurrency(stats.monthCommission) }}</h4>
                <p class="text-xs font-bold text-slate-500 mt-1">{{ __('commission_this_month') }}</p>
            </div>

            <!-- Total Commission -->
            <div class="premium-shadow rounded-3xl p-6 group hover:translate-y-[-4px] transition-all duration-300 shadow-lg bg-slate-900 dark:bg-amber-500">
                <div class="flex items-center justify-between mb-6">
                     <div class="p-3 rounded-2xl bg-white/10 text-white dark:text-slate-900 font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-white/40 dark:text-slate-700">{{ __('total_basis') }}</span>
                </div>
                <h4 class="text-3xl font-black text-white dark:text-slate-900">{{ formatCurrency(stats.totalCommission) }}</h4>
                <p class="text-xs font-bold text-white/60 dark:text-slate-600 mt-1">{{ __('total_earned') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Commission Chart -->
            <div class="lg:col-span-2 glass-card premium-shadow rounded-3xl p-8 bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ __('commission_trend') }}</h3>
                </div>
                <div class="h-[350px]">
                    <Line v-if="commissionChart && commissionChart.length > 0" :data="chartData" :options="chartOptions" />
                    <div v-else class="h-full flex items-center justify-center text-slate-400 font-bold italic">
                        {{ __('no_commission_data') }}
                    </div>
                </div>
            </div>

            <!-- Upcoming Appointments -->
            <div class="glass-card premium-shadow rounded-3xl p-8 bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ __('next_appointments') }}</h3>
                    <Link :href="route('barber.calendar')" class="text-[10px] font-black uppercase tracking-widest text-amber-500 hover:text-amber-600">{{ __('view_all') }}</Link>
                </div>
                
                <div v-if="appointments.length === 0" class="h-[200px] flex items-center justify-center text-slate-400 font-bold italic text-center text-sm">
                    {{ __('no_upcoming_scheduled') }}
                </div>
                
                <div v-else class="space-y-6">
                    <div v-for="appt in appointments" :key="appt.id" class="relative pl-6 border-l-2 border-slate-100 dark:border-white/5 group">
                        <!-- Dot -->
                        <div class="absolute -left-[5px] top-0 h-2 w-2 rounded-full bg-amber-500 group-hover:scale-150 transition-transform"></div>
                        
                        <div class="flex justify-between items-start mb-1">
                            <div>
                                <p class="text-sm font-black text-slate-900 dark:text-white">{{ appt.customer.name }}</p>
                                <p class="text-xs font-bold text-slate-500">
                                    {{ formatTime(appt.start_time) }}
                                </p>
                            </div>
                            <span :class="[
                                'px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest',
                                appt.status === 'scheduled' ? 'bg-amber-500/10 text-amber-500' : 'bg-slate-100 text-slate-500'
                            ]">
                                {{ __(appt.status) }}
                            </span>
                        </div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">
                            {{ formatDate(appt.start_time) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
