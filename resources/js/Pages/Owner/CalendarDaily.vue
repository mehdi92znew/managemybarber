<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import DailyGlobalView from '@/Components/DailyGlobalView.vue';

const props = defineProps({
    barbers: Array,
    services: Array,
});
</script>

<template>
    <Head :title="__('daily_global_calendar')" />

    <AuthenticatedLayout>
        <template #header-title>{{ __('daily_planning') }}</template>
        <template #header>{{ __('global_day_view') }}</template>
        <template #header-subtitle>{{ __('global_day_view_desc') }}</template>

        <div class="py-4">
            <div class="max-w-full mx-auto px-2 sm:px-4 lg:px-6">
                 <DailyGlobalView :barbers="barbers" :services="services" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
