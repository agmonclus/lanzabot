<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\StripeService;
use App\Models\Plan;
use App\Models\User;

class PlanController
{
    public function index(): void
    {
        Auth::require();
        $user    = Auth::user();
        $plans   = Plan::all();
        $current = Auth::plan();
        $sub     = Auth::subscription();

        View::render('plans/index', compact('user', 'plans', 'current', 'sub'));
    }

    public function subscribe(): void
    {
        Auth::require();
        $this->verifyCsrf();

        $user     = Auth::user();
        $planSlug = $_POST['plan'] ?? '';
        $plan     = Plan::findBySlug($planSlug);

        if (!$plan) {
            Auth::flash('error', 'Plan no válido.');
            View::redirect('/plans');
        }

        // Custom plan -> redirect to contact
        if ($plan['slug'] === 'custom') {
            View::redirect('/plans#custom');
        }

        // Free plan -> just update subscription
        if ($plan['slug'] === 'free') {
            // Handled via Stripe cancellation through billing portal
            Auth::flash('info', 'Para cambiar al plan gratuito, cancela tu suscripción desde el portal de facturación.');
            View::redirect('/billing');
        }

        $priceId = StripeService::getPriceIdForPlan($plan['slug']);
        if (!$priceId) {
            Auth::flash('error', 'Este plan no está disponible aún. Configura los Price IDs de Stripe.');
            View::redirect('/plans');
        }

        // Ensure Stripe customer
        if (!$user['stripe_customer_id']) {
            try {
                $customer = StripeService::createCustomer($user['email'] ?? '', $user['name']);
                User::update($user['id'], ['stripe_customer_id' => $customer->id]);
                $stripeCustomerId = $customer->id;
            } catch (\Exception $e) {
                Auth::flash('error', 'Error al crear cliente en Stripe.');
                View::redirect('/plans');
            }
        } else {
            $stripeCustomerId = $user['stripe_customer_id'];
        }

        try {
            $session = StripeService::createCheckoutSession(
                $priceId,
                $stripeCustomerId,
                $user['id'],
                $plan['slug']
            );
            header('Location: ' . $session->url);
            exit;
        } catch (\Exception $e) {
            Auth::flash('error', 'Error al crear sesión de pago: ' . $e->getMessage());
            View::redirect('/plans');
        }
    }

    public function customRequest(): void
    {
        Auth::require();
        $this->verifyCsrf();

        $user    = Auth::user();
        $company = trim($_POST['company']  ?? '');
        $bots    = trim($_POST['bots']     ?? '');
        $needs   = trim($_POST['needs']    ?? '');
        $email   = trim($_POST['email']    ?? $user['email'] ?? '');

        // Log to file until email is configured
        $log = date('Y-m-d H:i:s') . " | User #{$user['id']} | {$email} | {$company} | Bots: {$bots} | {$needs}\n";
        file_put_contents(dirname(__DIR__, 2) . '/storage/logs/custom_requests.log', $log, FILE_APPEND);

        Auth::flash('success', 'Solicitud recibida. Te contactaremos pronto con un presupuesto a medida.');
        View::redirect('/plans');
    }

    private function verifyCsrf(): void
    {
        $token = $_POST['_csrf'] ?? '';
        if (!Auth::verifyCsrf($token)) {
            http_response_code(403);
            exit('Token CSRF inválido.');
        }
    }
}
