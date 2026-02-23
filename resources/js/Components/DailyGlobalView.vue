<script setup>
import { ref, onMounted, computed, watch, onUnmounted } from "vue";
import axios from "axios";
import { trans } from "@/lang";
import { usePage } from "@inertiajs/vue3";
import BookingModal from "@/Components/BookingModal.vue";
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    barbers: Array,
    services: Array,
});

const page = usePage();
const getLocale = () => page.props.locale || "en";
const __ = (key) => trans(key, getLocale());

const currentDate = ref(new Date());
const appointments = ref([]);
const isLoading = ref(false);

const isBookingModalOpen = ref(false);
const isViewModalOpen = ref(false);
const selectedDate = ref(null);
const selectedEvent = ref(null);
const selectedBarberForBooking = ref(null);

const checkoutForm = ref({
    price_override: 0,
    payment_status: "paid",
});

// View State
const viewMode = ref("horizontal"); // 'horizontal' (Barbers as columns) or 'vertical' (Barbers as rows)
const isFlexible = ref(true); // Whether to use percentages/flex-1 for columns

// Time range: 08:00 to 21:00
const startHour = 8;
const endHour = 21;
const totalHours = endHour - startHour;
const hourHeight = 48; // px per hour in horizontal, or width unit in vertical
const hourWidth = 120; // px per hour in vertical view

const timeSlots = computed(() => {
    const slots = [];
    for (let i = startHour; i <= endHour; i++) {
        slots.push(`${i.toString().padStart(2, "0")}:00`);
    }
    return slots;
});

