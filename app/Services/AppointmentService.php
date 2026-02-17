<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    public function createAppointment($data, $creatorShopId)
    {
        // 1. Handle Customer
        $customerId = $data['customer_id'] ?? null;
        if (!$customerId && !empty($data['new_customer_name'])) {
            $customer = Customer::create([
                'shop_id' => $creatorShopId,
                'name' => $data['new_customer_name'],
                'phone' => $data['new_customer_phone'] ?? null,
            ]);
            $customerId = $customer->id;
        }

        // 2. Calculate End Time and Total Price
        $startTime = Carbon::parse($data['start_time']);
        $services = Service::whereIn('id', $data['service_ids'])->get();
        $totalDuration = (int) $services->sum('duration_minutes');
        $totalPrice = $services->sum('price');
        $endTime = $startTime->copy()->addMinutes($totalDuration);
        $barberId = $data['barber_id'];

        // 3. Double Booking Check
        if ($this->hasConflicts($barberId, $startTime, $endTime)) {
            throw new \Exception('This time slot is already booked for the selected barber.');
        }

        // 4. Create Appointment
        return DB::transaction(function () use ($creatorShopId, $barberId, $customerId, $startTime, $endTime, $totalPrice, $services) {
            $appointment = Appointment::create([
                'shop_id' => $creatorShopId,
                'barber_id' => $barberId,
                'customer_id' => $customerId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'scheduled',
                'total_price' => $totalPrice,
            ]);

            // 5. Attach Services
            foreach ($services as $service) {
                $appointment->services()->attach($service->id, ['price_at_time' => $service->price]);
            }

            return $appointment;
        });
    }

    public function updateTime(Appointment $appointment, $startTime, $endTime)
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        if ($this->hasConflicts($appointment->barber_id, $start, $end, $appointment->id)) {
            throw new \Exception('This time slot is already booked.');
        }

        $appointment->update([
            'start_time' => $start,
            'end_time' => $end,
        ]);

        return $appointment;
    }

    public function updateAppointment(Appointment $appointment, $data)
    {
        return DB::transaction(function () use ($appointment, $data) {
            // Update basic info
            $updateData = [];
            if (isset($data['barber_id'])) $updateData['barber_id'] = $data['barber_id'];
            if (isset($data['notes'])) $updateData['notes'] = $data['notes'];
            if (isset($data['start_time'])) {
                $updateData['start_time'] = Carbon::parse($data['start_time']);
                // If duration is known, update end_time too
                if ($appointment->services()->exists()) {
                    $totalDuration = (int) $appointment->services()->sum('duration_minutes');
                    $updateData['end_time'] = Carbon::parse($data['start_time'])->addMinutes($totalDuration);
                }
            }

            if (!empty($updateData)) {
                $appointment->update($updateData);
            }

            // Update services if provided
            if (isset($data['service_ids'])) {
                $services = Service::whereIn('id', $data['service_ids'])->get();
                $appointment->services()->detach();
                foreach ($services as $service) {
                    $appointment->services()->attach($service->id, ['price_at_time' => $service->price]);
                }
                
                // Recalculate total price based on NEW services if not overridden
                if (!isset($data['total_price'])) {
                    $appointment->update(['total_price' => $services->sum('price')]);
                }
            }

            // Manual price override
            if (isset($data['total_price'])) {
                $appointment->update(['total_price' => $data['total_price']]);
            }

            return $appointment;
        });
    }

    public function updateStatus(Appointment $appointment, $status, $paymentStatus = null, $totalPrice = null)
    {
        $data = ['status' => $status];
        if ($paymentStatus) $data['payment_status'] = $paymentStatus;
        if ($totalPrice !== null) $data['total_price'] = $totalPrice;

        $appointment->update($data);

        if ($status === 'completed') {
            $commissionService = new CommissionService();
            $commissionService->calculateAndRecord($appointment);
            
            if ($paymentStatus === 'paid' || $paymentStatus === 'semi-paid') {
                $commissionService->recordPayment($appointment, 'cash', $paymentStatus); 
            }
        }

        return $appointment;
    }

    public function hasConflicts($barberId, $startTime, $endTime, $excludeAppointmentId = null)
    {
        $query = Appointment::where('barber_id', $barberId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($sub) use ($startTime, $endTime) {
                      $sub->where('start_time', '<', $startTime)
                          ->where('end_time', '>', $endTime);
                  });
            });

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        return $query->exists();
    }
    
    public function getStatusColor($status, $paymentStatus = 'paid')
    {
        return match ($status) {
            'completed' => '#10B981', // green-500
            'cancelled' => '#EF4444', // red-500
            default => '#6366F1',     // indigo-500 (Professional Blue)
        };
    }
}
