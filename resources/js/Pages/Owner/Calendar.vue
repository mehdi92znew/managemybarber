<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import CalendarView from '@/Components/CalendarView.vue';

const props = defineProps({
    services: Array,
    barbers: Array,
});
</script>

<template>
     <Head :title="__('shop_calendar')" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">
                 <CalendarView :services="services" :barbers="barbers" :is-barber-view="false" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
