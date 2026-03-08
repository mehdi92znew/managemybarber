<script setup>
import { ref, reactive, computed, watch, onMounted } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Pagination from "@/Components/Pagination.vue";
import axios from "axios";
import { trans } from "@/lang";

const props = defineProps({
    customers: Object, // Changed to props.customers to match controller
    filters: Object,
});

const page = usePage();
const userRole = computed(() => page.props.auth.user.role || "barber");
const routePrefix = computed(() =>
    userRole.value === "owner" ? "owner" : "barber",
);

const customersList = computed(() => props.customers.data);

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingCustomerId = ref(null);

const isHistoryModalOpen = ref(false);
const selectedHistoryCustomer = ref(null);
const customerHistory = ref([]);
const isLoadingHistory = ref(false);

const filterForm = ref({
    search: props.filters?.search || "",
});

const form = reactive({
    name: "",
    phone: "",
    notes: "",
});

const formErrors = ref({});
const isProcessing = ref(false);

const openCreateModal = () => {
    isEditing.value = false;
    Object.assign(form, {
        name: "",
        phone: "",
        notes: "",
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
        notes: customer.notes,
    });
    formErrors.value = {};
    isModalOpen.value = true;
};

const openHistoryModal = (customer) => {
    selectedHistoryCustomer.value = customer;
    isHistoryModalOpen.value = true;
    isLoadingHistory.value = true;
    
    axios.get(route(`${routePrefix.value}.customers.show`, customer.id))
        .then(response => {
            customerHistory.value = response.data.history;
        })
        .catch(error => {
            alert("Failed to load history.");
        })
        .finally(() => {
            isLoadingHistory.value = false;
        });
};

const closeHistoryModal = () => {
    isHistoryModalOpen.value = false;
    selectedHistoryCustomer.value = null;
    customerHistory.value = [];
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

    const method = isEditing.value ? "put" : "post";

    axios[method](url, form)
        .then(() => {
            closeModal();
            router.reload({ only: ["customers"] });
        })
        .catch((error) => {
            if (error.response?.status === 422) {
                formErrors.value = error.response.data.errors;
            } else {
                alert("An error occurred. Please try again.");
            }
        })
        .finally(() => {
            isProcessing.value = false;
        });
};

const deleteCustomer = (customer) => {
    if (confirm("Are you sure you want to delete this customer?")) {
        const routeName = `${routePrefix.value}.customers.destroy`;
        axios
            .delete(route(routeName, customer.id))
            .then(() => {
                router.reload({ only: ["customers"] });
            })
            .catch(() => {
                alert("Failed to delete customer.");
            });
    }
};

const applyFilters = () => {
    router.get(
        route(`${routePrefix.value}.customers.index`),
        filterForm.value,
        {
            preserveState: true,
            replace: true,
        },
    );
};

const clearFilters = () => {
    filterForm.value = {
        search: "",
    };
    applyFilters();
};

const __ = (key) => trans(key, page.props.locale || "en");

