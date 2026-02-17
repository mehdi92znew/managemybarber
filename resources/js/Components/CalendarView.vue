<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import BookingModal from '@/Components/BookingModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { trans } from '@/lang';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    services: Array,
    barbers: {
        type: Array,
        default: () => []
    },
    isBarberView: {
        type: Boolean,
        default: false
    }
});

const page = usePage();
const getLocale = () => page.props.locale || 'en';

const emit = defineEmits(['event-updated']);

const calendarRef = ref(null);
const currentView = ref('');
const currentTitle = ref('');
const isBookingModalOpen = ref(false);
const selectedDate = ref(null);
const selectedEvent = ref(null);
const isViewModalOpen = ref(false);
const selectedBarberId = ref('');

const checkoutForm = ref({
    price_override: 0,
    payment_status: 'paid'
});

const eventSource = computed(() => {
    const baseUrl = props.isBarberView 
        ? route('barber.appointments.events') 
        : route('owner.appointments.events');
    
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
    const routeName = props.isBarberView ? 'barber.appointments.update' : 'owner.appointments.update';
    
    axios.patch(route(routeName, event.id), {
        start: event.start.toISOString(),
        end: event.end.toISOString(),
    })
    .catch(error => {
        alert(trans('failed_move_appointment', getLocale()) + (error.response?.data?.message || error.message));
        if (revertCallback) revertCallback();
    });
};

const closeBookingModal = () => {
    isBookingModalOpen.value = false;
    selectedDate.value = null;
    refreshCalendar();
};

const closeViewModal = () => {
    isViewModalOpen.value = false;
    selectedEvent.value = null;
};

const updateStatus = (status) => {
    if (!selectedEvent.value) return;
    
    // For 'completed', we now use the checkout flow
    if (status === 'completed') {
        const routeName = props.isBarberView ? 'barber.appointments.update' : 'owner.appointments.update';
        axios.patch(route(routeName, selectedEvent.value.id), {
            status: 'completed',
            payment_status: checkoutForm.value.payment_status,
            total_price: checkoutForm.value.price_override
        })
        .then(() => {
            closeViewModal();
            refreshCalendar();
        })
        .catch(() => {
            alert(trans('error_updating_status', getLocale()));
        });
        return;
    }

    if (!confirm(trans('confirm_mark_as', getLocale()) + trans(status, getLocale()) + '?')) return;

    const routeName = props.isBarberView ? 'barber.appointments.update' : 'owner.appointments.update';

    axios.patch(route(routeName, selectedEvent.value.id), {
        status: status
    })
    .then(() => {
        closeViewModal();
        refreshCalendar();
    })
    .catch(() => {
        alert(trans('error_updating_status', getLocale()));
    });
};

const deleteAppointment = () => {
    if (!selectedEvent.value) return;
    if (!confirm(trans('confirm_delete_appointment', getLocale()))) return;

    const routeName = props.isBarberView ? 'barber.appointments.destroy' : 'owner.appointments.destroy';

    axios.delete(route(routeName, selectedEvent.value.id))
    .then(() => {
        closeViewModal();
        refreshCalendar();
    })
    .catch((error) => {
        alert(trans('error_deleting_appointment', getLocale()) + (error.response?.data?.message || ''));
    });
};

const handleEditClick = () => {
    isBookingModalOpen.value = true;
    closeViewModal();
};

const formatCurrency = (value) => {
    let locale = 'en-US';
    if (getLocale() === 'fr' || getLocale() === 'ar') locale = 'fr-FR';
    return new Intl.NumberFormat(locale, { style: 'currency', currency: 'USD', currencyDisplay: 'narrowSymbol' }).format(value);
};

const handleDateClick = (arg) => {
    selectedDate.value = {
        start: arg.dateStr,
        allDay: arg.allDay
    };
    isBookingModalOpen.value = true;
};

const handleDateSelect = (selectInfo) => {
    selectedDate.value = {
        start: selectInfo.startStr,
        end: selectInfo.endStr,
        allDay: selectInfo.allDay
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
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const mins = String(now.getMinutes()).padStart(2, '0');
    
    selectedDate.value = {
        start: `${year}-${month}-${day}T${hours}:${mins}`,
        allDay: false
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
    checkoutForm.value.price_override = clickInfo.event.extendedProps.total_price;
    checkoutForm.value.payment_status = clickInfo.event.extendedProps.payment_status === 'unpaid' ? 'paid' : clickInfo.event.extendedProps.payment_status || 'paid';
    isViewModalOpen.value = true;
};

const handleEventDrop = (info) => {
    updateAppointmentTime(info.event, info.revert);
};

const handleEventResize = (info) => {
    updateAppointmentTime(info.event, info.revert);
};

const getStatusClass = (status, paymentStatus = 'paid') => {
    switch (status) {
        case 'completed': return 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20';
        case 'cancelled': return 'bg-rose-500/10 text-rose-600 border-rose-500/20';
        default: return 'bg-indigo-500/10 text-indigo-600 border-indigo-500/20';
    }
};

const isMobile = ref(false);

const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
    // Initialize currentView based on mobilty
    currentView.value = isMobile.value ? 'listWeek' : 'timeGridWeek';
});

const handleDatesSet = (dateInfo) => {
    currentView.value = dateInfo.view.type;
    currentTitle.value = dateInfo.view.title;
};

const calendarOptions = computed(() => ({
    plugins: [ dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin ],
    initialView: isMobile.value ? 'listWeek' : 'timeGridWeek',
    locale: getLocale() === 'ar' ? 'ar-dz' : getLocale(),
    headerToolbar: false,
    editable: true,
    selectable: true,
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
    slotMinTime: '08:00:00',
    slotMaxTime: '21:00:00',
    allDaySlot: false,
    height: 'auto',
    contentHeight: isMobile.value ? 'auto' : 700,
    nowIndicator: true,
    slotLabelFormat: {
        hour: 'numeric',
        minute: '2-digit',
        omitZeroMinute: false,
        meridiem: 'short'
    },
    eventContent: (arg) => {
        const { event } = arg;
        const status = event.extendedProps.status;
        const paymentStatus = event.extendedProps.payment_status;
        const customer = event.extendedProps.customer_name;
        const services = event.extendedProps.services;
        const price = event.extendedProps.total_price;
        
        const isUnpaid = paymentStatus !== 'paid';
        const isCompleted = status === 'completed';
        const isCancelled = status === 'cancelled';

        // Website Design Tokens - Simple Solid Version
        const theme = {
            completed: { bg: 'bg-emerald-500', text: 'text-white', sub: 'text-emerald-100/80', icon: 'bg-white/20' },
            cancelled: { bg: 'bg-rose-500', text: 'text-white', sub: 'text-rose-100/80', icon: 'bg-white/20' },
            scheduled: { bg: 'bg-indigo-600', text: 'text-white', sub: 'text-indigo-100/80', icon: 'bg-white/20' }
        };
        
        const active = isCompleted ? theme.completed : (isCancelled ? theme.cancelled : theme.scheduled);

        if (arg.view.type === 'listWeek') {
            return {
                html: `
                    <div class="flex items-center justify-between w-full py-2">
                        <div class="flex items-center gap-4">
                            <div class="w-3 h-3 rounded-full ${active.bg} shadow-sm"></div>
                            <div class="flex flex-col">
                                <span class="font-black text-sm text-slate-900 dark:text-white leading-none mb-1">${customer}</span>
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">${Array.isArray(services) ? services.map(s => s.name).join(', ') : services}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            ${(isCompleted && isUnpaid) ? `<span class="px-2 py-0.5 rounded text-[8px] font-black uppercase bg-rose-500 text-white leading-none">Flagged</span>` : ''}
                            <span class="text-xs font-black text-slate-900 dark:text-white">${formatCurrency(price)}</span>
                        </div>
                    </div>
                `
            };
        }

        return {
            html: `
                <div class="flex flex-col h-full rounded-xl p-2.5 ${active.bg} ${active.text} shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden group">
                    <div class="flex items-start justify-between gap-1">
                        <div class="flex flex-col min-w-0 flex-1">
                            <span class="text-[10px] font-black uppercase tracking-tight truncate leading-none">${customer}</span>
                            <span class="text-[8px] font-bold ${active.sub} truncate mt-1 leading-none italic">${Array.isArray(services) ? services.map(s => s.name).join(', ') : services}</span>
                        </div>
                        
                        ${(isCompleted && isUnpaid) ? `
                            <div class="shrink-0 flex items-center justify-center w-4 h-4 rounded-lg bg-white text-rose-600 shadow-sm animate-pulse">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            </div>
                        ` : ''}
                    </div>

                    <div class="mt-auto pt-2 flex items-center justify-between">
                         <span class="text-[10px] font-black px-1.5 py-0.5 rounded bg-black/10 uppercase tracking-tighter">${formatCurrency(price)}</span>
                         ${isCompleted ? `
                            <div class="p-0.5 rounded-full bg-white/20">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                            </div>
                         ` : ''}
                    </div>
                </div>
            `
        };
    }
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
        <div class="flex flex-col xl:flex-row gap-6 items-center justify-between p-8 rounded-[3rem] bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 premium-shadow max-w-7xl mx-auto">
            <!-- Left: View Controls -->
            <div class="flex items-center gap-1 p-1 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 w-full xl:w-auto overflow-x-auto no-scrollbar">
                <button @click="changeView('timeGridWeek')" class="flex-1 xl:flex-none px-3 sm:px-5 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap" :class="currentView === 'timeGridWeek' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'text-slate-500 hover:text-indigo-600'">
                    {{ __('week') }}
                </button>
                <button @click="changeView('timeGridDay')" class="flex-1 xl:flex-none px-3 sm:px-5 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap" :class="currentView === 'timeGridDay' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'text-slate-500 hover:text-indigo-600'">
                    {{ __('day') }}
                </button>
                <button @click="changeView('listWeek')" class="flex-1 xl:flex-none px-3 sm:px-5 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap" :class="currentView === 'listWeek' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'text-slate-500 hover:text-indigo-600'">
                    {{ __('list') }}
                </button>
            </div>

            <!-- Center: Navigation -->
            <div class="flex items-center justify-between xl:justify-center gap-2 sm:gap-5 w-full xl:w-auto order-first xl:order-none">
                <button @click="goPrev" class="p-2 sm:p-3 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 text-slate-500 hover:text-indigo-600 transition-all shadow-sm active:scale-90">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <div class="text-center min-w-[140px] sm:min-w-[200px]">
                    <h3 class="text-sm sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ currentTitle }}</h3>
                    <button @click="goToday" class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.3em] text-amber-500 hover:text-amber-600 transition-colors mt-0.5">
                        {{ __('today') }}
                    </button>
                </div>
                <button @click="goNext" class="p-2 sm:p-3 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 text-slate-500 hover:text-indigo-600 transition-all shadow-sm active:scale-90">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" /></svg>
                </button>
            </div>

            <!-- Right: Filter (Owner) or Branding -->
            <div v-if="!isBarberView" class="w-full xl:w-auto flex-1 xl:max-w-xs flex items-center gap-3 sm:gap-4 p-2 px-3 sm:p-2.5 sm:px-4 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 shadow-sm">
                <div class="p-1.5 sm:p-2 rounded-lg bg-indigo-500/10 text-indigo-600 shrink-0">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                </div>
                <select v-model="selectedBarberId" class="flex-1 border-none bg-transparent font-bold text-[10px] sm:text-xs text-slate-900 dark:text-white focus:ring-0 p-0">
                    <option value="">{{ trans('all_barbers', getLocale()) }}</option>
                    <option v-for="barber in barbers" :key="barber.id" :value="barber.id">{{ barber.name }}</option>
                </select>
            </div>
            <div v-else class="flex items-center gap-2 sm:gap-3 px-2 sm:px-6 w-full xl:w-auto justify-center xl:justify-start">
                <div class="h-1.5 w-1.5 sm:h-2 sm:w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 leading-none">{{ __('live_calendar') }}</span>
            </div>
        </div>

        <!-- Main Calendar Glass Card -->
        <div class="premium-shadow rounded-[2.5rem] p-4 lg:p-10 bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 mx-auto max-w-7xl">
            <div class="calendar-container overflow-hidden rounded-[2rem] border border-slate-100 dark:border-white/5">
                <FullCalendar ref="calendarRef" :options="calendarOptions" />
            </div>
        </div>

        <!-- View Details Modal -->
        <Modal :show="isViewModalOpen" @close="closeViewModal">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white items-center flex gap-3">
                         <div class="p-2 rounded-xl bg-indigo-500/10 text-indigo-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        {{ __('appointment_details') }}
                    </h3>
                    <button @click="closeViewModal" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div v-if="selectedEvent" class="space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('barber') }}</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ selectedEvent.extendedProps.barber_name }}</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('customer') }}</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ selectedEvent.extendedProps.customer_name }}</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('services') }}</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white">
                            {{ Array.isArray(selectedEvent.extendedProps.services) ? selectedEvent.extendedProps.services.map(s => s.name).join(', ') : selectedEvent.extendedProps.services }}
                        </p>
                    </div>

                    <div class="flex items-center gap-6">
                        <div class="flex-1 p-4 rounded-2xl bg-indigo-500/10 border border-indigo-500/20">
                            <p class="text-[10px] font-black uppercase tracking-widest text-indigo-600 mb-1">{{ __('scheduled_time') }}</p>
                            <p class="text-sm font-black text-indigo-700 dark:text-indigo-400">
                                {{ new Date(selectedEvent.start).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }} - 
                                {{ new Date(selectedEvent.end).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                            </p>
                            <p class="text-xs font-bold text-indigo-600/60 mt-0.5">
                                {{ new Date(selectedEvent.start).toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric' }) }}
                            </p>
                        </div>
                        <div class="p-4 px-6 rounded-2xl bg-slate-900 dark:bg-indigo-600 text-center">
                            <p class="text-[10px] font-black uppercase tracking-widest text-white/40 dark:text-slate-900 mb-1">{{ __('price_label') }}</p>
                            <p class="text-xl font-black text-white dark:text-slate-900">{{ formatCurrency(selectedEvent.extendedProps.total_price) }}</p>
                        </div>
                    </div>

                    <!-- Payment Status Info -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 flex items-center justify-between">
                        <div class="flex flex-col">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('payment_status') }}</p>
                            <span :class="[
                                'text-sm font-black uppercase tracking-widest',
                                selectedEvent.extendedProps.payment_status === 'paid' ? 'text-emerald-500' : 
                                (selectedEvent.extendedProps.payment_status === 'semi-paid' ? 'text-amber-500' : 'text-rose-500')
                            ]">
                                {{ __(selectedEvent.extendedProps.payment_status) }}
                            </span>
                        </div>
                        <div :class="[
                            'px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border',
                            selectedEvent.extendedProps.payment_status === 'paid' ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20' : 
                            (selectedEvent.extendedProps.payment_status === 'semi-paid' ? 'bg-amber-500/10 text-amber-600 border-amber-500/20' : 'bg-rose-500/10 text-rose-600 border-rose-500/20')
                        ]">
                            {{ selectedEvent.extendedProps.payment_status === 'paid' ? '✓ RÉGLÉ' : '⚠ À PERCEVOIR' }}
                        </div>
                    </div>

                    <!-- Checkout Flow (Visible for scheduled or completed but semi/unpaid) -->
                    <div v-if="selectedEvent.extendedProps.status === 'scheduled' || selectedEvent.extendedProps.status === 'completed'" class="p-6 rounded-3xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 space-y-4">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('complete_and_collect') }}</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-500 ml-1">{{ __('total_price') }}</label>
                                <input v-model="checkoutForm.price_override" type="number" step="0.01" class="w-full rounded-xl border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900 text-sm font-bold focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-500 ml-1">{{ __('payment_status') }}</label>
                                <select v-model="checkoutForm.payment_status" class="w-full rounded-xl border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900 text-sm font-bold focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="paid">{{ __('paid') }}</option>
                                    <option value="semi-paid">{{ __('semi_paid') }}</option>
                                    <option value="unpaid">{{ __('unpaid') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedEvent.extendedProps.notes" class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('notes') }}</p>
                        <p class="text-sm font-medium text-slate-500 italic">{{ selectedEvent.extendedProps.notes }}</p>
                    </div>

                    <div class="mt-8 pt-8 border-t border-slate-100 dark:border-white/5 grid grid-cols-2 gap-3">
                        <PrimaryButton 
                            @click="updateStatus('completed')" 
                            class="col-span-2 !bg-emerald-600 hover:!bg-emerald-700 !rounded-2xl !py-4 shadow-lg shadow-emerald-500/20"
                        >
                            {{ selectedEvent.extendedProps.status === 'completed' ? __('update_payment') : __('finish_and_collect') }}
                        </PrimaryButton>
                        <button 
                             v-if="selectedEvent.extendedProps.status === 'scheduled'" 
                             @click="handleEditClick"
                             class="px-6 py-4 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 font-bold text-sm hover:bg-indigo-100 transition-colors"
                        >
                            {{ __('edit') }}
                        </button>
                        <button 
                             @click="deleteAppointment"
                             class="px-6 py-4 rounded-2xl bg-red-50 dark:bg-red-500/10 text-red-600 font-bold text-sm hover:bg-red-100 transition-colors"
                             :class="selectedEvent.extendedProps.status !== 'scheduled' ? 'col-span-2' : ''"
                        >
                            {{ __('delete') }}
                        </button>
                        <SecondaryButton @click="closeViewModal" class="col-span-2 !rounded-2xl">{{ __('close') }}</SecondaryButton>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Booking Modal -->
        <BookingModal 
            :show="isBookingModalOpen" 
            :services="services" 
            :barbers="barbers"
            :initial-date="selectedDate"
            :initial-appointment="selectedEvent"
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
            <svg class="w-8 h-8 font-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
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

.fc-list-event-time, .fc-list-event-title {
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
