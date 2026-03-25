<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\StripeService;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

class BillingController
{
    public function index(): void
    {
        Auth::require();
        $user     = Auth::user();
        $payments = Payment::forUser($user['id']);
        $sub      = Auth::subscription();
        $plan     = Auth::plan();

        View::render('billing/index', compact('user', 'payments', 'sub', 'plan'));
    }

    public function portal(): void
    {
        Auth::require();
        $user = Auth::user();

        if (!$user['stripe_customer_id']) {
            Auth::flash('error', 'No tienes una suscripción activa de pago.');
            View::redirect('/billing');
        }

        try {
            $session = StripeService::createBillingPortalSession($user['stripe_customer_id']);
            header('Location: ' . $session->url);
            exit;
        } catch (\Exception $e) {
            Auth::flash('error', 'Error al acceder al portal de facturación.');
            View::redirect('/billing');
        }
    }

    public function webhook(): void
    {
        $payload   = file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        try {
            $event = StripeService::constructWebhookEvent($payload, $sigHeader);
        } catch (\Exception $e) {
            http_response_code(400);
            exit;
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutCompleted($event->data->object);
                break;
            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
                $this->handleSubscriptionUpdate($event->data->object);
                break;
            case 'invoice.payment_succeeded':
                $this->handleInvoicePaid($event->data->object);
                break;
            case 'invoice.payment_failed':
                $this->handleInvoiceFailed($event->data->object);
                break;
        }

        http_response_code(200);
        echo 'OK';
    }

    // ---- Webhook handlers ----

    private function handleCheckoutCompleted(object $session): void
    {
        if ($session->mode !== 'subscription') return;

        $userId   = (int) ($session->metadata->user_id ?? 0);
        $planSlug = $session->metadata->plan_slug ?? '';
        if (!$userId || !$planSlug) return;

        $plan = Plan::findBySlug($planSlug);
        if (!$plan) return;

        $stripeSubId = $session->subscription;
        $sub = StripeService::retrieveSubscription($stripeSubId);

        Subscription::createFromStripe(
            $userId,
            $plan['id'],
            $stripeSubId,
            $session->customer,
            $sub->current_period_end
        );

        // Save stripe customer on user
        User::update($userId, ['stripe_customer_id' => $session->customer]);
    }

    private function handleSubscriptionUpdate(object $subscription): void
    {
        $existing = Subscription::findByStripeId($subscription->id);
        if (!$existing) return;

        $status = $subscription->status;
        $data = [
            'status'              => in_array($status, ['active','canceled','past_due','trialing','unpaid']) ? $status : 'canceled',
            'current_period_end'  => date('Y-m-d H:i:s', $subscription->current_period_end),
        ];

        Subscription::updateByStripeId($subscription->id, $data);
    }

    private function handleInvoicePaid(object $invoice): void
    {
        if (Payment::existsByInvoiceId($invoice->id)) return;

        // Find user by customer ID
        $user = \App\Core\Database::fetch(
            'SELECT * FROM users WHERE stripe_customer_id = ?',
            [$invoice->customer]
        );
        if (!$user) return;

        Payment::create([
            'user_id'                  => $user['id'],
            'stripe_invoice_id'        => $invoice->id,
            'stripe_payment_intent_id' => $invoice->payment_intent ?? null,
            'amount'                   => $invoice->amount_paid,
            'currency'                 => $invoice->currency,
            'status'                   => 'paid',
            'description'              => 'Suscripción Lanzabot',
            'paid_at'                  => date('Y-m-d H:i:s', $invoice->status_transitions->paid_at ?? time()),
        ]);
    }

    private function handleInvoiceFailed(object $invoice): void
    {
        $sub = Subscription::findByStripeId($invoice->subscription ?? '');
        if ($sub) {
            Subscription::updateByStripeId($invoice->subscription, ['status' => 'past_due']);
        }
    }
}
