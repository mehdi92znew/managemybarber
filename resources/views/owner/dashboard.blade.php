@extends('layouts.owner')

@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Barbers -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between mb-4">
             <div class="p-3 rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <span class="text-sm font-medium text-gray-400">Team</span>
        </div>
        <div class="flex items-end justify-between">
            <div>
                 <h4 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalBarbers }}</h4>
                 <p class="text-sm text-gray-500 mt-1">Active Barbers</p>
            </div>
        </div>
    </div>

    <!-- Today's Appointments -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between mb-4">
             <div class="p-3 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <span class="text-sm font-medium text-gray-400">Today</span>
        </div>
        <div class="flex items-end justify-between">
             <div>
                 <h4 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $todayAppointments }}</h4>
                 <p class="text-sm text-gray-500 mt-1">Scheduled</p>
            </div>
        </div>
    </div>

    <!-- Total Revenue -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between mb-4">
             <div class="p-3 rounded-full bg-green-50 text-green-600 dark:bg-green-900/50 dark:text-green-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <span class="text-sm font-medium text-gray-400">Revenue</span>
        </div>
        <div class="flex items-end justify-between">
             <div>
                 <h4 class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($totalRevenue, 2) }}</h4>
                 <p class="text-sm text-gray-500 mt-1">Total Earnings</p>
            </div>
        </div>
    </div>

     <!-- Total Appointments -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow duration-300">
         <div class="flex items-center justify-between mb-4">
             <div class="p-3 rounded-full bg-purple-50 text-purple-600 dark:bg-purple-900/50 dark:text-purple-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <span class="text-sm font-medium text-gray-400">All Time</span>
        </div>
         <div class="flex items-end justify-between">
             <div>
                 <h4 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalAppointments }}</h4>
                 <p class="text-sm text-gray-500 mt-1">Appointments</p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Appointments -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recent Appointments</h3>
        <a href="#" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Barber</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Price</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($recentAppointments as $appointment)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $appointment->customer->name }}</div>
                        <div class="text-xs text-gray-500">{{ $appointment->customer->phone }}</div>
                    </td>
                     <td class="px-6 py-4 whitespace-nowrap">
                         <div class="flex items-center">
                            <div class="h-6 w-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold mr-2">
                                {{ substr($appointment->barber->name, 0, 1) }}
                            </div>
                            <div class="text-sm text-gray-700 dark:text-gray-300">{{ $appointment->barber->name }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $appointment->start_time->format('M d, H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                         <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $appointment->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 
                              ($appointment->status === 'scheduled' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($appointment->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900 dark:text-white">
                        ${{ number_format($appointment->total_price, 2) }}
                    </td>
                </tr>
                @empty
                 <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                        No appointments found yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
