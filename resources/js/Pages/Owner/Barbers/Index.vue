<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import BarberManager from '@/Components/BarberManager.vue';

const props = defineProps({
    barbers: Array,
});
</script>

<template>
    <Head :title="__('manage_barbers')" />

    <AuthenticatedLayout>
        <template #header>
            {{ __('barber_management') }}
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <BarberManager :initialBarbers="barbers" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
