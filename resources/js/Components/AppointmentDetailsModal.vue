<script setup>
import { computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import axios from 'axios';
import { trans } from '../lang';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const route = window.route;

const props = defineProps({
    show: Boolean,
    appointment: Object,
});

const emit = defineEmits(['close', 'updated']);

const statusColor = computed(() => {
    switch (props.appointment?.status) {
        case 'completed': return 'text-green-600 bg-green-100';
        case 'cancelled': return 'text-red-600 bg-red-100';
        default: return 'text-blue-600 bg-blue-100';
    }
});

const updateStatus = (status) => {
    const locale = page.props.locale || 'en';
    if (!confirm(`${trans('confirm_mark_as', locale)}${status}?`)) return;

    axios.patch(route('owner.appointments.update', props.appointment.id), { status })
        .then(response => {
            emit('updated');
            close();
        })
        .catch(error => {
            alert(trans('failed_update_status', locale));
        });
};

const formatCurrency = (value) => {
    let locale = 'en-US';
    const currentLocale = page.props.locale || 'en';
    if (currentLocale === 'fr' || currentLocale === 'ar') locale = 'fr-FR';
    return new Intl.NumberFormat(locale, { style: 'currency', currency: 'USD', currencyDisplay: 'narrowSymbol' }).format(value || 0);
};

const formatDateTime = (date) => {
    if (!date) return '-';
    const d = new Date(date);
    const dateLocale = (page.props.locale === 'fr' || page.props.locale === 'ar') ? 'fr-FR' : 'en-US';
    return d.toLocaleString(dateLocale, { dateStyle: 'medium', timeStyle: 'short' });
};

const formatTime = (date) => {
    if (!date) return '-';
    const d = new Date(date);
    const dateLocale = (page.props.locale === 'fr' || page.props.locale === 'ar') ? 'fr-FR' : 'en-US';
    return d.toLocaleTimeString(dateLocale, { timeStyle: 'short' });
};

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="close" max-width="md">
        <div class="p-6" v-if="appointment">
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    {{ __('appointment_details') }}
                </h2>
                <span :class="['px-2 py-1 rounded-full text-xs font-semibold uppercase', statusColor]">
                    {{ appointment.extendedProps?.status ? __(appointment.extendedProps.status) : __('scheduled') }}
                </span>
            </div>

            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">{{ __('customer') }}</h3>
                    <p class="text-lg text-gray-900 dark:text-white">{{ appointment.extendedProps?.customer_name }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500">{{ __('barber') }}</h3>
                    <p class="text-gray-900 dark:text-white">{{ appointment.extendedProps?.barber_name }}</p>
                </div>

                <div>
                     <h3 class="text-sm font-medium text-gray-500">{{ __('time') }}</h3>
                    <p class="text-gray-900 dark:text-white">
                        {{ formatDateTime(appointment.start) }}
                        - 
                        {{ formatTime(appointment.end) }}
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500">{{ __('services') }}</h3>
                    <p class="text-gray-900 dark:text-white">{{ appointment.extendedProps?.services }}</p>
                </div>
                
                 <div>
                    <h3 class="text-sm font-medium text-gray-500">{{ __('total_price') }}</h3>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(appointment.extendedProps?.total_price) }}</p>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3">
                <SecondaryButton @click="close">{{ __('close') }}</SecondaryButton>
                
                <template v-if="appointment.extendedProps?.status === 'scheduled'">
                    <DangerButton @click="updateStatus('cancelled')">{{ __('cancel') }}</DangerButton>
                    <PrimaryButton @click="updateStatus('completed')" class="bg-green-600 hover:bg-green-700 focus:bg-green-700 active:bg-green-900 ring-green-500">
                        {{ __('complete_and_pay') }}
                    </PrimaryButton>
                </template>
            </div>
        </div>
    </Modal>
</template>
