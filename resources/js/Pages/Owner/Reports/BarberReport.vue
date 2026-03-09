<script setup>
import { ref, computed, nextTick } from 'vue';
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

const formatTime = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
};

// Get service names for an appointment
const getServiceNames = (appt) => {
    if (!appt.services || appt.services.length === 0) return '-';
    return appt.services.map(s => s.name).join(', ');
};

// Selected barber name for display
const selectedBarberName = computed(() => {
    if (props.reportData?.barber_name) return props.reportData.barber_name;
    const barber = props.barbers?.find(b => b.id == form.value.barber_id);
    return barber?.name || '';
});

// Print report function
const printReport = () => {
    const printArea = document.getElementById('report-printable-area');
    if (!printArea) return;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${__('barber_report_title')} - ${selectedBarberName.value}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1e293b; padding: 24px; font-size: 11px; }
                .print-header { text-align: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #f59e0b; }
                .print-header h1 { font-size: 20px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 2px; }
                .print-header p { font-size: 12px; color: #64748b; margin-top: 4px; }
                .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
                .summary-card { border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; text-align: center; }
                .summary-card .label { font-size: 8px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 4px; }
                .summary-card .value { font-size: 16px; font-weight: 900; color: #0f172a; }
                .summary-card.highlight { background: #fef3c7; border-color: #f59e0b; }
                .section-title { font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; color: #94a3b8; margin: 16px 0 8px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
                th { background: #f8fafc; padding: 8px 10px; text-align: left; font-size: 8px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; border-bottom: 2px solid #e2e8f0; }
                td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
                .text-right { text-align: right; }
                .font-bold { font-weight: 700; }
                .font-black { font-weight: 900; }
                .text-red { color: #ef4444; }
                .text-amber { color: #d97706; }
                .text-emerald { color: #10b981; }
                tfoot td { font-weight: 900; background: #f8fafc; border-top: 2px solid #e2e8f0; }
                .print-footer { text-align: center; margin-top: 20px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body>
            <div class="print-header">
                <h1>${__('barber_report_title')}</h1>
                <p><strong>${selectedBarberName.value}</strong> — ${formatShortDate(form.value.start_date)} → ${formatShortDate(form.value.end_date)}</p>
            </div>
            ${printArea.innerHTML}
            <div class="print-footer">
                ${__('barber_report_title')} — ${__('generated')} ${new Date().toLocaleDateString('fr-FR')} ${new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => { printWindow.print(); }, 400);
};

// Download as PDF (using print dialog save-as-PDF)
const downloadPDF = () => {
    printReport();
};
</script>

<template>
    <Head :title="__('barber_report')" />

    <AuthenticatedLayout>
        <template #header-title>{{ __('reports') }}</template>
        <template #header>{{ __('barber_report') }}</template>

        <div class="py-6 space-y-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="report-filter-card bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-[2.5rem] border border-slate-200 dark:border-white/5 premium-shadow">
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
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        {{ __('generate_report') }}
                    </PrimaryButton>
                </div>
            </div>

            <template v-if="reportData">
                <!-- Action Buttons: Print & Download -->
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            <span class="text-sm font-black text-amber-700 dark:text-amber-300">{{ reportData.barber_name }}</span>
                        </div>
                        <div class="flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span class="text-xs font-bold text-slate-500">{{ formatShortDate(filters.start_date) }} → {{ formatShortDate(filters.end_date) }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="printReport" class="report-action-btn group flex items-center gap-2 px-5 py-3 text-sm font-black rounded-2xl bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:border-amber-400 hover:text-amber-600 dark:hover:border-amber-500 dark:hover:text-amber-400 transition-all duration-300 hover:shadow-lg hover:shadow-amber-500/10">
                            <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            {{ __('print_report') }}
                        </button>
                        <button @click="downloadPDF" class="report-action-btn group flex items-center gap-2 px-5 py-3 text-sm font-black rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 text-white hover:from-amber-600 hover:to-orange-600 transition-all duration-300 shadow-lg shadow-amber-500/25 hover:shadow-amber-500/40 hover:scale-[1.02]">
                            <svg class="w-4 h-4 transition-transform group-hover:translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            {{ __('download_pdf') }}
                        </button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                    <!-- Total Appointments -->
                    <div class="summary-card-animated p-5 rounded-[2rem] bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 premium-shadow hover:shadow-xl transition-all duration-300 group">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2.5 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-500 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        </div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('total_appointments') }}</p>
                        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ reportData.total_appointments }}</p>
                    </div>

                    <!-- Total Services Value -->
                    <div class="summary-card-animated p-5 rounded-[2rem] bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 premium-shadow hover:shadow-xl transition-all duration-300 group">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-500 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                        </div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('total_services_value') }}</p>
                        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ formatCurrency(reportData.totals.services) }}</p>
                    </div>

                    <!-- Total Commission -->
                    <div class="summary-card-animated p-5 rounded-[2rem] bg-gradient-to-br from-amber-400 to-amber-500 text-slate-900 premium-shadow hover:shadow-xl hover:shadow-amber-500/30 transition-all duration-300 group">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2.5 rounded-xl bg-white/20 text-slate-900 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                        </div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-900/50 mb-1">{{ __('total_commission_earned') }}</p>
                        <p class="text-2xl font-black">{{ formatCurrency(reportData.totals.commission) }}</p>
                    </div>

                    <!-- Total Payouts -->
                    <div class="summary-card-animated p-5 rounded-[2rem] bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 premium-shadow hover:shadow-xl transition-all duration-300 group">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2.5 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-500 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                            </div>
                        </div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('total_payouts_received') }}</p>
                        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ formatCurrency(reportData.totals.payouts) }}</p>
                    </div>

                    <!-- Outstanding Balance -->
                    <div class="summary-card-animated p-5 rounded-[2rem] bg-slate-900 dark:bg-white border border-transparent premium-shadow hover:shadow-xl transition-all duration-300 group">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2.5 rounded-xl bg-white/10 dark:bg-slate-900/10 text-white dark:text-slate-900 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                            </div>
                        </div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-white/50 dark:text-slate-400 mb-1">{{ __('outstanding_balance') }}</p>
                        <p class="text-2xl font-black" :class="reportData.totals.balance > 0 ? 'text-emerald-400 dark:text-emerald-600' : 'text-white dark:text-slate-900'">
                            {{ formatCurrency(reportData.totals.balance) }}
                        </p>
                    </div>
                </div>

                <!-- Printable Area (hidden from screen, used for print) -->
                <div id="report-printable-area" class="hidden">
                    <div class="summary-grid">
                        <div class="summary-card">
                            <div class="label">{{ __('total_appointments') }}</div>
                            <div class="value">{{ reportData.total_appointments }}</div>
                        </div>
                        <div class="summary-card highlight">
                            <div class="label">{{ __('total_services_value') }}</div>
                            <div class="value">{{ formatCurrency(reportData.totals.services) }}</div>
                        </div>
                        <div class="summary-card highlight">
                            <div class="label">{{ __('total_commission_earned') }}</div>
                            <div class="value">{{ formatCurrency(reportData.totals.commission) }}</div>
                        </div>
                        <div class="summary-card">
                            <div class="label">{{ __('total_payouts_received') }}</div>
                            <div class="value">{{ formatCurrency(reportData.totals.payouts) }}</div>
                        </div>
                    </div>
                    <div class="summary-grid" style="grid-template-columns: 1fr;">
                        <div class="summary-card" :style="reportData.totals.balance > 0 ? 'background: #dcfce7; border-color: #10b981;' : 'background: #fef2f2; border-color: #ef4444;'">
                            <div class="label">{{ __('outstanding_balance') }}</div>
                            <div class="value" :style="reportData.totals.balance > 0 ? 'color: #10b981;' : 'color: #ef4444;'">{{ formatCurrency(reportData.totals.balance) }}</div>
                        </div>
                    </div>

                    <div class="section-title">{{ __('appointments_list') }}</div>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('date') }}</th>
                                <th>{{ __('time') }}</th>
                                <th>{{ __('customer') }}</th>
                                <th>{{ __('service') }}</th>
                                <th class="text-right">{{ __('gross') }}</th>
                                <th class="text-right">{{ __('commission') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(appt, index) in reportData.appointments" :key="appt.id">
                                <td>{{ index + 1 }}</td>
                                <td>{{ formatShortDate(appt.start_time) }}</td>
                                <td>{{ formatTime(appt.start_time) }}</td>
                                <td class="font-bold">{{ appt.customer?.name || 'Walk-in' }}</td>
                                <td>{{ getServiceNames(appt) }}</td>
                                <td class="text-right font-bold">{{ formatCurrency(appt.total_price) }}</td>
                                <td class="text-right font-black text-amber">{{ formatCurrency(appt.commission_amount) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5">{{ __('total') }}</td>
                                <td class="text-right">{{ formatCurrency(reportData.totals.services) }}</td>
                                <td class="text-right text-amber">{{ formatCurrency(reportData.totals.commission) }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="section-title">{{ __('payouts_list') }}</div>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('date') }}</th>
                                <th>{{ __('note') }}</th>
                                <th class="text-right">{{ __('amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(payout, index) in reportData.payouts" :key="payout.id">
                                <td>{{ index + 1 }}</td>
                                <td>{{ formatShortDate(payout.date) }}</td>
                                <td>{{ payout.note || '-' }}</td>
                                <td class="text-right font-bold text-red">{{ formatCurrency(payout.amount) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3">{{ __('total') }}</td>
                                <td class="text-right text-red">{{ formatCurrency(reportData.totals.payouts) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Detailed Tables -->
                <div class="space-y-8">
                    <!-- Appointments List - Full Width -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between px-4">
                            <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                {{ __('appointments_list') }}
                            </h3>
                            <span class="text-[10px] font-black text-slate-400 bg-slate-100 dark:bg-white/5 px-3 py-1.5 rounded-full">
                                {{ reportData.total_appointments }} {{ __('total') }}
                            </span>
                        </div>
                        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-white/5 overflow-hidden premium-shadow">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-100 dark:divide-white/5">
                                    <thead class="bg-slate-50/80 dark:bg-black/20">
                                        <tr>
                                            <th class="px-4 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest w-8">#</th>
                                            <th class="px-4 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('date') }}</th>
                                            <th class="px-4 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('customer') }}</th>
                                            <th class="px-4 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('service') }}</th>
                                            <th class="px-4 py-4 text-right text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('gross') }}</th>
                                            <th class="px-4 py-4 text-right text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('commission') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                        <tr v-for="(appt, index) in reportData.appointments" :key="appt.id" class="table-row-animated hover:bg-amber-50/30 dark:hover:bg-amber-500/5 transition-colors duration-200">
                                            <td class="px-4 py-4 text-xs font-bold text-slate-400">{{ index + 1 }}</td>
                                            <td class="px-4 py-4">
                                                <div class="text-xs font-bold text-slate-900 dark:text-white">{{ formatShortDate(appt.start_time) }}</div>
                                                <div class="text-[10px] text-slate-400 mt-0.5">{{ formatTime(appt.start_time) }}</div>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white text-[10px] font-black flex-shrink-0">
                                                        {{ (appt.customer?.name || 'W')[0].toUpperCase() }}
                                                    </div>
                                                    <span class="text-xs font-bold text-slate-900 dark:text-white">{{ appt.customer?.name || __('walk_in') }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="flex flex-wrap gap-1">
                                                    <span v-for="service in appt.services" :key="service.id" class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300">
                                                        {{ service.name }}
                                                    </span>
                                                    <span v-if="!appt.services || appt.services.length === 0" class="text-xs text-slate-400 italic">-</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-right">
                                                <span class="text-xs font-black text-slate-900 dark:text-white">{{ formatCurrency(appt.total_price) }}</span>
                                            </td>
                                            <td class="px-4 py-4 text-right">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-black bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                                    {{ formatCurrency(appt.commission_amount) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr v-if="reportData.appointments.length === 0">
                                            <td colspan="6" class="px-6 py-14 text-center">
                                                <div class="flex flex-col items-center gap-3">
                                                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5">
                                                        <svg class="w-10 h-10 text-slate-300 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                    </div>
                                                    <p class="text-sm text-slate-500 italic">{{ __('no_appointments_found') }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <!-- Table Footer with Totals -->
                                    <tfoot v-if="reportData.appointments.length > 0" class="bg-slate-50/80 dark:bg-black/20 border-t-2 border-slate-200 dark:border-white/10">
                                        <tr>
                                            <td colspan="4" class="px-4 py-4 text-xs font-black uppercase tracking-widest text-slate-500">{{ __('total') }}</td>
                                            <td class="px-4 py-4 text-right text-sm font-black text-slate-900 dark:text-white">{{ formatCurrency(reportData.totals.services) }}</td>
                                            <td class="px-4 py-4 text-right">
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-sm font-black bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300">
                                                    {{ formatCurrency(reportData.totals.commission) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Payouts List -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between px-4">
                            <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                {{ __('payouts_list') }}
                            </h3>
                            <span class="text-[10px] font-black text-slate-400 bg-slate-100 dark:bg-white/5 px-3 py-1.5 rounded-full">
                                {{ reportData.payouts.length }} {{ __('total') }}
                            </span>
                        </div>
                        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-white/5 overflow-hidden premium-shadow">
                            <table class="min-w-full divide-y divide-slate-100 dark:divide-white/5">
                                <thead class="bg-slate-50/80 dark:bg-black/20">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest w-8">#</th>
                                        <th class="px-6 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('date') }}</th>
                                        <th class="px-6 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('note') }}</th>
                                        <th class="px-6 py-4 text-right text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                    <tr v-for="(payout, index) in reportData.payouts" :key="payout.id" class="table-row-animated hover:bg-red-50/30 dark:hover:bg-red-500/5 transition-colors duration-200">
                                        <td class="px-6 py-4 text-xs font-bold text-slate-400">{{ index + 1 }}</td>
                                        <td class="px-6 py-4">
                                            <div class="text-xs font-bold text-slate-900 dark:text-white">{{ formatShortDate(payout.date) }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-xs font-medium text-slate-500 italic truncate max-w-[200px]">{{ payout.note || '-' }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-black bg-red-50 dark:bg-red-500/10 text-red-500">
                                                -{{ formatCurrency(payout.amount) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="reportData.payouts.length === 0">
                                        <td colspan="4" class="px-6 py-14 text-center">
                                            <div class="flex flex-col items-center gap-3">
                                                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5">
                                                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                                </div>
                                                <p class="text-sm text-slate-500 italic">{{ __('no_payouts_found') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                <!-- Table Footer with Totals -->
                                <tfoot v-if="reportData.payouts.length > 0" class="bg-slate-50/80 dark:bg-black/20 border-t-2 border-slate-200 dark:border-white/10">
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-xs font-black uppercase tracking-widest text-slate-500">{{ __('total') }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-sm font-black bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400">
                                                -{{ formatCurrency(reportData.totals.payouts) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center py-24 bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200 dark:border-white/5 premium-shadow">
                <div class="p-8 rounded-[2.5rem] bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-500/10 dark:to-orange-500/10 mb-8">
                    <svg class="w-20 h-20 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('generate_report') }}</h3>
                <p class="text-sm text-slate-500 mt-2 font-medium max-w-sm text-center">{{ __('select_barber_dates_desc') }}</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.summary-card-animated {
    animation: fadeInUp 0.5s ease-out backwards;
}
.summary-card-animated:nth-child(1) { animation-delay: 0.05s; }
.summary-card-animated:nth-child(2) { animation-delay: 0.1s; }
.summary-card-animated:nth-child(3) { animation-delay: 0.15s; }
.summary-card-animated:nth-child(4) { animation-delay: 0.2s; }
.summary-card-animated:nth-child(5) { animation-delay: 0.25s; }

.table-row-animated {
    animation: fadeInUp 0.3s ease-out backwards;
}

.report-action-btn {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>
