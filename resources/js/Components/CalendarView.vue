<script setup>
import { ref, onMounted, computed, watch } from "vue";
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import listPlugin from "@fullcalendar/list";
import axios from "axios";
import BookingModal from "@/Components/BookingModal.vue";
import { trans } from "@/lang";
import { usePage, Link } from "@inertiajs/vue3";

const props = defineProps({
    services: Array,
    barbers: {
        type: Array,
        default: () => [],
    },
    isBarberView: {
        type: Boolean,
        default: false,
    },
});

const formatSimpleTime = (dateStr) => {
    if (!dateStr) return "";
    // Check if it's already a JS Date or a string
    let timeStr = "";
    if (typeof dateStr === "string") {
        timeStr = dateStr.includes("T")
            ? dateStr.split("T")[1]
            : dateStr.includes(" ")
              ? dateStr.split(" ")[1]
              : dateStr;
    } else if (dateStr instanceof Date) {
        timeStr = `${String(dateStr.getHours()).padStart(2, "0")}:${String(dateStr.getMinutes()).padStart(2, "0")}`;
    }
    return timeStr.substring(0, 5);
};

const page = usePage();
const getLocale = () => page.props.locale || "en";

const emit = defineEmits(["event-updated"]);

const calendarRef = ref(null);
const currentView = ref("");
const currentTitle = ref("");
const isBookingModalOpen = ref(false);
const selectedDate = ref(null);
const selectedEvent = ref(null);
const selectedBarberId = ref(
    !props.isBarberView && props.barbers && props.barbers.length > 0
        ? props.barbers[0].id
        : "",
);

const eventSource = computed(() => {
    const baseUrl = props.isBarberView
        ? route("barber.appointments.events")
        : route("owner.appointments.events");

    if (!props.isBarberView && selectedBarberId.value) {
        return `${baseUrl}?barber_id=${selectedBarberId.value}`;
    }

    return baseUrl;
});

const refreshCalendar = () => {
    if (calendarRef.value) {
        calendarRef.value.getApi().refetchEvents();
    } else {
        window.location.reload();
    }
};

watch(selectedBarberId, () => {
    refreshCalendar();
});

const updateAppointmentTime = (event, revertCallback) => {
    const routeName = props.isBarberView
        ? "barber.appointments.update"
        : "owner.appointments.update";

    const formatLocal = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");
        const hour = String(date.getHours()).padStart(2, "0");
        const minute = String(date.getMinutes()).padStart(2, "0");
        return `${year}-${month}-${day} ${hour}:${minute}:00`;
    };

    axios
        .patch(route(routeName, event.id), {
            start: formatLocal(event.start),
            end: formatLocal(event.end),
        })
        .catch((error) => {
            alert(
                trans("failed_move_appointment", getLocale()) +
                    (error.response?.data?.message || error.message),
            );
            if (revertCallback) revertCallback();
        });
};

const closeBookingModal = () => {
    isBookingModalOpen.value = false;
    selectedDate.value = null;
    selectedEvent.value = null;
    refreshCalendar();
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

const lastClickDate = ref(null);
const lastClickTime = ref(0);

const handleDateClick = (arg) => {
    if (isMobile.value) {
        const now = Date.now();
        const clickedTime = arg.date.getTime();

        if (
            lastClickDate.value === clickedTime &&
            now - lastClickTime.value < 500
        ) {
            selectedDate.value = {
                start: arg.dateStr,
                allDay: arg.allDay,
            };
            isBookingModalOpen.value = true;
            lastClickDate.value = null;
            lastClickTime.value = 0;
        } else {
            lastClickDate.value = clickedTime;
            lastClickTime.value = now;
            // Feedback for mobile users
            const el = arg.dayEl;
            el.classList.add("bg-indigo-500/10");
            setTimeout(() => el.classList.remove("bg-indigo-500/10"), 500);
        }
    } else {
        selectedDate.value = {
            start: arg.dateStr,
            allDay: arg.allDay,
        };
        isBookingModalOpen.value = true;
    }
};

const handleDateSelect = (selectInfo) => {
    if (isMobile.value) {
        // For mobile selection, we still allow it but maybe with a confirmation or just stick to dateClick
        // Given the "double click" requirement, dateClick is more intuitive for single slots
        return;
    }
    selectedDate.value = {
        start: selectInfo.startStr,
        end: selectInfo.endStr,
        allDay: selectInfo.allDay,
    };
    selectedEvent.value = null;
    isBookingModalOpen.value = true;
};

const openBookingModal = () => {
    const now = new Date();
    // Round to next 30 mins
    now.setMinutes(now.getMinutes() + (30 - (now.getMinutes() % 30)));

    // Format to local ISO-like string: YYYY-MM-DDTHH:mm
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, "0");
    const day = String(now.getDate()).padStart(2, "0");
    const hours = String(now.getHours()).padStart(2, "0");
    const mins = String(now.getMinutes()).padStart(2, "0");

    selectedDate.value = {
        start: `${year}-${month}-${day}T${hours}:${mins}`,
        allDay: false,
    };
    selectedEvent.value = null;
    isBookingModalOpen.value = true;
};

