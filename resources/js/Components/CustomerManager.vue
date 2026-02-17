<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import axios from 'axios';
import { trans } from '@/lang';

const props = defineProps({
    customers: Object, // Changed to props.customers to match controller
    filters: Object,
});

const page = usePage();
const userRole = computed(() => page.props.auth.user.role || 'barber');
const routePrefix = computed(() => userRole.value === 'owner' ? 'owner' : 'barber');

const customersList = computed(() => props.customers.data);

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingCustomerId = ref(null);

const filterForm = ref({
    search: props.filters?.search || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

const form = reactive({
    name: '',
    phone: '',
    notes: '',
});

const formErrors = ref({});
const isProcessing = ref(false);

const openCreateModal = () => {
    isEditing.value = false;
    Object.assign(form, {
        name: '',
        phone: '',
        notes: ''
    });
    formErrors.value = {};
    isModalOpen.value = true;
};

const openEditModal = (customer) => {
    isEditing.value = true;
    editingCustomerId.value = customer.id;
    Object.assign(form, {
        name: customer.name,
        phone: customer.phone,
        notes: customer.notes
    });
    formErrors.value = {};
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    formErrors.value = {};
};

const submit = () => {
    isProcessing.value = true;
    formErrors.value = {};

    const routeName = isEditing.value 
        ? `${routePrefix.value}.customers.update`
        : `${routePrefix.value}.customers.store`;

    const url = isEditing.value 
        ? route(routeName, editingCustomerId.value)
        : route(routeName);
    
    const method = isEditing.value ? 'put' : 'post';

    axios[method](url, form)
        .then(() => {
            closeModal();
            router.reload({ only: ['customers'] }); 
        })
        .catch(error => {
            if (error.response?.status === 422) {
                formErrors.value = error.response.data.errors;
            } else {
                alert('An error occurred. Please try again.');
            }
        })
        .finally(() => {
            isProcessing.value = false;
        });
};

const deleteCustomer = (customer) => {
    if (confirm('Are you sure you want to delete this customer?')) {
        const routeName = `${routePrefix.value}.customers.destroy`;
        axios.delete(route(routeName, customer.id))
            .then(() => {
                router.reload({ only: ['customers'] });
            })
            .catch(() => {
                alert('Failed to delete customer.');
            });
    }
};

const applyFilters = () => {
    router.get(route(`${routePrefix.value}.customers.index`), filterForm.value, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        search: '',
        start_date: '',
        end_date: '',
    };
    applyFilters();
};

watch([() => filterForm.value.start_date, () => filterForm.value.end_date], () => {
    applyFilters();
});

const __ = (key) => trans(key, page.props.locale || 'en');

const isMobile = ref(false);
const checkMobile = () => { isMobile.value = window.innerWidth < 1024; };
onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});
const formatDate = (date) => {
    if (!date) return '-';
    const d = new Date(date);
    const dateLocale = (page.props.locale === 'fr' || page.props.locale === 'ar') ? 'fr-FR' : 'en-US';
    return d.toLocaleDateString(dateLocale);
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header & Search Section -->
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight px-2">{{ __('customers') }}</h2>
        
        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-white/5 premium-shadow space-y-4">
            <div class="flex flex-col xl:flex-row justify-between items-stretch xl:items-center gap-4">
                <div class="flex-1 flex items-center gap-3 bg-slate-50 dark:bg-white/5 px-4 rounded-2xl border border-slate-100 dark:border-white/10 group focus-within:border-amber-500/50 transition-all">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <input
                        v-model="filterForm.search"
                        @keyup.enter="applyFilters"
                        :placeholder="__('search_customers')"
                        class="flex-1 py-3 bg-transparent border-none text-sm font-bold text-slate-900 dark:text-white focus:ring-0 placeholder:text-slate-400"
                    />
                    <button @click="applyFilters" class="px-4 py-1.5 rounded-xl bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-900 text-[10px] font-black uppercase tracking-widest hover:scale-105 active:scale-95 transition-all">
                        {{ __('search') }}
                    </button>
                </div>
                <PrimaryButton @click="openCreateModal" class="hidden sm:flex group justify-center">
                    <svg class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                    {{ __('add_customer') }}
                </PrimaryButton>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-1">
                    <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 px-1">{{ __('start_date') }}</label>
                    <input v-model="filterForm.start_date" type="date" class="w-full rounded-xl border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:ring-0 transition-all" />
                </div>
                <div class="space-y-1">
                    <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 px-1">{{ __('end_date') }}</label>
                    <input v-model="filterForm.end_date" type="date" class="w-full rounded-xl border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:ring-0 transition-all" />
                </div>
                <div class="flex items-end">
                    <button @click="clearFilters" class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                        {{ __('clear_filters') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Desktop Table View -->
        <div v-if="!isMobile" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 rounded-[2rem] overflow-hidden premium-shadow">
            <table class="min-w-full divide-y divide-slate-100 dark:divide-white/5">
                <thead class="bg-slate-50/50 dark:bg-black/20">
                    <tr>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('name') }}</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('phone') }}</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('last_visit') }}</th>
                        <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    <tr v-for="customer in customersList" :key="customer.id" class="group hover:bg-slate-50/80 dark:hover:bg-white/5 transition-all">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 font-bold text-sm">
                                    {{ customer.name.charAt(0) }}
                                </div>
                                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ customer.name }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ customer.phone || '-' }}</span>
                        </td>
                        <td class="px-8 py-6">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                {{ customer.last_visit_at ? formatDate(customer.last_visit_at) : __('never') }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="openEditModal(customer)" class="p-2.5 rounded-xl bg-slate-100 hover:bg-amber-500 hover:text-white dark:bg-white/5 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <button @click="deleteCustomer(customer)" class="p-2.5 rounded-xl bg-slate-100 hover:bg-red-500 hover:text-white dark:bg-white/5 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile List View -->
        <div v-else class="grid grid-cols-1 gap-4 pb-20">
            <div v-for="customer in customersList" :key="customer.id" class="p-6 rounded-[2.5rem] bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 premium-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-xl">
                            {{ customer.name.charAt(0) }}
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ customer.name }}</h3>
                            <p class="text-xs text-slate-500 font-bold tracking-tight">{{ customer.phone || __('no_phone') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                         <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">{{ __('last_visit') }}</p>
                         <p class="text-[10px] font-black text-slate-900 dark:text-amber-500 mt-0.5">
                            {{ customer.last_visit_at ? formatDate(customer.last_visit_at) : __('never') }}
                         </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-6">
                    <button @click="openEditModal(customer)" class="flex items-center justify-center gap-2 py-4 rounded-2xl bg-slate-900 text-white dark:bg-white/10 font-black text-[10px] uppercase tracking-widest active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        {{ __('edit') }}
                    </button>
                    <button @click="deleteCustomer(customer)" class="flex items-center justify-center gap-2 py-4 rounded-2xl bg-red-50 text-red-600 font-black text-[10px] uppercase tracking-widest active:scale-95 transition-all border border-red-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        {{ __('delete') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <Pagination :links="customers.links" />

         <!-- Empty State -->
        <div v-if="customersList.length === 0" class="flex flex-col items-center justify-center py-20 px-6 text-center bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-white/5">
             <div class="p-6 rounded-[2rem] bg-slate-50 dark:bg-white/5 text-slate-300 dark:text-slate-700 mb-6">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
             </div>
             <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('no_customers_found') }}</h3>
             <p class="text-sm text-slate-500 mt-2 font-medium">{{ __('try_adjusting_search') }}</p>
             <PrimaryButton @click="openCreateModal" class="mt-8" v-if="searchQuery === ''">{{ __('add_your_first_customer') }}</PrimaryButton>
        </div>

        <!-- Mobile FAB -->
        <button 
            v-if="isMobile"
            @click="openCreateModal"
            class="fixed bottom-8 right-6 z-40 h-14 w-14 rounded-2xl bg-amber-500 text-slate-900 shadow-2xl shadow-amber-500/40 flex items-center justify-center active:scale-90 transition-all border-4 border-white dark:border-slate-900"
        >
            <svg class="w-8 h-8 font-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
        </button>

        <!-- Customer Modal -->
        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6 sm:p-10 bg-white dark:bg-slate-900">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight flex items-center gap-3">
                        <div class="p-2 rounded-xl bg-amber-500/10 text-amber-500">
                             <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        </div>
                        {{ isEditing ? __('edit_customer') : __('add_customer') }}
                    </h2>
                    <button @click="closeModal" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('name') }}</p>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm"
                            :placeholder="__('customer_name_placeholder')"
                        />
                        <InputError :message="formErrors.name" class="mt-2" />
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('phone') }}</p>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm"
                            placeholder="01 23 45 67 89"
                        />
                        <InputError :message="formErrors.phone" class="mt-2" />
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('notes') }}</p>
                        <textarea
                            v-model="form.notes"
                            class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm min-h-[100px] resize-none"
                            :placeholder="__('customer_notes_placeholder')"
                        ></textarea>
                        <InputError :message="formErrors.notes" class="mt-2" />
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button @click="closeModal" class="flex-1 py-4 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 font-black uppercase tracking-widest text-[10px] hover:bg-slate-200 transition-all">{{ __('cancel') }}</button>
                    <button 
                        @click="submit" 
                        class="flex-2 px-10 py-4 rounded-2xl bg-amber-500 text-slate-900 font-black uppercase tracking-[0.2em] text-[10px] shadow-lg shadow-amber-500/20 active:scale-95 transition-all disabled:opacity-50"
                        :disabled="isProcessing"
                    >
                        {{ isEditing ? __('update_customer') : __('create_customer') }}
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>
