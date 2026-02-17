<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct()
    {
        if (config('services.stripe.secret')) {
             Stripe::setApiKey(config('services.stripe.secret'));
        }
    }

    public function createPaymentIntent(Appointment $appointment)
    {
        // Validation
        if (!config('services.stripe.secret')) {
            // For MVP mock environment, maybe return mock?
            // But better to fail if explicitly requested.
            // Or log warning and return null?
            throw new \Exception('Stripe API key is not configured.');
        }

        if (!$appointment->total_price || $appointment->total_price <= 0) {
             throw new \Exception("Invalid total price for appointment {$appointment->id}");
        }

        $amount = (int) ($appointment->total_price * 100);

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'usd',
                'metadata' => [
                    'appointment_id' => $appointment->id,
                    'type' => 'appointment_booking'
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            // Create pending payment record
            Payment::updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'amount' => $appointment->total_price,
                    'method' => 'stripe',
                    'status' => 'pending',
                    'stripe_payment_intent_id' => $paymentIntent->id,
                    'paid_at' => null // Ensure null if updating existing
                ]
            );

            return $paymentIntent;
        } catch (\Exception $e) {
            Log::error('Stripe PaymentIntent Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function verifyPaymentIntent($paymentIntentId)
    {
        if (!config('services.stripe.secret')) return false;
        
        try {
            $intent = PaymentIntent::retrieve($paymentIntentId);
            if ($intent->status === 'succeeded') {
                $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();
                
                if ($payment) {
                    $payment->update([
                        'status' => 'succeeded',
                        'paid_at' => now(),
                    ]);
                    
                    if ($payment->appointment) {
                        $payment->appointment->update(['payment_status' => 'paid']);
                    }
                }
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Stripe Verification Error: ' . $e->getMessage());
        }
        return false;
    }
}
