<?php

namespace App\Http\Controllers\Barber;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function index()
    {
        $barber = auth()->user();
        $services = Service::where('shop_id', $barber->shop_id)->where('is_active', true)->get();
        return \Inertia\Inertia::render('Barber/Calendar', [
            'services' => $services
        ]);
    }

    public function events(Request $request)
    {
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        $appointments = Appointment::with(['customer', 'services'])
            ->where('barber_id', auth()->id()) // Scoped to self
            ->whereBetween('start_time', [$start, $end])
            ->get();

        $events = $appointments->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'title' => $appointment->customer->name,
                'start' => $appointment->start_time->toDateTimeString(),
                'end' => $appointment->end_time->toDateTimeString(),
                'extendedProps' => [
                    'barber_id' => auth()->id(),
                    'barber_name' => auth()->user()->name,
                    'customer_id' => $appointment->customer_id,
                    'customer_name' => $appointment->customer->name,
                    'status' => $appointment->status,
                    'payment_status' => $appointment->payment_status,
                    'services' => $appointment->services->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'price' => $s->pivot->price_at_time]),
                    'total_price' => $appointment->total_price ?? 0,
                    'notes' => $appointment->notes,
                ],
                'backgroundColor' => $this->appointmentService->getStatusColor($appointment->status, $appointment->payment_status),
                'borderColor' => $this->appointmentService->getStatusColor($appointment->status, $appointment->payment_status),
            ];
        });

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $request->validate([
            'new_customer_name' => 'required_without:customer_id|string|nullable',
            'new_customer_phone' => 'nullable|string',
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
            'start_time' => 'required|date',
        ]);

        try {
            // Force barber_id to self
            $data = $request->all();
            $data['barber_id'] = auth()->id();

            $appointment = $this->appointmentService->createAppointment($data, auth()->user()->shop_id);
            return response()->json(['message' => 'Appointment booked successfully.', 'appointment' => $appointment]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, Appointment $appointment)
    {
        if ($appointment->barber_id !== auth()->id()) abort(403);

        try {
            if ($request->has('start') && $request->has('end')) {
                $this->appointmentService->updateTime($appointment, $request->start, $request->end);
                return response()->json(['message' => 'Appointment moved successfully.']);
            }

            if ($request->has('status')) {
                 $this->appointmentService->updateStatus(
                    $appointment, 
                    $request->status, 
                    $request->payment_status, 
                    $request->total_price
                );
                return response()->json(['message' => 'Appointment status updated.']);
            }

            // General update
            $this->appointmentService->updateAppointment($appointment, $request->all());
            return response()->json(['message' => 'Appointment updated.']);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Appointment $appointment)
    {
        if ($appointment->barber_id !== auth()->id()) {
            abort(403);
        }

        $appointment->delete();

        return response()->json(['message' => 'Appointment deleted successfully.']);
    }
}
