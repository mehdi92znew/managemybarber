<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import BarberPayoutManager from '@/Components/BarberPayoutManager.vue';

defineProps({
    payouts: Object,
    barbers: Array,
    filters: Object,
});
</script>

<template>
    <Head :title="__('barber_payouts')" />

    <AuthenticatedLayout>
        <template #header-title>{{ __('barber_payouts') }}</template>
        <template #header>{{ __('payout_management') }}</template>
        <template #header-subtitle>{{ __('manage_payouts_desc') }}</template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <BarberPayoutManager 
                    :payouts="payouts" 
                    :barbers="barbers" 
                    :filters="filters"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
