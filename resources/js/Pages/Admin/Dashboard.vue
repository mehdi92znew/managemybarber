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
import { computed } from 'vue';
import { __ } from '@/translate';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, LineElement, PointElement, Filler);

const props = defineProps({
    stats: Object,
    shopsGrowth: Array,
    recentShops: Array,
    recentLogs: Array,
    auth: Object
});

// Translated via the imported __ function

const chartData = computed(() => {
    const labels = props.shopsGrowth.map(point => point.month);
    const data = props.shopsGrowth.map(point => point.total);

    return {
        labels: labels,
        datasets: [{
            label: 'New Shops',
            data: data,
            borderColor: '#F59E0B',
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#F59E0B',
            pointBorderColor: '#fff',
            pointHoverRadius: 6,
        }]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#1E293B',
            padding: 12,
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 13 },
            cornerRadius: 8,
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: { color: 'rgba(148, 163, 184, 0.1)' },
            ticks: { color: '#94a3b8' }
        },
        x: {
            grid: { display: false },
            ticks: { color: '#94a3b8' }
        }
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
};

const getActionColor = (action) => {
    if (action.includes('error') || action.includes('blocked') || action.includes('suspension')) return 'text-red-400';
    if (action.includes('registration') || action.includes('approval') || action.includes('activation')) return 'text-emerald-400';
    return 'text-amber-400';
};
</script>

