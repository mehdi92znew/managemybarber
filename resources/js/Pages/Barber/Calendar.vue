<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import CalendarView from '@/Components/CalendarView.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    services: Array,
});
</script>

<template>
    <Head :title="__('my_appointments')" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">
                 <CalendarView :services="services" :is-barber-view="true" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