const fetchAppointments = async () => {
    isLoading.value = true;
    const d = currentDate.value;
    const dateStr = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
    const start = `${dateStr}T00:00:00`;
    const end = `${dateStr}T23:59:59`;

    try {
        const response = await axios.get(route("owner.appointments.events"), {
            params: { start, end },
        });
        appointments.value = response.data;
    } catch (error) {
        console.error("Error fetching appointments:", error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchAppointments();
});

watch(currentDate, () => {
    fetchAppointments();
});

const prevDay = () => {
    const d = new Date(currentDate.value);
    d.setDate(d.getDate() - 1);
    currentDate.value = d;
};

const nextDay = () => {
    const d = new Date(currentDate.value);
    d.setDate(d.getDate() + 1);
    currentDate.value = d;
};

const goToday = () => {
    currentDate.value = new Date();
};

const formatDayTitle = computed(() => {
    return currentDate.value.toLocaleDateString(getLocale(), {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    });
});

const getBarberAppointments = (barberId) => {
    return appointments.value.filter(
        (a) => a.extendedProps.barber_id === barberId,
    );
};

const getPosition = (startTime, endTime) => {
    const parseTime = (str) => {
        const t = str.includes("T") ? str.split("T")[1] : str.split(" ")[1];
        const [h, m] = t.split(":").map(Number);
        return { h, m };
    };

    const start = parseTime(startTime);
    const end = parseTime(endTime);

    const startMinutes = (start.h - startHour) * 60 + start.m;
    const durationMinutes = end.h * 60 + end.m - (start.h * 60 + start.m);

    if (viewMode.value === "horizontal") {
        return {
            top: `${(startMinutes / 60) * hourHeight}px`,
            height: `${(durationMinutes / 60) * hourHeight}px`,
        };
    } else {
        return {
            left: `${(startMinutes / 60) * hourWidth}px`,
            width: `${(durationMinutes / 60) * hourWidth}px`,
            top: "4px",
            bottom: "4px",
            height: "auto",
        };
    }
};

const toggleViewMode = () => {
    viewMode.value =
        viewMode.value === "horizontal" ? "vertical" : "horizontal";
};

const handleGridClick = (event, barberId, timeClicked = null) => {
    const rect = event.currentTarget.getBoundingClientRect();
    let hour, minute;
    let minutesTotal;

    if (viewMode.value === "horizontal") {
        const clientY =
            event.clientY || (event.touches ? event.touches[0].clientY : 0);
        const y = clientY - rect.top;
        minutesTotal = (y / hourHeight) * 60;
    } else {
        const clientX =
            event.clientX || (event.touches ? event.touches[0].clientX : 0);
        const x = clientX - rect.left;
        minutesTotal = (x / hourWidth) * 60;
    }

    // Round total minutes to nearest 15 to handle rollover (e.g. 55 -> 60 -> +1 hour)
    const roundedMinutesTotal = Math.round(minutesTotal / 15) * 15;
    hour = Math.floor(roundedMinutesTotal / 60) + startHour;
    minute = roundedMinutesTotal % 60;

    const d = currentDate.value;
    const dateStr = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
    const hourStr = hour.toString().padStart(2, "0");
    const minStr = minute.toString().padStart(2, "0");

    selectedDate.value = `${dateStr}T${hourStr}:${minStr}`;
    selectedBarberForBooking.value = barberId;
    isBookingModalOpen.value = true;
};

// Double-tap logic for mobile
const lastTapTime = ref(0);
const lastTapBarber = ref(null);

const handleGridTouch = (event, barberId) => {
    const now = Date.now();
    if (lastTapBarber.value === barberId && now - lastTapTime.value < 500) {
        handleGridClick(event, barberId);
        lastTapTime.value = 0;
        lastTapBarber.value = null;
    } else {
        lastTapTime.value = now;
        lastTapBarber.value = barberId;
        // Visual feedback
        const el = event.currentTarget;
        el.classList.add("bg-amber-500/5");
        setTimeout(() => el.classList.remove("bg-amber-500/5"), 500);
    }
};

const handleAppointmentClick = (appt) => {
    selectedEvent.value = appt;
    checkoutForm.value.price_override = appt.extendedProps.total_price;
    checkoutForm.value.payment_status =
        appt.extendedProps.payment_status === "unpaid"
            ? "paid"
            : appt.extendedProps.payment_status || "paid";
    isViewModalOpen.value = true;
};

const formatCurrency = (value) => {
    let locale = "en-US";
    if (getLocale() === "fr" || getLocale() === "ar") locale = "fr-FR";
    return new Intl.NumberFormat(locale, {
        style: "currency",
        currency: "USD",
        currencyDisplay: "narrowSymbol",
    }).format(value);
};

const closeBookingModal = () => {
    isBookingModalOpen.value = false;
    fetchAppointments();
};

const closeViewModal = () => {
    isViewModalOpen.value = false;
    selectedEvent.value = null;
};

const updateStatus = (status) => {
    if (!selectedEvent.value) return;

    if (status === "completed") {
        axios
            .patch(route("owner.appointments.update", selectedEvent.value.id), {
                status: "completed",
                payment_status: checkoutForm.value.payment_status,
                total_price: checkoutForm.value.price_override,
            })
            .then(() => {
                closeViewModal();
                fetchAppointments();
            });
        return;
    }

    if (!confirm(__("confirm_mark_as") + status + "?")) return;

    axios
        .patch(route("owner.appointments.update", selectedEvent.value.id), {
            status,
        })
        .then(() => {
            closeViewModal();
            fetchAppointments();
        });
};

const deleteAppointment = () => {
    if (!selectedEvent.value) return;
    if (!confirm(__("confirm_delete_appointment"))) return;

    axios
        .delete(route("owner.appointments.destroy", selectedEvent.value.id))
        .then(() => {
            closeViewModal();
            fetchAppointments();
        });
};

const handleEditClick = () => {
    isBookingModalOpen.value = true;
    closeViewModal();
};

const getStatusColorClass = (status) => {
    switch (status) {
        case "completed":
            return "bg-emerald-500";
        case "cancelled":
            return "bg-rose-500";
        default:
            return "bg-indigo-600";
    }
};

const formatSimpleTime = (dateStr) => {
    if (!dateStr) return "";
    const t = dateStr.includes("T")
        ? dateStr.split("T")[1]
        : dateStr.split(" ")[1];
    return t.substring(0, 5);
};
</script>

<template>
    <div
        class="flex flex-col h-full bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-white/5 overflow-hidden premium-shadow"
    >
        <!-- Header -->
        <div
            class="flex flex-col sm:flex-row items-center justify-between p-4 border-b border-slate-100 dark:border-white/5 gap-3"
        >
            <div class="flex items-center gap-3">
                <button
                    @click="prevDay"
                    class="p-1.5 rounded-lg border border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-white/5 transition-all active:scale-95"
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
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                </button>
                <div class="text-center">
                    <h2
                        class="text-sm sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight"
                    >
                        {{ formatDayTitle }}
                    </h2>
                    <button
                        @click="goToday"
                        class="text-[8px] font-black uppercase tracking-widest text-amber-500 hover:text-amber-600 block leading-none"
                    >
                        {{ __("today") }}
                    </button>
                </div>
                <button
                    @click="nextDay"
                    class="p-1.5 rounded-lg border border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-white/5 transition-all active:scale-95"
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
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </button>
            </div>

            <div class="flex items-center gap-2">
                <!-- View Mode Toggle -->
                <button
                    @click="toggleViewMode"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-white/5 transition-all active:scale-95 mr-2"
                >
                    <svg
                        v-if="viewMode === 'vertical'"
                        class="w-4 h-4 text-amber-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                    <svg
                        v-else
                        class="w-4 h-4 text-amber-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2m0 10V7"
                        />
                    </svg>
                    <span
                        class="text-[9px] font-black uppercase tracking-widest text-slate-600 dark:text-slate-400"
                    >
                        {{
                            viewMode === "horizontal"
                                ? __("vertical")
                                : __("horizontal")
                        }}
                    </span>
                </button>

                <div class="flex items-center gap-2 mr-4">
                    <div class="w-3 h-3 rounded-full bg-indigo-600"></div>
                    <span>{{ __("scheduled") }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                    <span>{{ __("completed") }}</span>
                </div>
            </div>
        </div>

        <!-- Desktop Grid -->
        <div class="flex-1 overflow-auto relative custom-scrollbar">
            <!-- HORIZONTAL MODE (Old view but flexible) -->
            <div v-if="viewMode === 'horizontal'" class="flex min-w-max h-full">
                <!-- Time Column -->
                <div
                    class="w-16 shrink-0 bg-slate-50/50 dark:bg-black/20 border-r border-slate-100 dark:border-white/5 sticky left-0 z-20"
                >
                    <div
                        class="h-10 border-b border-slate-100 dark:border-white/5 bg-white dark:bg-slate-900"
                    ></div>
                    <!-- Corner -->
                    <div
                        v-for="time in timeSlots"
                        :key="time"
                        class="relative"
                        :style="{ height: hourHeight + 'px' }"
                    >
                        <span
                            class="absolute -top-2 left-0 w-full text-center text-[9px] font-black text-slate-400 uppercase leading-none"
                            >{{ time }}</span
                        >
                    </div>
                </div>

                <!-- Barber Columns -->
                <div
                    v-for="barber in barbers"
                    :key="barber.id"
                    class="shrink-0 border-r border-slate-100 dark:border-white/5 last:border-0 relative grid-column transition-all"
                    :class="
                        isFlexible ? 'flex-1 min-w-[160px]' : 'w-40 sm:w-52'
                    "
                >
                    <!-- Barber Header -->
                    <div
                        class="h-10 flex items-center gap-2 px-3 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-100 dark:border-white/5 sticky top-0 z-10"
                    >
                        <div
                            class="w-6 h-6 rounded-lg bg-amber-500 text-slate-900 flex items-center justify-center font-black shadow-md shadow-amber-500/20 text-[10px] shrink-0"
                        >
                            {{ barber.name.charAt(0) }}
                        </div>
                        <div class="min-w-0">
                            <p
                                class="text-[10px] font-black text-slate-900 dark:text-white truncate leading-tight"
                            >
                                {{ barber.name }}
                            </p>
                        </div>
                    </div>

                    <!-- Column Grid Body -->
                    <div
                        class="relative bg-transparent cursor-crosshair group transition-colors duration-200"
                        :style="{
                            height: (totalHours + 1) * hourHeight + 'px',
                        }"
                        @click="handleGridClick($event, barber.id)"
                        @touchstart="handleGridTouch($event, barber.id)"
                    >
                        <!-- Horizontal Hours Lines -->
                        <div
                            v-for="i in totalHours + 1"
                            :key="i"
                            class="absolute left-0 right-0 border-b border-slate-50 dark:border-white/5 pointer-events-none"
                            :style="{
                                top: (i - 1) * hourHeight + 'px',
                                height: '1px',
                            }"
                        ></div>

                        <!-- Appointments -->
                        <div
                            v-for="appt in getBarberAppointments(barber.id)"
                            :key="appt.id"
                            @click.stop="handleAppointmentClick(appt)"
                            class="absolute left-1 right-1 rounded-lg p-1 shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer overflow-hidden group/appt z-10"
                            :class="
                                getStatusColorClass(appt.extendedProps.status)
                            "
                            :style="getPosition(appt.start, appt.end)"
                        >
                            <div
                                class="flex flex-col h-full text-white leading-none"
                            >
                                <span
                                    class="text-[8px] font-black uppercase truncate mb-0.5"
                                    >{{
                                        appt.extendedProps.customer_name
                                    }}</span
                                >
                                <span
                                    class="text-[7px] font-bold opacity-80 leading-none truncate block"
                                >
                                    {{
                                        appt.extendedProps.services
                                            .map((s) => s.name)
                                            .join(", ")
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VERTICAL MODE (New requested view) -->
            <div v-else class="flex flex-col min-w-max h-full">
                <!-- Time Row Header -->
                <div class="flex sticky top-0 z-20 bg-white dark:bg-slate-900">
                    <div
                        class="w-32 shrink-0 bg-white/95 dark:bg-slate-900/95 border-b border-r border-slate-100 dark:border-white/5 sticky left-0 z-30 h-10 backdrop-blur-md shadow-[4px_0_24px_-12px_rgba(0,0,0,0.1)] dark:shadow-[4px_0_24px_-12px_rgba(255,255,255,0.05)]"
                    ></div>
                    <!-- Corner -->
                    <div
                        v-for="time in timeSlots"
                        :key="time"
                        class="bg-white/95 dark:bg-slate-900/95 border-b border-slate-100 dark:border-white/5 relative shrink-0 backdrop-blur-md"
                        :style="{ width: hourWidth + 'px', height: '40px' }"
                    >
                        <span
                            class="absolute top-1/2 left-0 -translate-y-1/2 w-full text-center text-[9px] font-black text-slate-400 uppercase tracking-widest"
                            >{{ time }}</span
                        >
                        <!-- Vertical grid line -->
                        <div
                            class="absolute right-0 top-0 bottom-0 w-px bg-slate-100 dark:bg-white/5 pointer-events-none h-screen"
                        ></div>
                    </div>
                </div>

                <!-- Barber Rows -->
                <div
                    v-for="barber in barbers"
                    :key="barber.id"
                    class="flex border-b border-slate-100 dark:border-white/5 relative group/row overflow-visible"
                >
                    <!-- Barber Lead -->
                    <div
                        class="w-32 shrink-0 bg-white/95 dark:bg-slate-900/95 border-r border-slate-100 dark:border-white/5 sticky left-0 z-10 flex items-center px-3 h-14 backdrop-blur-sm shadow-[4px_0_24px_-12px_rgba(0,0,0,0.1)] dark:shadow-[4px_0_24px_-12px_rgba(255,255,255,0.05)]"
                    >
                        <div
                            class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-black text-[10px] mr-2 shrink-0 shadow-sm"
                        >
                            {{ barber.name.charAt(0) }}
                        </div>
                        <p
                            class="text-[10px] font-black text-slate-900 dark:text-white truncate"
                        >
                            {{ barber.name }}
                        </p>
                    </div>

                    <!-- Row Grid Body -->
                    <div
                        class="relative bg-transparent cursor-crosshair h-14 transition-all duration-200 hover:bg-slate-50/50 dark:hover:bg-white/5"
                        :style="{ width: (totalHours + 1) * hourWidth + 'px' }"
                        @click="handleGridClick($event, barber.id)"
                        @touchstart="handleGridTouch($event, barber.id)"
                    >
                        <!-- Vertical Grid Lines for alignment -->
                        <div
                            v-for="i in totalHours + 1"
                            :key="i"
                            class="absolute top-0 bottom-0 border-r border-slate-50 dark:border-white/5 pointer-events-none"
                            :style="{
                                left: (i - 1) * hourWidth + 'px',
                                width: '1px',
                            }"
                        ></div>

                        <!-- Appointments -->
                        <div
                            v-for="appt in getBarberAppointments(barber.id)"
                            :key="appt.id"
                            @click.stop="handleAppointmentClick(appt)"
                            class="absolute rounded-lg p-1.5 shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer overflow-hidden z-20 group/appt"
                            :class="
                                getStatusColorClass(appt.extendedProps.status)
                            "
                            :style="getPosition(appt.start, appt.end)"
                        >
                            <div
                                class="flex flex-col h-full text-white justify-center"
                            >
                                <div
                                    class="flex items-center justify-between gap-2 overflow-hidden"
                                >
                                    <span
                                        class="text-[9px] font-black uppercase truncate"
                                        >{{
                                            appt.extendedProps.customer_name
                                        }}</span
                                    >
                                    <span
                                        class="text-[8px] font-black px-1 rounded bg-black/10 shrink-0"
                                        >{{
                                            formatCurrency(
                                                appt.extendedProps.total_price,
                                            )
                                        }}</span
                                    >
                                </div>
                                <span
                                    class="text-[7px] font-bold opacity-80 truncate block leading-tight"
                                >
                                    {{
                                        appt.extendedProps.services
                                            .map((s) => s.name)
                                            .join(", ")
                                    }}
                                </span>
                            </div>
                        </div>

                        <!-- Hover hint -->
                        <div
                            class="absolute inset-x-0 bottom-0 top-0 flex items-center justify-start pl-4 opacity-0 group-hover/row:opacity-100 pointer-events-none transition-opacity"
                        >
                            <span
                                class="text-[8px] font-black uppercase tracking-widest text-slate-300 dark:text-slate-700"
                                >{{ __("click_to_book") }}</span
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals (Copied logic from CalendarView for identical functionality) -->
        <Modal :show="isViewModalOpen" @close="closeViewModal">
            <div class="p-8" v-if="selectedEvent">
                <div class="flex items-center justify-between mb-8">
                    <h3
                        class="text-2xl font-black text-slate-900 dark:text-white items-center flex gap-3"
                    >
                        <div
                            class="p-2 rounded-xl bg-indigo-500/10 text-indigo-600"
                        >
                            <svg
                                class="w-6 h-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                        </div>
                        {{ __("appointment_details") }}
                    </h3>
                    <button
                        @click="closeViewModal"
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
                    <div class="grid grid-cols-2 gap-6">
                        <div
                            class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5"
                        >
                            <p
                                class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1"
                            >
                                {{ __("barber") }}
                            </p>
                            <p
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                {{ selectedEvent.extendedProps.barber_name }}
                            </p>
                        </div>
                        <div
                            class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5"
                        >
                            <p
                                class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1"
                            >
                                {{ __("customer") }}
                            </p>
                            <p
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                {{ selectedEvent.extendedProps.customer_name }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5"
                    >
                        <p
                            class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1"
                        >
                            {{ __("services") }}
                        </p>
                        <p
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            {{
                                selectedEvent.extendedProps.services
                                    .map((s) => s.name)
                                    .join(", ")
                            }}
                        </p>
                    </div>

                    <div class="flex items-center gap-6">
                        <div
                            class="flex-1 p-4 rounded-2xl bg-indigo-500/10 border border-indigo-500/20"
                        >
                            <p
                                class="text-[10px] font-black uppercase tracking-widest text-indigo-600 mb-1"
                            >
                                {{ __("scheduled_time") }}
                            </p>
                            <p
                                class="text-sm font-black text-indigo-700 dark:text-indigo-400"
                            >
                                {{ formatSimpleTime(selectedEvent.start) }} -
                                {{ formatSimpleTime(selectedEvent.end) }}
                            </p>
                        </div>
                        <div
                            class="p-4 px-6 rounded-2xl bg-slate-900 dark:bg-indigo-600 text-center text-white"
                        >
                            <p
                                class="text-[10px] font-black uppercase tracking-widest opacity-40 mb-1"
                            >
                                {{ __("price_label") }}
                            </p>
                            <p class="text-xl font-black">
                                {{
                                    formatCurrency(
                                        selectedEvent.extendedProps.total_price,
                                    )
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-8 pt-8 border-t border-slate-100 dark:border-white/5 grid grid-cols-2 gap-3"
                    >
                        <PrimaryButton
                            @click="updateStatus('completed')"
                            class="col-span-2 !bg-emerald-600 hover:!bg-emerald-700 !rounded-2xl !py-4 shadow-lg shadow-emerald-500/20"
                        >
                            {{
                                selectedEvent.extendedProps.status ===
                                "completed"
                                    ? __("update_payment")
                                    : __("finish_and_collect")
                            }}
                        </PrimaryButton>
                        <button
                            @click="deleteAppointment"
                            class="px-6 py-4 rounded-2xl bg-red-50 dark:bg-red-500/10 text-red-600 font-bold text-sm hover:bg-red-100 transition-colors col-span-2"
                        >
                            {{ __("delete") }}
                        </button>
                    </div>
                </div>
            </div>
        </Modal>

        <BookingModal
            :show="isBookingModalOpen"
            :services="services"
            :barbers="barbers"
            :initial-date="selectedDate"
            :initial-barber-id="selectedBarberForBooking"
            @close="closeBookingModal"
            @appointment-created="fetchAppointments"
        />
    </div>
</template>

<style scoped>
.min-w-max {
    min-width: max-content;
}
.cursor-crosshair {
    cursor: crosshair;
}

/* Custom Premium Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    height: 10px;
    width: 10px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    @apply bg-slate-50 dark:bg-black/20;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    @apply bg-slate-200 dark:bg-slate-700 rounded-full border-2 border-transparent bg-clip-padding hover:bg-slate-300 dark:hover:bg-slate-600;
}

/* For Firefox */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #e2e8f0 transparent;
}
.dark .custom-scrollbar {
    scrollbar-color: #334155 transparent;
}
</style>
