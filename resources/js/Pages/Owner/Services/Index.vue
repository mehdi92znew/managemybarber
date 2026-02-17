<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ServiceManager from '@/Components/ServiceManager.vue';

const props = defineProps({
    services: Object,
});
</script>

<template>
    <Head :title="__('manage_services')" />

    <AuthenticatedLayout>
        <template #header-title>{{ __('services') }}</template>
        <template #header>{{ __('service_management') }}</template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <ServiceManager :services="services" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
