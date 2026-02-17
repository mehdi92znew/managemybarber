<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function createIntent(Request $request)
    {
        // Require authentication
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate(['appointment_id' => 'required|exists:appointments,id']);
        
        $appointment = Appointment::findOrFail($request->appointment_id);
        
        // Authorization: Current user must be owner/barber of the shop
        // Or super admin
        // For MVP, if auth check passes and user belongs to same shop (or owner/barber), allow.
        $user = auth()->user();
        if ($user->shop_id !== $appointment->shop_id && !$user->hasRole('super_admin')) {
             return response()->json(['message' => 'Unauthorized access to this appointment.'], 403);
        }

        try {
            $intent = $this->paymentService->createPaymentIntent($appointment);
            return response()->json([
                'client_secret' => $intent->client_secret,
                'payment_intent_id' => $intent->id,
            ]);
        } catch (\Exception $e) {
             return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function verify(Request $request)
    {
        $request->validate(['payment_intent_id' => 'required|string']);

        $success = $this->paymentService->verifyPaymentIntent($request->payment_intent_id);

        if ($success) {
            return response()->json(['message' => 'Payment verified successfully.']);
        }

        return response()->json(['message' => 'Payment verification failed.'], 400);
    }
}