const handleEventClick = (clickInfo) => {
    selectedEvent.value = {
        id: clickInfo.event.id,
        title: clickInfo.event.title,
        start: clickInfo.event.start,
        end: clickInfo.event.end,
        extendedProps: clickInfo.event.extendedProps,
    };
    isBookingModalOpen.value = true;
};

const handleEventDrop = (info) => {
    updateAppointmentTime(info.event, info.revert);
};

const handleEventResize = (info) => {
    updateAppointmentTime(info.event, info.revert);
};

const getStatusClass = (status, paymentStatus = "paid") => {
    switch (status) {
        case "completed":
            return "bg-emerald-500/10 text-emerald-600 border-emerald-500/20";
        case "cancelled":
            return "bg-rose-500/10 text-rose-600 border-rose-500/20";
        default:
            return "bg-indigo-500/10 text-indigo-600 border-indigo-500/20";
    }
};

const isMobile = ref(false);

const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
};

onMounted(() => {
    checkMobile();
    window.addEventListener("resize", checkMobile);
    // Initialize currentView based on mobilty
    currentView.value = isMobile.value ? "timeGridDay" : "timeGridWeek";
});

const handleDatesSet = (dateInfo) => {
    currentView.value = dateInfo.view.type;
    currentTitle.value = dateInfo.view.title;
};

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
    initialView: isMobile.value ? "timeGridDay" : "timeGridWeek",
    locale: getLocale() === "ar" ? "ar-dz" : getLocale(),
    headerToolbar: false,
    editable: true,
    selectable: !isMobile.value, // Disable drag selection on mobile to avoid conflicts with scrolling/double-tap
    selectMirror: true,
    dayMaxEvents: true,
    weekends: true,
    events: eventSource.value,
    select: handleDateSelect,
    dateClick: handleDateClick,
    eventClick: handleEventClick,
    eventDrop: handleEventDrop,
    eventResize: handleEventResize,
    datesSet: handleDatesSet,
    selectLongPressDelay: 0,
    slotMinTime: "08:00:00",
    slotMaxTime: "21:00:00",
    allDaySlot: false,
    height: "auto",
    contentHeight: isMobile.value ? "auto" : 700,
    nowIndicator: true,
    slotLabelFormat: {
        hour: "numeric",
        minute: "2-digit",
        omitZeroMinute: false,
        meridiem: "short",
    },
    eventContent: (arg) => {
        const { event } = arg;
        const status = event.extendedProps.status;
        const paymentStatus = event.extendedProps.payment_status;
        const customer = event.extendedProps.customer_name;
        const services = event.extendedProps.services;
        const price = event.extendedProps.total_price;

        const isUnpaid = paymentStatus !== "paid";
        const isCompleted = status === "completed";
        const isCancelled = status === "cancelled";

        // Website Design Tokens - Simple Solid Version
        const theme = {
            completed: {
                bg: "bg-emerald-500",
                text: "text-white",
                sub: "text-emerald-100/80",
                icon: "bg-white/20",
            },
            cancelled: {
                bg: "bg-rose-500",
                text: "text-white",
                sub: "text-rose-100/80",
                icon: "bg-white/20",
            },
            scheduled: {
                bg: "bg-indigo-600",
                text: "text-white",
                sub: "text-indigo-100/80",
                icon: "bg-white/20",
            },
        };

        const active = isCompleted
            ? theme.completed
            : isCancelled
              ? theme.cancelled
              : theme.scheduled;

        if (arg.view.type === "listWeek") {
            return {
                html: `
                    <div class="flex items-center justify-between w-full py-2">
                        <div class="flex items-center gap-4">
                            <div class="w-3 h-3 rounded-full ${active.bg} shadow-sm"></div>
                            <div class="flex flex-col">
                                <span class="font-black text-sm text-slate-900 dark:text-white leading-none mb-1">${customer}</span>
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">${Array.isArray(services) ? services.map((s) => s.name).join(", ") : services}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            ${isCompleted && isUnpaid ? `<span class="px-2 py-0.5 rounded text-[8px] font-black uppercase bg-rose-500 text-white leading-none">Flagged</span>` : ""}
                            <span class="text-xs font-black text-slate-900 dark:text-white">${formatCurrency(price)}</span>
                        </div>
                    </div>
                `,
            };
        }

        return {
            html: `
                <div class="flex flex-col h-full rounded-xl p-2.5 ${active.bg} ${active.text} shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden group">
                    <div class="flex items-start justify-between gap-1">
                        <div class="flex flex-col min-w-0 flex-1">
                            <span class="text-[10px] font-black uppercase tracking-tight truncate leading-none">${customer}</span>
                            <span class="text-[8px] font-bold ${active.sub} truncate mt-1 leading-none italic">${Array.isArray(services) ? services.map((s) => s.name).join(", ") : services}</span>
                        </div>
                        
                        ${
                            isCompleted && isUnpaid
                                ? `
                            <div class="shrink-0 flex items-center justify-center w-4 h-4 rounded-lg bg-white text-rose-600 shadow-sm animate-pulse">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            </div>
                        `
                                : ""
                        }
                    </div>

                    <div class="mt-auto pt-2 flex items-center justify-between">
                         <span class="text-[10px] font-black px-1.5 py-0.5 rounded bg-black/10 uppercase tracking-tighter">${formatCurrency(price)}</span>
                         ${
                             isCompleted
                                 ? `
                            <div class="p-0.5 rounded-full bg-white/20">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                            </div>
                         `
                                 : ""
                         }
                    </div>
                </div>
            `,
        };
    },
}));

