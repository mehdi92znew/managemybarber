<script setup>
import { ref, computed } from 'vue';
import { usePage, router, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { trans } from '@/lang';

const props = defineProps({
    drawer: Object,
    stats: Object,
});

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'en');
const __ = (key) => trans(key, currentLocale.value);

const formatCurrency = (value) => {
    let locale = 'en-US';
    if (currentLocale.value === 'fr' || currentLocale.value === 'ar') locale = 'fr-FR';
    return new Intl.NumberFormat(locale, { style: 'currency', currency: 'USD', currencyDisplay: 'narrowSymbol' }).format(value || 0);
};

const startingCash = ref(0);
const closingCash = ref(props.drawer ? (Number(props.drawer.starting_cash) + props.stats.grossRevenue - props.stats.expenses - props.stats.payouts) : 0);
const notes = ref(props.drawer?.notes || '');
const isProcessing = ref(false);

const openDrawer = () => {
    isProcessing.value = true;
    router.post(route('owner.cash-drawer.store'), {
        starting_cash: startingCash.value
    }, {
        preserveScroll: true,
        onFinish: () => isProcessing.value = false
    });
};

const closeDrawer = () => {
    if (confirm(__('confirm_close_drawer'))) {
        isProcessing.value = true;
        router.put(route('owner.cash-drawer.update', props.drawer.id), {
            closing_cash: closingCash.value,
            notes: notes.value
        }, {
            preserveScroll: true,
            onFinish: () => isProcessing.value = false
        });
    }
};
</script>

<template>
    <Head :title="__('cash_drawer')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-black text-2xl uppercase tracking-tight text-slate-900 dark:text-white leading-tight">
                {{ __('cash_drawer') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Not Opened State -->
                <div v-if="!drawer" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 overflow-hidden shadow-xl sm:rounded-3xl p-10 text-center max-w-xl mx-auto">
                    <div class="mx-auto w-20 h-20 bg-amber-500/10 text-amber-500 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('start_of_day') }}</h3>
                    <p class="mt-3 text-slate-500 text-sm font-medium">{{ __('open_drawer_desc') }}</p>
                    
                    <div class="mt-8 text-left">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('starting_cash') }}</label>
                        <input
                            v-model="startingCash"
                            type="number"
                            step="0.01"
                            class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-2xl py-4 px-5 text-xl font-black text-slate-900 dark:text-white focus:ring-amber-500 focus:border-amber-500"
                        />
                    </div>
                    
                    <PrimaryButton @click="openDrawer" :disabled="isProcessing" class="mt-8 w-full justify-center py-4 text-sm">
                        {{ __('open_drawer') }}
                    </PrimaryButton>
                </div>

                <!-- Opened State -->
                <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Stats Grid -->
                    <div class="lg:col-span-2 space-y-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Gross Revenue -->
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 rounded-[2rem] p-8 relative overflow-hidden group">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/5 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center text-amber-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                                    </div>
                                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">{{ __('gross_revenue') }}</h3>
                                </div>
                                <div class="text-4xl font-black text-slate-900 dark:text-white tracking-tight">{{ formatCurrency(stats.grossRevenue) }}</div>
                            </div>

                            <!-- Expenses -->
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 rounded-[2rem] p-8 relative overflow-hidden group">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-red-500/5 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="w-12 h-12 bg-red-500/10 rounded-2xl flex items-center justify-center text-red-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" /></svg>
                                    </div>
                                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">{{ __('expenses') }}</h3>
                                </div>
                                <div class="text-4xl font-black text-slate-900 dark:text-white tracking-tight">{{ formatCurrency(stats.expenses) }}</div>
                            </div>

                            <!-- Payouts -->
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 rounded-[2rem] p-8 relative overflow-hidden group">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="w-12 h-12 bg-indigo-500/10 rounded-2xl flex items-center justify-center text-indigo-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    </div>
                                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">{{ __('payouts') }}</h3>
                                </div>
                                <div class="text-4xl font-black text-slate-900 dark:text-white tracking-tight">{{ formatCurrency(stats.payouts) }}</div>
                            </div>

                            <!-- Starting Cash -->
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 rounded-[2rem] p-8 relative overflow-hidden group opacity-80">
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center text-slate-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                                    </div>
                                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">{{ __('starting_cash') }}</h3>
                                </div>
                                <div class="text-4xl font-black text-slate-900 dark:text-white tracking-tight">{{ formatCurrency(drawer.starting_cash) }}</div>
                            </div>
                        </div>

                        <!-- Expected Logic Graphic -->
                        <div class="bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-3xl p-6 sm:p-10 flex flex-col sm:flex-row items-center justify-between text-center sm:text-left gap-6">
                            <div>
                                <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">{{ __('expected_net_cash') }}</h3>
                                <p class="text-[10px] font-medium text-slate-500">{{ __('expected_net_cash_formula') }}</p>
                            </div>
                            <div class="text-3xl sm:text-5xl font-black tracking-tighter" :class="stats.expectedNetCash >= 0 ? 'text-emerald-500' : 'text-red-500'">
                                {{ formatCurrency(stats.expectedNetCash) }}
                            </div>
                        </div>
                    </div>

                    <!-- Close Drawer Panel -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 shadow-xl rounded-[2rem] p-8 flex flex-col">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-xs font-black uppercase tracking-[0.2em] text-emerald-500">{{ __('drawer_open') }}</span>
                        </div>

                        <div v-if="drawer.closed_at" class="bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 p-6 rounded-2xl">
                            <h3 class="font-black uppercase tracking-tight">{{ __('drawer_closed') }}</h3>
                            <p class="text-sm mt-2 font-medium">{{ __('drawer_closed_at') }}: {{ new Date(drawer.closed_at).toLocaleTimeString() }}</p>
                            <p class="text-sm mt-1 font-medium">{{ __('counted_cash') }}: <span class="font-black">{{ formatCurrency(drawer.closing_cash) }}</span></p>
                        </div>
                        
                        <div v-else class="flex-1 flex flex-col">
                            <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight mb-6">{{ __('end_of_day') }}</h3>
                            
                            <div class="space-y-6 flex-1">
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('counted_cash') }}</label>
                                    <input
                                        v-model="closingCash"
                                        type="number"
                                        step="0.01"
                                        class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-2xl py-4 px-5 text-xl font-black text-slate-900 dark:text-white focus:ring-amber-500 focus:border-amber-500"
                                    />
                                    <p class="text-[10px] font-medium text-slate-500 mt-2">{{ __('counted_cash_desc') }}</p>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('notes') }} ({{ __('optional') }})</label>
                                    <textarea
                                        v-model="notes"
                                        rows="3"
                                        class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-2xl p-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-amber-500 focus:border-amber-500"
                                        :placeholder="__('cash_drawer_notes_placeholder')"
                                    ></textarea>
                                </div>
                            </div>
                            
                            <PrimaryButton @click="closeDrawer" :disabled="isProcessing" class="mt-8 w-full justify-center py-4 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-200 dark:text-slate-900 font-black">
                                {{ __('close_drawer') }}
                            </PrimaryButton>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
