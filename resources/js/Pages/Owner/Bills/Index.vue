<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import BillManager from '@/Components/BillManager.vue';
import { computed } from 'vue';
import { trans } from '@/lang';

const props = defineProps({
    bills: Object,
    filters: Object,
});

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'en');
const __ = (key) => trans(key, currentLocale.value);
</script>

<template>
    <Head :title="__('bills')" />

    <AuthenticatedLayout>
        <template #header-title>
            {{ __('bills') }}
        </template>
        <template #header-subtitle>
            {{ __('all_expenses') }}
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <BillManager :bills="bills" :filters="filters" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
