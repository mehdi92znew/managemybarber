<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  LineElement,
  PointElement
} from 'chart.js';
import { Line } from 'vue-chartjs';
import { computed } from 'vue';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, LineElement, PointElement);

const props = defineProps({
    stats: Object,
    shopsGrowth: Array,
    recentShops: Array,
    auth: Object
});

const chartData = computed(() => {
    const labels = props.shopsGrowth.map(point => point.month);
    const data = props.shopsGrowth.map(point => point.total);

    return {
        labels: labels,
        datasets: [{
            label: 'New Shops',
            data: data,
            backgroundColor: '#10B981',
            borderColor: '#059669',
            borderWidth: 2,
            tension: 0.3,
            fill: false
        }]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
};
</script>

<template>
    <Head title="Super Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Super Admin Overview
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Shops -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Shops</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.totalShops }}</div>
                    </div>

                    <!-- Total Barbers -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Barbers</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.totalBarbers }}</div>
                    </div>

                    <!-- Total Appointments -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Appointments</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.totalAppointments }}</div>
                    </div>

                    <!-- Total Customers -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Customers</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.totalCustomers }}</div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Chart Section -->
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Platform Growth (New Shops)</h3>
                        <div class="h-64">
                            <Line :data="chartData" :options="chartOptions" />
                        </div>
                        <div v-if="shopsGrowth.length === 0" class="text-center text-gray-500 py-10">
                            No growth data available yet.
                        </div>
                    </div>

                    <!-- Recent Shops -->
                     <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Newest Shops</h3>
                        <ul class="space-y-4">
                            <li v-for="shop in recentShops" :key="shop.id" class="flex flex-col">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ shop.name }}</div>
                                        <div class="text-xs text-gray-500">{{ shop.address || 'No address' }}</div>
                                    </div>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ shop.subscription_status }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    Owner: {{ shop.owner?.name || 'Unknown' }}
                                </div>
                            </li>
                             <li v-if="recentShops.length === 0" class="text-sm text-gray-500 italic">
                                No shops found.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
