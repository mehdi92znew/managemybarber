<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { trans } from '@/lang';

const props = defineProps({
    payouts: Object,
    totalEarned: Number,
    filters: Object,
});

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'en');
const __ = (key) => trans(key, currentLocale.value);

const filterForm = ref({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

const applyFilters = () => {
    router.get(route('barber.payouts.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const clearFilters = () => {
    filterForm.value = {
        start_date: '',
        end_date: '',
    };
    applyFilters();
};

watch(filterForm, () => {
    applyFilters();
}, { deep: true });

const formatCurrency = (value) => {
    let locale = 'en-US';
    if (currentLocale.value === 'fr' || currentLocale.value === 'ar') locale = 'fr-FR';
    return new Intl.NumberFormat(locale, { style: 'currency', currency: 'USD', currencyDisplay: 'narrowSymbol' }).format(value || 0);
};

const formatShortDate = (date) => {
    if (!date) return '-';
    const d = new Date(date);
    const dateLocale = (currentLocale.value === 'fr' || currentLocale.value === 'ar') ? 'fr-FR' : 'en-US';
    return d.toLocaleDateString(dateLocale, { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head :title="__('payouts')" />

    <AuthenticatedLayout>
        <template #header-title>{{ __('payouts') }}</template>
        <template #header>{{ __('payout_management') }}</template>

        <div class="py-6 space-y-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Summary Stats (Top Bar) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200 dark:border-white/5 premium-shadow flex items-center gap-6">
                    <div class="h-14 w-14 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                         <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('total_earned') }}</p>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white">{{ formatCurrency(totalEarned) }}</h3>
                    </div>
                </div>
                
                <div class="md:col-span-2 bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200 dark:border-white/5 premium-shadow flex flex-col md:flex-row items-center gap-6">
                    <div class="flex-1 w-full grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">{{ __('start_date') }}</label>
                            <input v-model="filterForm.start_date" type="date" class="w-full rounded-2xl border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-sm font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:ring-0 transition-all" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">{{ __('end_date') }}</label>
                            <input v-model="filterForm.end_date" type="date" class="w-full rounded-2xl border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-sm font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:ring-0 transition-all" />
                        </div>
                    </div>
                    <button @click="clearFilters" class="px-8 py-4 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                        {{ __('clear_filters') }}
                    </button>
                </div>
            </div>

            <!-- List -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 rounded-[2.5rem] overflow-hidden premium-shadow">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-white/5">
                    <thead class="bg-slate-50/50 dark:bg-black/20">
                        <tr>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('date') }}</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('note') }}</th>
                            <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        <tr v-for="payout in payouts.data" :key="payout.id" class="group hover:bg-slate-50/80 dark:hover:bg-white/5 transition-all">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ formatShortDate(payout.date) }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-sm font-medium text-slate-500 italic">{{ payout.note || '-' }}</p>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <span class="text-lg font-black text-emerald-500">{{ formatCurrency(payout.amount) }}</span>
                            </td>
                        </tr>
                        <tr v-if="payouts.data.length === 0">
                            <td colspan="3" class="py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="p-6 rounded-3xl bg-slate-50 dark:bg-white/5 text-slate-300 dark:text-slate-700 mb-4">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    </div>
                                    <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('no_payouts_found') }}</h3>
                                    <p class="text-sm text-slate-500 font-medium mt-1">{{ __('try_different_filters') }}</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <Pagination :links="payouts.links" />
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
</style>
