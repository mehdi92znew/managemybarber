<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Payment;
use Carbon\Carbon;

class CommissionService
{
    public function calculateAndRecord(Appointment $appointment)
    {
        $barber = $appointment->barber;
        $totalPrice = $appointment->total_price ?? 0;

        // 1. Calculate Commission
        $commissionAmount = 0;
        if ($barber && $barber->commission_value) {
            if ($barber->commission_type === 'percentage') {
                $commissionAmount = ($totalPrice * $barber->commission_value) / 100;
            } else {
                $commissionAmount = $barber->commission_value;
            }
        }

        // Ensure commission doesn't exceed total price (optional safeguard)
        $commissionAmount = min((float)$commissionAmount, (float)$totalPrice);

        // 2. Update Appointment
        $appointment->update([
            'commission_amount' => $commissionAmount,
            // We assume this is called when status is set to 'completed'
        ]);

        // 3. Update Customer Last Visit
        if ($appointment->customer_id && $appointment->customer) {
            $appointment->customer->update(['last_visit_at' => Carbon::now()]);
        }

        return $commissionAmount;
    }

    public function recordPayment(Appointment $appointment, $method = 'cash', $status = 'paid')
    {
        // Simple payment recording for MVP
        Payment::create([
            'appointment_id' => $appointment->id,
            'amount' => $appointment->total_price,
            'method' => $method,
            'status' => 'succeeded',
            'paid_at' => Carbon::now(),
        ]);
        
        $appointment->update(['payment_status' => $status]);
    }
}
