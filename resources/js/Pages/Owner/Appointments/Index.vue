<script setup>
import { ref, computed, watch } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import { trans } from "@/lang";

const props = defineProps({
    appointments: Object,
    barbers: Array,
    services: Array,
    filters: Object,
});

import BookingModal from "@/Components/BookingModal.vue";
const isEditModalOpen = ref(false);
const selectedAppt = ref(null);

const editAppointment = (appt) => {
    selectedAppt.value = {
        ...appt,
        start: appt.start_time,
        extendedProps: {
            barber_id: appt.barber_id,
            customer_id: appt.customer_id,
            customer_name: appt.customer?.name,
            services: appt.services.map((s) => s.id),
            notes: appt.notes,
            status: appt.status,
            payment_status: appt.payment_status,
            total_price: appt.total_price,
        },
    };
    isEditModalOpen.value = true;
};

const page = usePage();
const currentLocale = computed(() => page.props.locale || "en");
const __ = (key) => trans(key, currentLocale.value);

const filterForm = ref({
    barber_id: props.filters?.barber_id || "",
    start_date: props.filters?.start_date || "",
    end_date: props.filters?.end_date || "",
    status: props.filters?.status || "",
    payment_status: props.filters?.payment_status || "",
});

const applyFilters = () => {
    router.get(route("owner.appointments.list"), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        barber_id: "",
        start_date: "",
        end_date: "",
        status: "",
        payment_status: "",
    };
    applyFilters();
};

watch(
    filterForm,
    () => {
        applyFilters();
    },
    { deep: true },
);

const formatCurrency = (value) => {
    let locale = "en-US";
    if (currentLocale.value === "fr" || currentLocale.value === "ar")
        locale = "fr-FR";
    return new Intl.NumberFormat(locale, {
        style: "currency",
        currency: "USD",
        currencyDisplay: "narrowSymbol",
    }).format(value || 0);
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return "-";
    // dateStr comes as "YYYY-MM-DD HH:mm:ss" now
    const [datePart, timePart] = dateStr.split(" ");
    if (!datePart || !timePart) return dateStr;
    const [yyyy, mm, dd] = datePart.split("-");
    const [hh, min] = timePart.split(":");

    // Quick month dictionary
    const month =
        {
            "01": "Jan",
            "02": "Feb",
            "03": "Mar",
            "04": "Apr",
            "05": "May",
            "06": "Jun",
            "07": "Jul",
            "08": "Aug",
            "09": "Sep",
            10: "Oct",
            11: "Nov",
            12: "Dec",
        }[mm] || mm;

    return `${dd} ${month}, ${hh}:${min}`;
};

const getStatusClass = (status) => {
    switch (status) {
        case "completed":
            return "bg-emerald-500/10 text-emerald-500 border-emerald-500/20";
        case "scheduled":
            return "bg-amber-500/10 text-amber-500 border-amber-500/20";
        case "cancelled":
            return "bg-red-500/10 text-red-500 border-red-500/20";
        default:
            return "bg-slate-500/10 text-slate-500 border-slate-500/20";
    }
};

const getPaymentStatusClass = (status) => {
    switch (status) {
        case "paid":
            return "bg-emerald-500/10 text-emerald-500 border-emerald-500/20";
        case "semi-paid":
            return "bg-amber-500/10 text-amber-500 border-amber-500/20";
        case "unpaid":
        case "pending":
            return "bg-slate-500/10 text-slate-500 border-slate-500/20";
        default:
            return "bg-slate-500/10 text-slate-500 border-slate-500/20";
    }
};
</script>

