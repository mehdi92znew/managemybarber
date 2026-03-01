<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Services\AppointmentService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function index()
    {
        $barbers = User::where('shop_id', auth()->user()->shop_id)->where('role', 'barber')->get();
        $services = Service::where('shop_id', auth()->user()->shop_id)->where('is_active', true)->get();
        return \Inertia\Inertia::render('Owner/Calendar', compact('barbers', 'services'));
    }

    public function daily()
    {
        $barbers = User::where('shop_id', auth()->user()->shop_id)->where('role', 'barber')->get();
        $services = Service::where('shop_id', auth()->user()->shop_id)->where('is_active', true)->get();
        return \Inertia\Inertia::render('Owner/CalendarDaily', compact('barbers', 'services'));
    }

    public function list(Request $request)
    {
        $query = Appointment::with(['customer', 'barber', 'services'])
            ->where('shop_id', auth()->user()->shop_id);

        if ($request->filled('barber_id')) {
            $query->where('barber_id', $request->barber_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_time', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('start_time', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $appointments = $query->latest()->paginate(20)->withQueryString();
        $barbers = User::where('shop_id', auth()->user()->shop_id)->where('role', 'barber')->get(['id', 'name']);
        $services = Service::where('shop_id', auth()->user()->shop_id)->where('is_active', true)->get(['id', 'name', 'price', 'duration_minutes']);

        return \Inertia\Inertia::render('Owner/Appointments/Index', [
            'appointments' => $appointments,
            'barbers' => $barbers,
            'services' => $services,
            'filters' => $request->only(['barber_id', 'start_date', 'end_date', 'status', 'payment_status'])
        ]);
    }

    public function events(Request $request)
    {
        $start = \Carbon\Carbon::parse($request->start);
        $end = \Carbon\Carbon::parse($request->end);

        $query = Appointment::with(['customer', 'barber', 'services'])
            ->whereBetween('start_time', [$start, $end]);

        if ($request->has('barber_id') && $request->barber_id) {
            $query->where('barber_id', $request->barber_id);
        }

        $events = $query->get()->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'title' => $appointment->customer->name . ' - ' . $appointment->barber->name,
                'start' => $appointment->start_time->toDateTimeString(),
                'end' => $appointment->end_time->toDateTimeString(),
                'resourceId' => $appointment->barber_id,
                'extendedProps' => [
                    'barber_id' => $appointment->barber_id,
                    'barber_name' => $appointment->barber->name,
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
            'barber_id' => 'required|exists:users,id',
            'customer_id' => 'nullable|exists:customers,id',
            'new_customer_name' => 'required_without:customer_id|string|nullable',
            'new_customer_phone' => 'nullable|string',
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
            'start_time' => 'required|date',
        ]);

        try {
            $appointment = $this->appointmentService->createAppointment($request->all(), auth()->user()->shop_id);
            return response()->json(['message' => 'Appointment booked successfully.', 'appointment' => $appointment]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, Appointment $appointment)
    {
        try {
            if ($request->has('start') && $request->has('end')) {
                $this->appointmentService->updateTime($appointment, $request->start, $request->end);
                return response()->json(['message' => 'Appointment moved successfully.']);
            }

            if ($request->has('status') && !$request->has('start_time')) {
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
        if ($appointment->shop_id !== auth()->user()->shop_id) {
            abort(403);
        }

        $appointment->delete();

        return response()->json(['message' => 'Appointment deleted successfully.']);
    }
}
