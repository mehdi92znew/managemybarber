<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;

class StripeWebhookController extends Controller
{
    public function handle(Request $request) {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            return response('Invalid payload', 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response('Invalid signature', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $this->handlePaymentSucceeded($paymentIntent);
                break;
            // Add other event types here
            default:
                // Unexpected event type
                // Log::info('Received unknown event type ' . $event->type);
        }

        return response('Webhook Handled', 200);
    }

    protected function handlePaymentSucceeded($paymentIntent) {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();
        if ($payment) {
            $payment->update([
                'status' => 'succeeded',
                'paid_at' => now(),
            ]);
            
            if ($payment->appointment) {
                $payment->appointment->update(['payment_status' => 'paid']);
            }
        } else {
             Log::warning('PaymentIntent succeeded but no local record found: ' . $paymentIntent->id);
        }
    }
}