const isMobile = ref(false);
const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
};
onMounted(() => {
    checkMobile();
    window.addEventListener("resize", checkMobile);
});
const formatDate = (date) => {
    if (!date) return "-";
    const d = new Date(date);
    const dateLocale =
        page.props.locale === "fr" || page.props.locale === "ar"
            ? "fr-FR"
            : "en-US";
    return d.toLocaleDateString(dateLocale);
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header & Search Section -->
        <h2
            class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight px-2"
        >
            {{ __("customers") }}
        </h2>

        <div
            class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-white/5 premium-shadow space-y-4"
        >
            <div
                class="flex flex-col xl:flex-row justify-between items-stretch xl:items-center gap-4"
            >
                <div
                    class="flex-1 flex items-center gap-3 bg-slate-50 dark:bg-white/5 px-4 rounded-2xl border border-slate-100 dark:border-white/10 group focus-within:border-amber-500/50 transition-all"
                >
                    <svg
                        class="w-5 h-5 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>
                    <input
                        v-model="filterForm.search"
                        @keyup.enter="applyFilters"
                        :placeholder="__('search_customers')"
                        class="flex-1 py-3 bg-transparent border-none text-sm font-bold text-slate-900 dark:text-white focus:ring-0 placeholder:text-slate-400"
                    />
                    <button
                        @click="applyFilters"
                        class="px-4 py-1.5 rounded-xl bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-900 text-[10px] font-black uppercase tracking-widest hover:scale-105 active:scale-95 transition-all"
                    >
                        {{ __("search") }}
                    </button>
                </div>
                <PrimaryButton
                    @click="openCreateModal"
                    class="hidden sm:flex group justify-center"
                >
                    <svg
                        class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="3"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    {{ __("add_customer") }}
                </PrimaryButton>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div class="flex items-end">
                    <button
                        @click="clearFilters"
                        class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all"
                    >
                        {{ __("clear_filters") }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Grid View (Standardized) -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 pb-20">
            <div
                v-for="customer in customersList"
                :key="customer.id"
                class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 premium-shadow group"
            >
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-4 flex-1 pr-4">
                        <div
                            class="shrink-0 h-12 w-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-xl"
                        >
                            {{ customer.name.charAt(0) }}
                        </div>
                        <div class="min-w-0">
                            <h3
                                class="font-black text-slate-900 dark:text-white uppercase tracking-tight truncate"
                            >
                                {{ customer.name }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate">{{ customer.phone || __("no_phone") }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="customer.notes" class="mb-4 bg-slate-50 dark:bg-white/5 p-3 rounded-xl">
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">
                        {{ __("notes") }}
                    </p>
                    <p class="text-xs font-bold text-slate-600 dark:text-slate-300">
                        {{ customer.notes }}
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-3 mt-4">
                    <button
                        @click="openHistoryModal(customer)"
                        class="flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-2 py-3 px-2 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold text-[10px] sm:text-xs hover:bg-blue-600 hover:text-white transition-all"
                    >
                        <svg class="w-4 h-4 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="truncate">{{ __("history") }}</span>
                    </button>
                    <button
                        @click="openEditModal(customer)"
                        class="flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-2 py-3 px-2 rounded-xl bg-slate-50 dark:bg-white/5 text-slate-600 dark:text-slate-400 font-bold text-[10px] sm:text-xs hover:bg-amber-500 hover:text-white transition-all"
                    >
                        <svg class="w-4 h-4 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        <span class="truncate">{{ __("edit") }}</span>
                    </button>
                    <button
                        @click="deleteCustomer(customer)"
                        class="flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-2 py-3 px-2 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold text-[10px] sm:text-xs hover:bg-red-600 hover:text-white transition-all"
                    >
                        <svg class="w-4 h-4 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        <span class="truncate">{{ __("delete") }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <Pagination :links="customers.links" />

        <!-- Empty State -->
        <div
            v-if="customersList.length === 0"
            class="flex flex-col items-center justify-center py-20 px-6 text-center bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-white/5"
        >
            <div
                class="p-6 rounded-[2rem] bg-slate-50 dark:bg-white/5 text-slate-300 dark:text-slate-700 mb-6"
            >
                <svg
                    class="w-16 h-16"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                    />
                </svg>
            </div>
            <h3
                class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight"
            >
                {{ __("no_customers_found") }}
            </h3>
            <p class="text-sm text-slate-500 mt-2 font-medium">
                {{ __("try_adjusting_search") }}
            </p>
            <PrimaryButton
                @click="openCreateModal"
                class="mt-8"
                v-if="searchQuery === ''"
                >{{ __("add_your_first_customer") }}</PrimaryButton
            >
        </div>

        <!-- Mobile FAB -->
        <button
            v-if="isMobile"
            @click="openCreateModal"
            class="fixed bottom-8 right-6 z-40 h-14 w-14 rounded-2xl bg-amber-500 text-slate-900 shadow-2xl shadow-amber-500/40 flex items-center justify-center active:scale-90 transition-all border-4 border-white dark:border-slate-900"
        >
            <svg
                class="w-8 h-8 font-black"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="3"
                    d="M12 4v16m8-8H4"
                />
            </svg>
        </button>

        <!-- Customer Modal -->
        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6 sm:p-10 bg-white dark:bg-slate-900">
                <div class="flex items-center justify-between mb-8">
                    <h2
                        class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight flex items-center gap-3"
                    >
                        <div
                            class="p-2 rounded-xl bg-amber-500/10 text-amber-500"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                                />
                            </svg>
                        </div>
                        {{
                            isEditing ? __("edit_customer") : __("add_customer")
                        }}
                    </h2>
                    <button
                        @click="closeModal"
                        class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <div
                        class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5"
                    >
                        <p
                            class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2"
                        >
                            {{ __("name") }}
                        </p>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm"
                            :placeholder="__('customer_name_placeholder')"
                        />
                        <InputError :message="formErrors.name" class="mt-2" />
                    </div>

                    <div
                        class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5"
                    >
                        <p
                            class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2"
                        >
                            {{ __("phone") }}
                        </p>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm"
                            placeholder="01 23 45 67 89"
                        />
                        <InputError :message="formErrors.phone" class="mt-2" />
                    </div>

                    <div
                        class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5"
                    >
                        <p
                            class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2"
                        >
                            {{ __("notes") }}
                        </p>
                        <textarea
                            v-model="form.notes"
                            class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm min-h-[100px] resize-none"
                            :placeholder="__('customer_notes_placeholder')"
                        ></textarea>
                        <InputError :message="formErrors.notes" class="mt-2" />
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button
                        @click="closeModal"
                        class="flex-1 py-4 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 font-black uppercase tracking-widest text-[10px] hover:bg-slate-200 transition-all"
                    >
                        {{ __("cancel") }}
                    </button>
                    <button
                        @click="submit"
                        class="flex-2 px-10 py-4 rounded-2xl bg-amber-500 text-slate-900 font-black uppercase tracking-[0.2em] text-[10px] shadow-lg shadow-amber-500/20 active:scale-95 transition-all disabled:opacity-50"
                        :disabled="isProcessing"
                    >
                        {{
                            isEditing
                                ? __("update_customer")
                                : __("create_customer")
                        }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- History Modal -->
        <Modal :show="isHistoryModalOpen" @close="closeHistoryModal">
            <div class="p-6 bg-white dark:bg-slate-900 flex flex-col max-h-[90vh]">
                <div class="flex items-center justify-between mb-6 shrink-0">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight flex items-center gap-3">
                        <div class="p-2 rounded-xl bg-blue-500/10 text-blue-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        {{ __("customer_history") }}
                    </h2>
                    <button @click="closeHistoryModal" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 transition-colors">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    </button>
                </div>

                <div v-if="isLoadingHistory" class="py-12 flex justify-center items-center">
                    <div class="w-8 h-8 rounded-full border-4 border-slate-200 border-t-amber-500 animate-spin"></div>
                </div>

                <div v-else-if="customerHistory.length === 0" class="py-12 text-center text-slate-500 flex flex-col items-center">
                    <svg class="w-12 h-12 mb-4 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="font-bold">{{ __("no_history_found") }}</p>
                </div>

                <div v-else class="flex-1 overflow-y-auto space-y-4 pr-2 -mr-2 bg-slate-50/50 dark:bg-black/10 rounded-2xl p-4 border border-slate-100 dark:border-white/5 shadow-inner">
                    <div v-for="item in customerHistory" :key="item.id" class="p-4 rounded-2xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 premium-shadow relative transition-transform hover:-translate-y-0.5">
                        <div class="flex justify-between items-start mb-3">
                            <div class="font-black text-slate-900 dark:text-white text-sm tracking-tight">{{ item.date }}</div>
                            <div class="text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full shadow-sm" 
                                :class="item.status === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30' : 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300'">
                                {{ __(item.status) }}
                            </div>
                        </div>
                        <div class="text-xs text-slate-500 font-medium mb-1.5 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"></path></svg>
                            <span class="opacity-80">{{ item.services || '-' }}</span>
                        </div>
                        <div class="text-xs text-slate-500 font-medium mb-4 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span class="opacity-80">{{ item.barber_name }}</span>
                        </div>
                        <div class="flex justify-between items-end mt-4 pt-3 border-t border-slate-100 dark:border-white/5">
                            <div class="text-[9px] uppercase font-black tracking-widest opacity-60">
                                {{ __(item.payment_status) }}
                            </div>
                            <div class="text-base font-black text-amber-500 flex items-center gap-1">
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold">$</span>{{ item.total_price }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>
    </div>
</template>