const changeView = (view) => {
    calendarRef.value.getApi().changeView(view);
};

const goPrev = () => calendarRef.value.getApi().prev();
const goNext = () => calendarRef.value.getApi().next();
const goToday = () => calendarRef.value.getApi().today();

defineExpose({ refreshCalendar });
</script>

<template>
    <div class="space-y-4 sm:space-y-6">
        <!-- Premium Calendar Header / Toolbar -->
        <div
            class="flex flex-col xl:flex-row gap-4 sm:gap-6 items-center justify-between p-4 sm:p-8 rounded-[2rem] sm:rounded-[3rem] bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 premium-shadow max-w-7xl mx-auto"
        >
            <!-- Left: View Controls -->
            <div
                class="flex items-center gap-1 p-1 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 w-full xl:w-auto overflow-x-auto no-scrollbar order-last xl:order-first"
            >
                <Link
                    :href="route('owner.calendar.daily')"
                    class="px-3 sm:px-5 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap text-amber-500 hover:bg-amber-500/10 mr-2 border border-amber-500/20"
                >
                    {{ __("daily_planning") }}
                </Link>
                <div
                    class="h-6 w-px bg-slate-200 dark:bg-white/10 mx-2 hidden sm:block"
                ></div>
                <button
                    @click="changeView('timeGridWeek')"
                    class="flex-1 xl:flex-none px-3 sm:px-5 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap"
                    :class="
                        currentView === 'timeGridWeek'
                            ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20'
                            : 'text-slate-500 hover:text-indigo-600'
                    "
                >
                    {{ __("week") }}
                </button>
                <button
                    @click="changeView('timeGridDay')"
                    class="flex-1 xl:flex-none px-3 sm:px-5 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap"
                    :class="
                        currentView === 'timeGridDay'
                            ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20'
                            : 'text-slate-500 hover:text-indigo-600'
                    "
                >
                    {{ __("day") }}
                </button>
                <button
                    @click="changeView('listWeek')"
                    class="flex-1 xl:flex-none px-3 sm:px-5 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap"
                    :class="
                        currentView === 'listWeek'
                            ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20'
                            : 'text-slate-500 hover:text-indigo-600'
                    "
                >
                    {{ __("list") }}
                </button>
            </div>

            <!-- Center: Navigation -->
            <div
                class="flex items-center justify-between xl:justify-center gap-2 sm:gap-5 w-full xl:w-auto"
            >
                <button
                    @click="goPrev"
                    class="p-2 sm:p-3 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 text-slate-500 hover:text-indigo-600 transition-all shadow-sm active:scale-90"
                >
                    <svg
                        class="w-4 h-4 sm:w-5 sm:h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="3"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                </button>
                <div class="text-center min-w-[140px] sm:min-w-[200px]">
                    <h3
                        class="text-xs sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight"
                    >
                        {{ currentTitle }}
                    </h3>
                    <div class="flex flex-col items-center">
                        <button
                            @click="goToday"
                            class="text-[8px] sm:text-[10px] font-black uppercase tracking-[0.3em] text-amber-500 hover:text-amber-600 transition-colors mt-0.5"
                        >
                            {{ __("today") }}
                        </button>
                        <span
                            v-if="isMobile"
                            class="text-[7px] font-bold uppercase tracking-tight text-slate-400 mt-1"
                        >
                            Double clic pour réserver
                        </span>
                    </div>
                </div>
                <button
                    @click="goNext"
                    class="p-2 sm:p-3 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 text-slate-500 hover:text-indigo-600 transition-all shadow-sm active:scale-90"
                >
                    <svg
                        class="w-4 h-4 sm:w-5 sm:h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="3"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </button>
            </div>

            <!-- Right: Filter (Owner) or Branding -->
            <div
                v-if="!isBarberView"
                class="w-full xl:w-auto flex-1 xl:max-w-xs flex items-center gap-3 sm:gap-4 p-2 px-3 sm:p-2.5 sm:px-4 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 shadow-sm"
            >
                <div
                    class="p-1.5 sm:p-2 rounded-lg bg-indigo-500/10 text-indigo-600 shrink-0"
                >
                    <svg
                        class="w-3 h-3 sm:w-4 sm:h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                        />
                    </svg>
                </div>
                <select
                    v-model="selectedBarberId"
                    class="flex-1 border-none bg-transparent font-bold text-[10px] sm:text-xs text-slate-900 dark:text-white focus:ring-0 p-0"
                >
                    <option
                        v-for="barber in barbers"
                        :key="barber.id"
                        :value="barber.id"
                    >
                        {{ barber.name }}
                    </option>
                </select>
            </div>
            <div
                v-else
                class="flex items-center gap-2 sm:gap-3 px-2 sm:px-6 w-full xl:w-auto justify-center xl:justify-start"
            >
                <div
                    class="h-1.5 w-1.5 sm:h-2 sm:w-2 rounded-full bg-emerald-500 animate-pulse"
                ></div>
                <span
                    class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 leading-none"
                    >{{ __("live_calendar") }}</span
                >
            </div>
        </div>

        <!-- Main Calendar Glass Card -->
        <div
            v-if="
                props.isBarberView ||
                (props.barbers && props.barbers.length > 0)
            "
            class="premium-shadow rounded-[2.5rem] p-4 lg:p-10 bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 mx-auto max-w-7xl"
        >
            <div
                class="calendar-container overflow-hidden rounded-[2rem] border border-slate-100 dark:border-white/5"
            >
                <FullCalendar ref="calendarRef" :options="calendarOptions" />
            </div>
        </div>

        <div
            v-else
            class="premium-shadow rounded-[2.5rem] py-20 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 mx-auto max-w-7xl mt-6"
        >
            <div class="flex flex-col items-center">
                <div
                    class="p-6 rounded-3xl bg-amber-50 dark:bg-amber-500/10 text-amber-500 mb-4"
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
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        />
                    </svg>
                </div>
                <h3
                    class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight"
                >
                    {{ __("no_barbers_found") }}
                </h3>
                <p class="text-sm border-0 text-slate-500 font-medium mt-1">
                    {{ __("please_insert_barber") }}
                </p>
                <Link
                    :href="route('owner.barbers.index')"
                    class="mt-6 px-6 py-3 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/20"
                >
                    {{ __("add_barber") }}
                </Link>
            </div>
        </div>

        <!-- Booking Modal -->
        <BookingModal
            :show="isBookingModalOpen"
            :services="services"
            :barbers="barbers"
            :initial-date="selectedDate"
            :initial-appointment="selectedEvent"
            :initial-barber-id="selectedBarberId"
            :is-barber-view="isBarberView"
            @close="closeBookingModal"
            @appointment-created="refreshCalendar"
            @appointment-updated="refreshCalendar"
        />

        <!-- Mobile Floating Action Button -->
        <button
            v-if="isMobile"
            @click="openBookingModal"
            class="fab-button z-[60]"
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
            <div class="fab-button-ping"></div>
        </button>
    </div>
