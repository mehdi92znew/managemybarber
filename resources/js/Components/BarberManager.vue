<script setup>
import { ref, reactive, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { trans } from '@/lang';
import { usePage } from '@inertiajs/vue3';

const route = window.route;
const page = usePage();
const getLocale = () => page.props.locale || 'en';

const props = defineProps({
    initialBarbers: Array,
});

const barbers = ref(props.initialBarbers);
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingBarberId = ref(null);

const form = reactive({
    name: '',
    email: '',
    password: '',
    commission_type: 'percentage',
    commission_value: 0,
});

const formErrors = ref({});
const isProcessing = ref(false);

const openCreateModal = () => {
    isEditing.value = false;
    Object.assign(form, {
        name: '',
        email: '',
        password: '',
        commission_type: 'percentage',
        commission_value: 0
    });
    formErrors.value = {};
    isModalOpen.value = true;
};

const openEditModal = (barber) => {
    isEditing.value = true;
    editingBarberId.value = barber.id;
    Object.assign(form, {
        name: barber.name,
        email: barber.email,
        password: '',
        commission_type: barber.commission_type,
        commission_value: barber.commission_value
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
        ? route('owner.barbers.update', editingBarberId.value)
        : route('owner.barbers.store');
    
    const method = isEditing.value ? 'put' : 'post';

    axios[method](url, form)
        .then(() => {
            closeModal();
            window.location.reload();
        })
        .catch(error => {
            if (error.response?.status === 422) {
                formErrors.value = error.response.data.errors;
            } else {
                alert(trans('error_occurred', getLocale()));
            }
        })
        .finally(() => {
            isProcessing.value = false;
        });
};

const deleteBarber = (barber) => {
    if (confirm(trans('confirm_delete_barber', getLocale()))) {
        axios.delete(route('owner.barbers.destroy', barber.id))
            .then(() => {
                window.location.reload();
            })
            .catch(error => {
                alert(trans('failed_delete_barber', getLocale()));
            });
    }
};

const formatCurrency = (value) => {
    let locale = 'en-US';
    const currentLocale = getLocale();
    if (currentLocale === 'fr' || currentLocale === 'ar') locale = 'fr-FR';
    return new Intl.NumberFormat(locale, { style: 'currency', currency: 'USD', currencyDisplay: 'narrowSymbol' }).format(value || 0);
};

const isMobile = ref(false);
const checkMobile = () => { isMobile.value = window.innerWidth < 1024; };
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
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('our_barbers') }}</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">{{ __('manage_barbers_desc') }}</p>
            </div>
            <PrimaryButton @click="openCreateModal" class="hidden sm:flex group">
                <svg class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                {{ __('add_barber') }}
            </PrimaryButton>
        </div>

        <!-- Desktop Table View -->
        <div v-if="!isMobile" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 rounded-[2rem] overflow-hidden premium-shadow">
            <table class="min-w-full divide-y divide-slate-100 dark:divide-white/5">
                <thead class="bg-slate-50/50 dark:bg-black/20">
                    <tr>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('name') }}</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('commission') }}</th>
                        <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    <tr v-for="barber in barbers" :key="barber.id" class="group hover:bg-slate-50/80 dark:hover:bg-white/5 transition-all">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 font-black text-xl">
                                    {{ barber.name.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ barber.name }}</p>
                                    <p class="text-xs text-slate-500">{{ barber.email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="px-4 py-2 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-black uppercase tracking-widest">
                                <span v-if="barber.commission_type === 'percentage'">{{ barber.commission_value }}%</span>
                                <span v-else>{{ formatCurrency(barber.commission_value) }}</span>
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="openEditModal(barber)" class="p-2.5 rounded-xl bg-slate-100 hover:bg-amber-500 hover:text-white dark:bg-white/5 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <button @click="deleteBarber(barber)" class="p-2.5 rounded-xl bg-slate-100 hover:bg-red-500 hover:text-white dark:bg-white/5 transition-all">
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
            <div v-for="barber in barbers" :key="barber.id" class="p-6 rounded-[2.5rem] bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 premium-shadow">
                <div class="flex items-center gap-4 mb-6">
                     <div class="h-14 w-14 rounded-[1.25rem] bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-2xl">
                        {{ barber.name.charAt(0) }}
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ barber.name }}</h3>
                        <p class="text-xs text-slate-500 font-medium">{{ barber.email }}</p>
                    </div>
                </div>
                
                <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 mb-6">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('commission') }}</span>
                    <span class="text-sm font-black text-amber-500">
                         <span v-if="barber.commission_type === 'percentage'">{{ barber.commission_value }}%</span>
                         <span v-else>{{ formatCurrency(barber.commission_value) }}</span>
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button @click="openEditModal(barber)" class="flex items-center justify-center gap-2 py-4 rounded-2xl bg-slate-900 text-white dark:bg-white/10 font-black text-[10px] uppercase tracking-widest active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        {{ __('edit') }}
                    </button>
                    <button @click="deleteBarber(barber)" class="flex items-center justify-center gap-2 py-4 rounded-2xl bg-red-50 text-red-600 font-black text-[10px] uppercase tracking-widest active:scale-95 transition-all border border-red-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        {{ __('delete') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile FAB -->
        <button 
            v-if="isMobile"
            @click="openCreateModal"
            class="fixed bottom-8 right-6 z-40 h-14 w-14 rounded-2xl bg-amber-500 text-slate-900 shadow-2xl shadow-amber-500/40 flex items-center justify-center active:scale-90 transition-all border-4 border-white dark:border-slate-900"
        >
            <svg class="w-8 h-8 font-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
        </button>

        <!-- Barber Modal -->
        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6 sm:p-10 bg-white dark:bg-slate-900">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight flex items-center gap-3">
                         <div class="p-2 rounded-xl bg-amber-500/10 text-amber-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        {{ isEditing ? __('edit_barber') : __('add_barber') }}
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
                            :placeholder="__('barber_name')"
                        />
                        <InputError :message="formErrors.name" class="mt-2" />
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('email') }}</p>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm"
                            placeholder="email@example.com"
                        />
                        <InputError :message="formErrors.email" class="mt-2" />
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('password') }}</p>
                        <input
                            v-model="form.password"
                            type="password"
                            class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm"
                            :placeholder="isEditing ? __('leave_blank_password') : __('secret_password')"
                        />
                        <InputError :message="formErrors.password" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('commission_type') }}</p>
                            <select
                                v-model="form.commission_type"
                                class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm"
                            >
                                <option value="percentage">{{ __('percentage') }}</option>
                                <option value="fixed">{{ __('fixed') }}</option>
                            </select>
                            <InputError :message="formErrors.commission_type" class="mt-2" />
                        </div>
                        <div class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('value') }}</p>
                            <input
                                v-model="form.commission_value"
                                type="number"
                                step="0.01"
                                class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm"
                            />
                            <InputError :message="formErrors.commission_value" class="mt-2" />
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button @click="closeModal" class="flex-1 py-4 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 font-black uppercase tracking-widest text-[10px] hover:bg-slate-200 transition-all">{{ __('cancel') }}</button>
                    <button 
                        @click="submit" 
                        class="flex-2 px-10 py-4 rounded-2xl bg-amber-500 text-slate-900 font-black uppercase tracking-[0.2em] text-[10px] shadow-lg shadow-amber-500/20 active:scale-95 transition-all disabled:opacity-50"
                        :disabled="isProcessing"
                    >
                        {{ isEditing ? __('update_barber') : __('create_barber') }}
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>