<template>
    <Head :title="__('appointments')" />

    <AuthenticatedLayout>
        <template #header-title>{{ __("appointments") }}</template>
        <template #header>{{ __("appointment_management") }}</template>

        <div class="py-6 space-y-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div
                class="grid grid-cols-1 md:grid-cols-6 gap-4 bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-white/5 premium-shadow items-end"
            >
                <div class="space-y-2">
                    <label
                        class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1"
                        >{{ __("barber") }}</label
                    >
                    <select
                        v-model="filterForm.barber_id"
                        class="w-full rounded-2xl border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-sm font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:ring-0 transition-all"
                    >
                        <option value="">{{ __("all_barbers") }}</option>
                        <option v-for="b in barbers" :key="b.id" :value="b.id">
                            {{ b.name }}
                        </option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label
                        class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1"
                        >{{ __("status") }}</label
                    >
                    <select
                        v-model="filterForm.status"
                        class="w-full rounded-2xl border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-sm font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:ring-0 transition-all"
                    >
                        <option value="">{{ __("all_statuses") }}</option>
                        <option value="scheduled">{{ __("scheduled") }}</option>
                        <option value="completed">{{ __("completed") }}</option>
                        <option value="cancelled">{{ __("cancelled") }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label
                        class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1"
                        >{{ __("payment_status") }}</label
                    >
                    <select
                        v-model="filterForm.payment_status"
                        class="w-full rounded-2xl border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-sm font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:ring-0 transition-all"
                    >
                        <option value="">
                            {{ __("all_payment_statuses") }}
                        </option>
                        <option value="unpaid">{{ __("unpaid") }}</option>
                        <option value="semi-paid">{{ __("semi_paid") }}</option>
                        <option value="paid">{{ __("paid") }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label
                        class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1"
                        >{{ __("start_date") }}</label
                    >
                    <input
                        v-model="filterForm.start_date"
                        type="date"
                        class="w-full rounded-2xl border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-sm font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:ring-0 transition-all"
                    />
                </div>
                <div class="space-y-2">
                    <label
                        class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1"
                        >{{ __("end_date") }}</label
                    >
                    <input
                        v-model="filterForm.end_date"
                        type="date"
                        class="w-full rounded-2xl border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-sm font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:ring-0 transition-all"
                    />
                </div>
                <button
                    @click="clearFilters"
                    class="w-full py-3 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all"
                >
                    {{ __("clear_filters") }}
                </button>
            </div>

            <!-- List -->
            <div
                class="bg-transparent sm:bg-white dark:sm:bg-slate-900 border-none sm:border sm:border-slate-200 dark:sm:border-white/5 rounded-[2.5rem] overflow-hidden sm:premium-shadow"
            >
                <!-- Desktop Table -->
                <table
                    class="hidden lg:table min-w-full divide-y divide-slate-100 dark:divide-white/5"
                >
                    <thead class="bg-slate-50/50 dark:bg-black/20">
                        <tr>
                            <th
                                class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest"
                            >
                                {{ __("date_time") }}
                            </th>
                            <th
                                class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest"
                            >
                                {{ __("customer") }}
                            </th>
                            <th
                                class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest"
                            >
                                {{ __("barber") }}
                            </th>
                            <th
                                class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest"
                            >
                                {{ __("status") }}
                            </th>
                            <th
                                class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest"
                            >
                                {{ __("payment_status") }}
                            </th>
                            <th
                                class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest"
                            >
                                {{ __("price") }}
                            </th>
                            <th
                                class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest"
                            >
                                {{ __("actions") }}
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-100 dark:divide-white/5"
                    >
                        <tr
                            v-for="appt in appointments.data"
                            :key="appt.id"
                            class="group hover:bg-slate-50/80 dark:hover:bg-white/5 transition-all"
                        >
                            <td class="px-8 py-6">
                                <span
                                    class="text-sm font-bold text-slate-600 dark:text-slate-400"
                                    >{{ formatDateTime(appt.start_time) }}</span
                                >
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-black text-slate-900 dark:text-white"
                                        >{{
                                            appt.customer?.name || __("walk_in")
                                        }}</span
                                    >
                                    <span
                                        class="text-[10px] font-bold text-slate-400"
                                        >{{ appt.customer?.phone || "-" }}</span
                                    >
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-8 w-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center text-[10px] font-black"
                                    >
                                        {{ appt.barber?.name.charAt(0) }}
                                    </div>
                                    <span
                                        class="text-sm font-bold text-slate-700 dark:text-slate-300"
                                        >{{ appt.barber?.name }}</span
                                    >
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span
                                    :class="[
                                        'px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest border',
                                        getStatusClass(appt.status),
                                    ]"
                                >
                                    {{ __(appt.status) }}
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <span
                                    :class="[
                                        'px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest border',
                                        getPaymentStatusClass(
                                            appt.payment_status,
                                        ),
                                    ]"
                                >
                                    {{ __(appt.payment_status) }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <span
                                    class="font-black text-slate-900 dark:text-white"
                                    >{{
                                        formatCurrency(appt.total_price)
                                    }}</span
                                >
                            </td>
                            <td class="px-8 py-6 text-right">
                                <button
                                    @click="editAppointment(appt)"
                                    class="p-2 rounded-lg bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-amber-500 transition-colors"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Mobile Card List -->
                <div class="lg:hidden space-y-4">
                    <div
                        v-for="appt in appointments.data"
                        :key="appt.id"
                        class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-white/5 premium-shadow"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-10 w-10 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-xs font-black"
                                >
                                    {{ appt.barber?.name.charAt(0) }}
                                </div>
                                <div class="flex flex-col">
                                    <h4
                                        class="text-sm font-black text-slate-900 dark:text-white truncate max-w-[150px]"
                                    >
                                        {{
                                            appt.customer?.name || __("walk_in")
                                        }}
                                    </h4>
                                    <p
                                        class="text-[10px] font-bold text-slate-400"
                                    >
                                        {{ formatDateTime(appt.start_time) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span
                                    :class="[
                                        'px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest border',
                                        getStatusClass(appt.status),
                                    ]"
                                >
                                    {{ __(appt.status) }}
                                </span>
                                <span
                                    :class="[
                                        'px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest border',
                                        getPaymentStatusClass(
                                            appt.payment_status,
                                        ),
                                    ]"
                                >
                                    {{ __(appt.payment_status) }}
                                </span>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-white/5"
                        >
                            <div class="flex flex-col">
                                <p
                                    class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5"
                                >
                                    {{ __("price") }}
                                </p>
                                <p
                                    class="text-sm font-black text-slate-900 dark:text-white"
                                >
                                    {{ formatCurrency(appt.total_price) }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    @click="editAppointment(appt)"
                                    class="p-3 rounded-xl bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-amber-500 transition-colors"
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
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="appointments.data.length === 0"
                    class="py-20 text-center bg-white dark:bg-slate-900 rounded-[2.5rem]"
                >
                    <div class="flex flex-col items-center">
                        <div
                            class="p-6 rounded-3xl bg-slate-50 dark:bg-white/5 text-slate-300 dark:text-slate-700 mb-4"
                        >
                            <svg
                                class="w-12 h-12"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                        <h3
                            class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight"
                        >
                            {{ __("no_appointments_found") }}
                        </h3>
                        <p class="text-sm text-slate-500 font-medium mt-1">
                            {{ __("try_different_filters") }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <Pagination :links="appointments.links" />

            <BookingModal
                :show="isEditModalOpen"
                :services="services"
                :barbers="barbers"
                :initial-appointment="selectedAppt"
                @close="
                    isEditModalOpen = false;
                    selectedAppt = null;
                "
                @appointment-updated="applyFilters"
            />
        </div>
    </AuthenticatedLayout>
</template>