<template>
    <Head :title="__('admin_dashboard')" />

    <AuthenticatedLayout>
        <template #header-title>{{ __('admin_dashboard') }}</template>
        <template #header>
            <div class="flex flex-col">
                <span class="text-xs uppercase tracking-widest text-amber-500 font-black mb-1">PLATFORM CONTROL</span>
                <span class="italic font-black text-slate-900 dark:text-white">{{ __('admin_dashboard') }}</span>
            </div>
        </template>
        <template #header-subtitle>{{ __('admin_panel_desc') }}</template>

        <template #header-actions>
            <Link :href="route('admin.settings.index')" class="flex items-center gap-2 px-6 py-3 bg-slate-900 dark:bg-amber-500 text-white dark:text-slate-950 text-xs font-black uppercase tracking-widest rounded-2xl hover:scale-105 transition-all duration-300 premium-shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                {{ __('platform_settings') }}
            </Link>
        </template>

        <div class="space-y-10 pb-12">
            <!-- Main Metrics Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <!-- Revenue Card -->
                <div class="bg-slate-900 rounded-[2.5rem] p-8 premium-shadow relative overflow-hidden group hover:-translate-y-2 transition-all duration-500">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-amber-500/10 blur-3xl group-hover:bg-amber-500/20 transition-all duration-700"></div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500/80 mb-3 block">{{ __('platform_revenue') }}</span>
                    <h3 class="text-4xl font-black text-white tracking-tighter">{{ formatCurrency(stats.platformRevenue) }}</h3>
                    <div class="mt-6 flex items-center gap-2">
                        <span class="text-[9px] font-black uppercase tracking-widest px-3 py-1 bg-white/5 text-amber-500 rounded-full border border-white/5 tracking-tighter italic">LIFETIME GROWTH</span>
                    </div>
                </div>

                <!-- Shops Card -->
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 border border-slate-200 dark:border-white/5 premium-shadow hover:-translate-y-2 transition-all duration-500 group">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-3 block">{{ __('active_shops') }}</span>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">{{ stats.activeShops }}</h3>
                        <span class="text-xs font-bold text-slate-500">/ {{ stats.totalShops }}</span>
                    </div>
                    <div class="mt-6 flex items-center gap-1">
                        <div class="w-full bg-slate-100 dark:bg-white/5 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full transition-all duration-1000" :style="{ width: (stats.activeShops / stats.totalShops * 100) + '%' }"></div>
                        </div>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 border border-slate-200 dark:border-white/5 premium-shadow hover:-translate-y-2 transition-all duration-500 group">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-rose-500 mb-3 block">{{ __('pending_approvals') }}</span>
                    <h3 class="text-4xl font-black tracking-tighter" :class="stats.pendingShops > 0 ? 'text-rose-500 animate-pulse' : 'text-slate-900 dark:text-white'">{{ stats.pendingShops }}</h3>
                    <Link v-if="stats.pendingShops > 0" :href="route('admin.shops.index', {status: 'pending'})" class="mt-6 inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-rose-500 hover:underline">
                        VERIFY NOW →
                    </Link>
                    <div v-else class="mt-6 text-[10px] font-black uppercase tracking-widest text-slate-400 italic">SYSTEM CLEAR</div>
                </div>

                <!-- Total Users -->
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 border border-slate-200 dark:border-white/5 premium-shadow hover:-translate-y-2 transition-all duration-500 group">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-500 mb-3 block">TOTAL TALENT</span>
                    <h3 class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">{{ stats.totalBarbers + stats.totalShops }}</h3>
                    <p class="mt-6 text-[10px] font-black uppercase tracking-widest text-slate-400">
                        {{ stats.totalBarbers }} Barbers • {{ stats.totalShops }} Owners
                    </p>
                </div>
            </div>

            <!-- Charts & Activity Split -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-10">
                <!-- Platform Growth Chart -->
                <div class="xl:col-span-2 bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-200 dark:border-white/5 premium-shadow">
                    <div class="flex items-center justify-between mb-10">
                        <div>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight italic">NETWORK EXPANSION</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Monthly signup velocity</p>
                        </div>
                        <Link :href="route('admin.shops.index')" class="px-4 py-2 bg-slate-100 dark:bg-white/5 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-amber-500 transition-colors">
                            RESOURCES →
                        </Link>
                    </div>
                    <div class="h-[400px]">
                        <Line :data="chartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Recent Activity Feed -->
                <div class="bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200 dark:border-white/5 premium-shadow flex flex-col">
                    <div class="p-8 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
                        <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest">LIVE FEED</h3>
                        <div class="flex gap-1">
                            <div class="w-1 h-1 rounded-full bg-amber-500 animate-ping"></div>
                            <div class="w-1 h-1 rounded-full bg-amber-500"></div>
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto max-h-[500px] p-8 space-y-8">
                        <div v-for="log in recentLogs" :key="log.id" class="flex gap-6 group">
                            <div class="relative">
                                <div class="w-1 h-full absolute left-1/2 -translate-x-1/2 bg-slate-100 dark:bg-white/5 rounded-full"></div>
                                <div class="w-3 h-3 rounded-full relative z-10 transition-transform group-hover:scale-150 duration-300" 
                                     :class="getActionColor(log.action).includes('emerald') ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]' : 
                                             getActionColor(log.action).includes('red') ? 'bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.5)]' : 
                                             'bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.5)]'"></div>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-bold text-slate-900 dark:text-slate-100 leading-relaxed italic">
                                    {{ log.description }}
                                </p>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ new Date(log.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
                                    <span v-if="log.shop" class="text-[9px] font-black text-amber-500 uppercase italic">@{{ log.shop.name }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-if="recentLogs.length === 0" class="text-center py-20">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 italic">{{ __('no_logs') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Signups Luxury Table -->
            <div class="bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200 dark:border-white/5 premium-shadow overflow-hidden">
                <div class="px-10 py-8 border-b border-slate-100 dark:border-white/5 flex items-center justify-between bg-slate-50/50 dark:bg-white/[0.02]">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight italic">NEWEST PARTNERS</h3>
                    <Link :href="route('admin.shops.index')" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-amber-500 transition-colors">DIRECTORY ACCESS →</Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 dark:bg-black/20 text-[9px] font-black uppercase tracking-[0.3em] text-slate-500 italic">
                            <tr>
                                <th class="px-10 py-6">Identity</th>
                                <th class="px-10 py-6">Governance</th>
                                <th class="px-10 py-6">Status</th>
                                <th class="px-10 py-6 text-right">Protocol</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            <tr v-for="shop in recentShops" :key="shop.id" class="hover:bg-slate-50 dark:hover:bg-white/5 transition-all duration-300 group">
                                <td class="px-10 py-8">
                                    <div class="font-black text-slate-900 dark:text-white group-hover:text-amber-500 transition-colors uppercase italic tracking-tighter text-lg">{{ shop.name }}</div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ shop.slug }}</div>
                                </td>
                                <td class="px-10 py-8">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-full bg-slate-100 dark:bg-white/5 flex items-center justify-center text-[10px] font-black text-slate-500 border border-slate-200 dark:border-white/10 shrink-0 uppercase italic">
                                            {{ shop.owner?.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase italic">{{ shop.owner?.name || 'N/A' }}</div>
                                            <div class="text-[9px] text-slate-500 truncate max-w-[150px] font-bold">{{ shop.owner?.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-10 py-8">
                                    <span class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest border italic" 
                                          :class="{
                                              'bg-amber-500/10 border-amber-500/20 text-amber-500': shop.subscription_status === 'pending',
                                              'bg-emerald-500/10 border-emerald-500/20 text-emerald-500': shop.subscription_status === 'trial',
                                              'bg-blue-500/10 border-blue-500/20 text-blue-500': shop.subscription_status === 'active',
                                              'bg-rose-500/10 border-rose-500/20 text-rose-500 border-rose-500/30': shop.subscription_status === 'suspended',
                                          }">
                                        {{ shop.subscription_status }}
                                    </span>
                                </td>
                                <td class="px-10 py-8 text-right">
                                    <Link :href="route('admin.shops.show', shop.id)" class="px-5 py-2.5 bg-slate-900 dark:bg-white/5 hover:bg-amber-500 dark:hover:bg-amber-500 text-white hover:text-slate-900 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all duration-300 italic">
                                        MANAGE PROFILE
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.premium-shadow {
    box-shadow: 0 20px 50px -20px rgba(0, 0, 0, 0.1);
}
:dark .premium-shadow {
    box-shadow: 0 30px 60px -30px rgba(0, 0, 0, 0.5);
}
</style>
