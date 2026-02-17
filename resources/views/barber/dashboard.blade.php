@extends('layouts.barber')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-gray-500 dark:text-gray-400 text-sm">Today's Appointments</div>
        <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $todayAppointments }}</div>
    </div>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-gray-500 dark:text-gray-400 text-sm">Upcoming</div>
        <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $upcomingAppointments }}</div>
    </div>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
         <div class="text-gray-500 dark:text-gray-400 text-sm">My Commission (This Month)</div>
        <div class="text-3xl font-bold text-green-600 mt-2">${{ number_format($monthCommission, 2) }}</div>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Upcoming Appointments</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($appointments as $appointment)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        {{ $appointment->start_time->format('M d, H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-medium">
                        {{ $appointment->customer->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                         {{ $appointment->services->pluck('name')->join(', ') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $appointment->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ ucfirst($appointment->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No upcoming appointments.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