</template>

<style lang="postcss">
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.fc {
    --fc-border-color: rgba(226, 232, 240, 0.5);
    --fc-today-bg-color: rgba(245, 158, 11, 0.05);
    --fc-button-bg-color: transparent;
    --fc-button-border-color: rgba(226, 232, 240, 0.8);
    --fc-button-text-color: #64748b;
    --fc-button-hover-bg-color: #f1f5f9;
    --fc-button-hover-border-color: #cbd5e1;
    --fc-button-active-bg-color: #e2e8f0;
    --fc-button-active-border-color: #94a3b8;
    --fc-list-event-hover-bg-color: rgba(245, 158, 11, 0.05);
}

.dark .fc {
    --fc-border-color: rgba(255, 255, 255, 0.05);
    --fc-today-bg-color: rgba(245, 158, 11, 0.05);
    --fc-button-text-color: #94a3b8;
    --fc-button-hover-bg-color: rgba(255, 255, 255, 0.05);
    --fc-button-active-bg-color: rgba(255, 255, 255, 0.1);
}

.fc .fc-toolbar-title {
    @apply text-xl font-black text-slate-900 dark:text-white tracking-tight;
}

.fc .fc-button {
    @apply rounded-xl px-4 py-2 text-xs font-black uppercase tracking-widest transition-all duration-200 border-2;
}

.fc-list-day-cushion {
    @apply bg-slate-50 dark:bg-white/5 !py-4;
}

.fc-list-event {
    @apply !bg-transparent hover:!bg-amber-500/5 transition-colors cursor-pointer;
}

.fc-list-event-time,
.fc-list-event-title {
    @apply !border-none text-sm font-bold;
}

.fc-list-event-graphic {
    @apply !hidden;
}

.fc .fc-timegrid-slot {
    height: 4em !important;
}

@media (max-width: 640px) {
    .fc .fc-timegrid-slot {
        height: 3em !important;
    }
    .fc .fc-col-header-cell-cushion {
        @apply py-2 !text-[9px];
    }
}

.fc .fc-event {
    @apply border-none shadow-none bg-transparent !important;
    overflow: visible !important;
}

.fc-v-event .fc-event-main {
    @apply p-0 overflow-visible;
}

.fc .fc-col-header-cell-cushion {
    @apply text-[11px] font-black uppercase tracking-[0.2em] text-slate-400 py-6;
}

.fc .fc-timegrid-slot-label-cushion {
    @apply text-[10px] font-bold text-slate-300 uppercase;
}
</style>
