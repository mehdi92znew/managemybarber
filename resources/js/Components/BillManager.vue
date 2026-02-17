<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import { trans } from '@/lang';

const props = defineProps({
    bills: Object, // Changed to Object for paginated results
    filters: Object,
});

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'en');
const showAddModal = ref(false);

const filterForm = ref({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    type: props.filters?.type || '',
});

const applyFilters = () => {
    router.get(route('owner.bills.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const clearFilters = () => {
    filterForm.value = {
        start_date: '',
        end_date: '',
        type: '',
    };
    applyFilters();
};

watch(filterForm, () => {
    applyFilters();
}, { deep: true });

const form = useForm({
    amount: '',
    date: new Date().toISOString().split('T')[0],
    type: 'Utility',
    note: '',
});

const customType = ref('');

const types = computed(() => [
    { value: 'Utility', label: __('utility') }, 
    { value: 'Rent', label: __('rent') }, 
    { value: 'Supplies', label: __('supplies') }, 
    { value: 'Equipment', label: __('equipment') }, 
    { value: 'Other', label: __('other') }
]);

const submit = () => {
    // If "Other" is selected, use the customType value if provided
    if (form.type === 'Other' && customType.value.trim()) {
        form.type = customType.value.trim();
    }
    
    form.post(route('owner.bills.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
            customType.value = '';
        },
    });
};

const deleteBill = (id) => {
    if (confirm(trans('confirm_delete_bill', currentLocale.value))) {
        router.delete(route('owner.bills.destroy', id), {
            preserveScroll: true
        });
    }
};

const formatCurrency = (value) => {
    let locale = 'en-US';
    if (currentLocale.value === 'fr' || currentLocale.value === 'ar') locale = 'fr-FR';
    return new Intl.NumberFormat(locale, { style: 'currency', currency: 'USD', currencyDisplay: 'narrowSymbol' }).format(value || 0);
};

const __ = (key) => trans(key, currentLocale.value);

const isMobile = ref(false);
const checkMobile = () => { isMobile.value = window.innerWidth < 1024; };
onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

const formatShortDate = (date) => {
    if (!date) return '-';
    const d = new Date(date);
    const dateLocale = (currentLocale.value === 'fr' || currentLocale.value === 'ar') ? 'fr-FR' : 'en-US';
    return d.toLocaleDateString(dateLocale, { day: '2-digit', month: 'short' });
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-white/5 premium-shadow">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('bills') }}</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">{{ __('manage_bills_desc') }}</p>
            </div>
            <PrimaryButton @click="showAddModal = true" class="hidden sm:flex group">
                <svg class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                {{ __('add_bill') }}
            </PrimaryButton>
        </div>

        <!-- Filters Section -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-white/5 premium-shadow">
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">{{ __('bill_type') }}</label>
                <select v-model="filterForm.type" class="w-full rounded-2xl border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-sm font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:ring-amber-500 transition-all">
                    <option value="">{{ __('all_types') }}</option>
                    <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">{{ __('start_date') }}</label>
                <input v-model="filterForm.start_date" type="date" class="w-full rounded-2xl border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-sm font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:ring-amber-500 transition-all" />
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">{{ __('end_date') }}</label>
                <input v-model="filterForm.end_date" type="date" class="w-full rounded-2xl border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-sm font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:ring-amber-500 transition-all" />
            </div>
            <div class="flex items-end">
                <button @click="clearFilters" class="w-full py-3 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 dark:hover:bg-white/10 transition-all">
                    {{ __('clear_filters') }}
                </button>
            </div>
        </div>

        <!-- Desktop Table View -->
        <div v-if="!isMobile" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 rounded-[2rem] overflow-hidden premium-shadow">
            <table class="min-w-full divide-y divide-slate-100 dark:divide-white/5">
                <thead class="bg-slate-50/50 dark:bg-black/20">
                    <tr>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('date') }}</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('bill_type') }}</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('bill_note') }}</th>
                        <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('bill_amount') }}</th>
                        <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    <tr v-for="bill in bills.data" :key="bill.id" class="group hover:bg-slate-50/80 dark:hover:bg-white/5 transition-all">
                        <td class="px-8 py-6">
                            <span class="text-sm font-bold text-slate-600 dark:text-slate-400">{{ bill.date }}</span>
                        </td>
                        <td class="px-8 py-6">
                            <span class="px-3 py-1.5 rounded-lg bg-amber-500/10 text-amber-500 text-[10px] font-black uppercase tracking-widest border border-amber-500/20">
                                {{ __(bill.type.toLowerCase()) }}
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            <p class="text-sm font-medium text-slate-500 italic max-w-xs truncate">{{ bill.note || '-' }}</p>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <span class="font-black text-slate-900 dark:text-white">{{ formatCurrency(bill.amount) }}</span>
                        </td>
                        <td class="px-8 py-6 text-right">
                             <button @click="deleteBill(bill.id)" class="p-2.5 rounded-xl bg-slate-100/50 hover:bg-red-500 hover:text-white dark:bg-white/5 transition-all opacity-0 group-hover:opacity-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div v-else class="grid grid-cols-1 gap-4 pb-20">
            <div v-for="bill in bills.data" :key="bill.id" class="p-5 rounded-[2.5rem] bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 premium-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-sm">
                            {{ bill.type.charAt(0) }}
                        </div>
                        <div>
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block">{{ __('bill_type') }}</span>
                            <span class="text-xs font-black text-slate-900 dark:text-white uppercase">{{ __(bill.type.toLowerCase()) }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block">{{ __('bill_amount') }}</span>
                        <span class="text-sm font-black text-amber-500 underline decoration-amber-500/30 decoration-2 underline-offset-4">{{ formatCurrency(bill.amount) }}</span>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 mb-4">
                     <p class="text-xs font-medium text-slate-500 italic">{{ bill.note || __('no_note') }}</p>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                         <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                         <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ formatShortDate(bill.date) }}</span>
                    </div>
                     <button @click="deleteBill(bill.id)" class="px-4 py-2 rounded-xl bg-red-50 text-red-600 text-[10px] font-black uppercase tracking-widest active:scale-95 transition-all">
                        {{ __('delete') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <Pagination :links="bills.links" />

        <!-- Empty State -->
        <div v-if="bills.data.length === 0" class="flex flex-col items-center justify-center py-20 px-6 text-center bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-white/5">
             <div class="p-6 rounded-[2rem] bg-slate-50 dark:bg-white/5 text-slate-300 dark:text-slate-700 mb-6">
                 <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
             </div>
             <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('no_bills_found') }}</h3>
             <p class="text-sm text-slate-500 mt-2 font-medium">{{ __('add_bills_to_track_expenses') }}</p>
             <PrimaryButton @click="showAddModal = true" class="mt-8">{{ __('add_your_first_bill') }}</PrimaryButton>
        </div>

        <!-- Mobile FAB -->
        <button 
            v-if="isMobile"
            @click="showAddModal = true"
            class="fixed bottom-8 right-6 z-40 h-14 w-14 rounded-2xl bg-amber-500 text-slate-900 shadow-2xl shadow-amber-500/40 flex items-center justify-center active:scale-90 transition-all border-4 border-white dark:border-slate-900"
        >
            <svg class="w-8 h-8 font-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
        </button>

        <!-- Bill Modal -->
        <Modal :show="showAddModal" @close="showAddModal = false" maxWidth="lg">
            <div class="p-6 sm:p-10 bg-white dark:bg-slate-900">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight flex items-center gap-3">
                         <div class="p-2 rounded-xl bg-amber-500/10 text-amber-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                        </div>
                        {{ __('add_bill') }}
                    </h2>
                    <button @click="showAddModal = false" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('bill_amount') }}</p>
                            <input v-model="form.amount" type="number" step="0.01" class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm" required />
                        </div>
                        <div class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('date') }}</p>
                            <input v-model="form.date" type="date" class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm" required />
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('bill_type') }}</p>
                        <select v-model="form.type" class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm">
                            <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
                        </select>
                    </div>

                    <!-- Custom Type Input (Visible only if Other is selected) -->
                    <div v-if="form.type === 'Other'" class="p-5 rounded-2xl bg-amber-500/5 border border-amber-500/20 animate-in fade-in slide-in-from-top-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-amber-600 mb-2">{{ __('specify_type') }}</p>
                        <input v-model="customType" type="text" class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm placeholder:text-amber-500/30" :placeholder="__('specify_type_placeholder')" />
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('bill_note') }}</p>
                        <textarea v-model="form.note" class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm min-h-[100px] resize-none" :placeholder="__('add_optional_note')"></textarea>
                    </div>
                    
                    <div class="flex gap-4 pt-4">
                        <button @click="showAddModal = false" type="button" class="flex-1 py-4 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 font-black uppercase tracking-widest text-[10px] hover:bg-slate-200 transition-all">{{ __('cancel') }}</button>
                        <button :disabled="form.processing" class="flex-2 px-10 py-4 rounded-2xl bg-amber-500 text-slate-900 font-black uppercase tracking-[0.2em] text-[10px] shadow-lg shadow-amber-500/20 active:scale-95 transition-all disabled:opacity-50">
                            {{ __('add_bill') }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>
