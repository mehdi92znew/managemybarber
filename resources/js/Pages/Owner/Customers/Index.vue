<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import CustomerManager from '@/Components/CustomerManager.vue';

const props = defineProps({
    customers: Object,
    filters: Object,
});
</script>

<template>
    <Head :title="__('manage_customers')" />

    <AuthenticatedLayout>
        <template #header>
            {{ __('customer_management') }}
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <CustomerManager :customers="customers" :filters="filters" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
