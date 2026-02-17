<script setup>
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { trans } from '@/lang';

const props = defineProps({
    barbers: Array,
    reportData: Object,
    filters: Object,
});

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'en');
const __ = (key) => trans(key, currentLocale.value);

const form = ref({
    barber_id: props.filters?.barber_id || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

const generateReport = () => {
    router.get(route('owner.barber-report'), form.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

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
    <Head :title="__('barber_report')" />

    <AuthenticatedLayout>
        <template #header-title>{{ __('reports') }}</template>
        <template #header>{{ __('barber_report') }}</template>

        <div class="py-6 space-y-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-[2.5rem] border border-slate-200 dark:border-white/5 premium-shadow">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">{{ __('barber') }}</label>
                        <select v-model="form.barber_id" class="w-full rounded-2xl border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-sm font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:ring-amber-500 transition-all">
                            <option value="" disabled>{{ __('select_barber') }}</option>
                            <option v-for="b in barbers" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">{{ __('start_date') }}</label>
                        <input v-model="form.start_date" type="date" class="w-full rounded-2xl border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-sm font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:ring-amber-500 transition-all" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">{{ __('end_date') }}</label>
                        <input v-model="form.end_date" type="date" class="w-full rounded-2xl border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-sm font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:ring-amber-500 transition-all" />
                    </div>
                    <PrimaryButton @click="generateReport" class="w-full justify-center py-4">
                        {{ __('generate_report') }}
                    </PrimaryButton>
                </div>
            </div>

            <template v-if="reportData">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Services -->
                    <div class="p-6 rounded-[2rem] bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 premium-shadow">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('total_services_value') }}</p>
                        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ formatCurrency(reportData.totals.services) }}</p>
                    </div>

                    <!-- Total Commission -->
                    <div class="p-6 rounded-[2rem] bg-amber-500 text-slate-900 premium-shadow">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-900/50 mb-2">{{ __('total_commission_earned') }}</p>
                        <p class="text-2xl font-black">{{ formatCurrency(reportData.totals.commission) }}</p>
                    </div>

                    <!-- Total Payouts -->
                    <div class="p-6 rounded-[2rem] bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 premium-shadow">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('total_payouts_received') }}</p>
                        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ formatCurrency(reportData.totals.payouts) }}</p>
                    </div>

                    <!-- Outstanding Balance -->
                    <div class="p-6 rounded-[2rem] bg-slate-900 dark:bg-white border border-transparent dark:border-white/5 premium-shadow">
                        <p class="text-[10px] font-black uppercase tracking-widest text-white/50 dark:text-slate-400 mb-2">{{ __('outstanding_balance') }}</p>
                        <p class="text-2xl font-black text-white dark:text-slate-900" :class="reportData.totals.balance > 0 ? 'text-emerald-500' : ''">
                            {{ formatCurrency(reportData.totals.balance) }}
                        </p>
                    </div>
                </div>

                <!-- Detailed Tables -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Appointments List -->
                    <div class="space-y-4">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 px-4">{{ __('appointments_list') }}</h3>
                        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-white/5 overflow-hidden premium-shadow">
                            <table class="min-w-full divide-y divide-slate-100 dark:divide-white/5">
                                <thead class="bg-slate-50/50 dark:bg-black/20">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('date') }}</th>
                                        <th class="px-6 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('customer') }}</th>
                                        <th class="px-6 py-4 text-right text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('commission_value') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                    <tr v-for="appt in reportData.appointments" :key="appt.id">
                                        <td class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-400">{{ formatShortDate(appt.start_time) }}</td>
                                        <td class="px-6 py-4 text-xs font-bold text-slate-900 dark:text-white">{{ appt.customer?.name || 'Walk-in' }}</td>
                                        <td class="px-6 py-4 text-right text-xs font-black text-slate-900 dark:text-white">{{ formatCurrency(appt.commission_amount) }}</td>
                                    </tr>
                                    <tr v-if="reportData.appointments.length === 0">
                                        <td colspan="3" class="px-6 py-10 text-center text-sm text-slate-500 italic">{{ __('no_appointments_found') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payouts List -->
                    <div class="space-y-4">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 px-4">{{ __('payouts_list') }}</h3>
                        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-white/5 overflow-hidden premium-shadow">
                            <table class="min-w-full divide-y divide-slate-100 dark:divide-white/5">
                                <thead class="bg-slate-50/50 dark:bg-black/20">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('date') }}</th>
                                        <th class="px-6 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('note') }}</th>
                                        <th class="px-6 py-4 text-right text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                    <tr v-for="payout in reportData.payouts" :key="payout.id">
                                        <td class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-400">{{ formatShortDate(payout.date) }}</td>
                                        <td class="px-6 py-4 text-xs font-medium text-slate-500 italic truncate max-w-[150px]">{{ payout.note || '-' }}</td>
                                        <td class="px-6 py-4 text-right text-xs font-black text-red-500">{{ formatCurrency(payout.amount) }}</td>
                                    </tr>
                                    <tr v-if="reportData.payouts.length === 0">
                                        <td colspan="3" class="px-6 py-10 text-center text-sm text-slate-500 italic">{{ __('no_payouts_found') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center py-24 bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200 dark:border-white/5 premium-shadow">
                <div class="p-8 rounded-[2.5rem] bg-slate-50 dark:bg-white/5 text-slate-300 dark:text-slate-700 mb-8">
                    <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('generate_report') }}</h3>
                <p class="text-sm text-slate-500 mt-2 font-medium">{{ __('select_barber_dates_desc') }}</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
