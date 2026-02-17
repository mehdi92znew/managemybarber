<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
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
    services: Object, // Changed to Object for paginated results
});

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'en');
const __ = (key) => trans(key, currentLocale.value);

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingServiceId = ref(null);

const form = reactive({
    name: '',
    price: 0,
    duration_minutes: 30,
    is_extra: false,
});

const formErrors = ref({});
const isProcessing = ref(false);

const openCreateModal = () => {
    isEditing.value = false;
    Object.assign(form, {
        name: '',
        price: 0,
        duration_minutes: 30,
        is_extra: false
    });
    formErrors.value = {};
    isModalOpen.value = true;
};

const openEditModal = (service) => {
    isEditing.value = true;
    editingServiceId.value = service.id;
    Object.assign(form, {
        name: service.name,
        price: service.price,
        duration_minutes: service.duration_minutes,
        is_extra: Boolean(service.is_extra)
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

    const url = isEditing.value 
        ? route('owner.services.update', editingServiceId.value)
        : route('owner.services.store');
    
    const method = isEditing.value ? 'put' : 'post';

    axios[method](url, form)
        .then(() => {
            closeModal();
            router.reload({ only: ['services'] });
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

const deleteService = (service) => {
    if (confirm(trans('confirm_delete_service', currentLocale.value))) {
        axios.delete(route('owner.services.destroy', service.id))
            .then(() => {
                router.reload({ only: ['services'] });
            })
            .catch(error => {
                alert('Failed to delete service.');
            });
    }
};

const formatCurrency = (value) => {
    let locale = 'en-US';
    if (currentLocale.value === 'fr' || currentLocale.value === 'ar') locale = 'fr-FR';
    return new Intl.NumberFormat(locale, { style: 'currency', currency: 'USD', currencyDisplay: 'narrowSymbol' }).format(value || 0);
};

const isMobile = ref(false);
const checkMobile = () => { isMobile.value = window.innerWidth < 768; };
onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});
</script>

<template>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-white/5 premium-shadow">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('service_menu') }}</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">{{ __('manage_services_desc') }}</p>
            </div>
            <PrimaryButton @click="openCreateModal" class="hidden sm:flex group">
                <svg class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                {{ __('add_service') }}
            </PrimaryButton>
        </div>

        <!-- Desktop Table View -->
        <div v-if="!isMobile" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 rounded-[2rem] overflow-hidden premium-shadow">
            <table class="min-w-full divide-y divide-slate-100 dark:divide-white/5">
                <thead class="bg-slate-50/50 dark:bg-black/20">
                    <tr>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('name') }}</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('duration') }}</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('price') }}</th>
                        <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    <tr v-for="service in services.data" :key="service.id" class="group hover:bg-slate-50/80 dark:hover:bg-white/5 transition-all">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-xl bg-amber-500/10 text-amber-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z" /></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ service.name }}</p>
                                    <span v-if="service.is_extra" class="text-[9px] font-black uppercase tracking-widest text-amber-500">{{ __('extra_service') }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="px-3 py-1.5 rounded-full bg-slate-100 dark:bg-white/5 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                {{ service.duration_minutes }} {{ __('mins_short') }}
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            <span class="text-sm font-black text-slate-900 dark:text-white">{{ formatCurrency(service.price) }}</span>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="openEditModal(service)" class="p-2 rounded-lg bg-slate-100 hover:bg-amber-500 hover:text-white dark:bg-white/5 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <button @click="deleteService(service)" class="p-2 rounded-lg bg-slate-100 hover:bg-red-500 hover:text-white dark:bg-white/5 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Grid View -->
        <div v-else class="grid grid-cols-1 gap-4 pb-20">
            <div v-for="service in services.data" :key="service.id" class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 premium-shadow group">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-2xl bg-amber-500/10 text-amber-500">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z" /></svg>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ service.name }}</h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ service.duration_minutes }} {{ __('mins_short') }}</span>
                                <span v-if="service.is_extra" class="h-1 w-1 rounded-full bg-amber-500"></span>
                                <span v-if="service.is_extra" class="text-[9px] font-black uppercase tracking-widest text-amber-500">{{ __('extra') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-lg font-black text-slate-900 dark:text-white">{{ formatCurrency(service.price) }}</div>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-4">
                    <button @click="openEditModal(service)" class="flex items-center justify-center gap-2 py-3 rounded-xl bg-slate-50 dark:bg-white/5 text-slate-600 dark:text-slate-400 font-bold text-xs hover:bg-amber-500 hover:text-white transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        {{ __('edit') }}
                    </button>
                    <button @click="deleteService(service)" class="flex items-center justify-center gap-2 py-3 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold text-xs hover:bg-red-600 hover:text-white transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        {{ __('delete') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <Pagination :links="services.links" />

        <!-- Empty State -->
        <div v-if="services.data.length === 0" class="flex flex-col items-center justify-center py-20 px-6 text-center bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-white/5">
             <div class="p-6 rounded-3xl bg-slate-50 dark:bg-white/5 text-slate-300 dark:text-slate-700 mb-6">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z" /></svg>
             </div>
             <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('no_services_found') }}</h3>
             <p class="text-sm text-slate-500 mt-2 font-medium">{{ __('no_services_desc') }}</p>
             <PrimaryButton @click="openCreateModal" class="mt-8">{{ __('add_your_first_service') }}</PrimaryButton>
        </div>

        <!-- Mobile FAB -->
        <button 
            v-if="isMobile"
            @click="openCreateModal"
            class="fixed bottom-8 right-6 z-40 h-14 w-14 rounded-2xl bg-amber-500 text-slate-900 shadow-2xl shadow-amber-500/40 flex items-center justify-center active:scale-90 transition-all border-4 border-white dark:border-slate-900"
        >
            <svg class="w-8 h-8 font-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
        </button>

        <!-- Service Modal -->
        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6 sm:p-10 bg-white dark:bg-slate-900">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight flex items-center gap-3">
                        <div class="p-2 rounded-xl bg-amber-500/10 text-amber-500">
                             <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        {{ isEditing ? __('edit_service') : __('add_service') }}
                    </h2>
                    <button @click="closeModal" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('service_name') }}</p>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm placeholder:text-slate-300"
                            :placeholder="__('service_placeholder')"
                        />
                        <InputError :message="formErrors.name" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('duration_mins') }}</p>
                            <input
                                id="duration_minutes"
                                v-model="form.duration_minutes"
                                type="number"
                                class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm"
                            />
                            <InputError :message="formErrors.duration_minutes" class="mt-2" />
                        </div>
                        <div class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('price_currency') }}</p>
                            <input
                                id="price"
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm"
                            />
                            <InputError :message="formErrors.price" class="mt-2" />
                        </div>
                    </div>

                    <label class="flex items-center gap-4 p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 cursor-pointer group transition-all" :class="form.is_extra ? 'border-amber-500/30 bg-amber-500/5' : ''">
                        <div class="relative w-10 h-6">
                             <input
                                id="is_extra"
                                type="checkbox"
                                v-model="form.is_extra"
                                class="sr-only"
                            />
                            <div class="w-10 h-6 bg-slate-200 dark:bg-slate-700 rounded-full transition-colors" :class="form.is_extra ? 'bg-amber-500' : ''"></div>
                            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform" :class="form.is_extra ? 'translate-x-4' : ''"></div>
                        </div>
                        <div>
                             <p class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">{{ __('is_extra_label') }}</p>
                             <p class="text-[10px] font-medium text-slate-500 mt-0.5">{{ __('extra_service_desc') }}</p>
                        </div>
                    </label>
                </div>

                <div class="mt-10 flex gap-4">
                    <button @click="closeModal" class="flex-1 py-4 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 font-black uppercase tracking-widest text-[10px] hover:bg-slate-200 transition-all">{{ __('cancel') }}</button>
                    <button 
                        @click="submit" 
                        class="flex-2 px-10 py-4 rounded-2xl bg-amber-500 text-slate-900 font-black uppercase tracking-[0.2em] text-[10px] shadow-lg shadow-amber-500/20 active:scale-95 transition-all disabled:opacity-50"
                        :disabled="isProcessing"
                    >
                        {{ isEditing ? __('update_service') : __('create_service') }}
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>
