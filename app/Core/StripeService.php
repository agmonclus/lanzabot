<?php

namespace App\Core;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\BillingPortal\Session as BillingPortalSession;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Webhook;

class StripeService
{
    public static function init(): void
    {
        Stripe::setApiKey(STRIPE_SECRET_KEY);
    }

    public static function createCustomer(string $email, string $name): Customer
    {
        self::init();
        return Customer::create(['email' => $email, 'name' => $name]);
    }

    public static function createCheckoutSession(
        string $priceId,
        string $customerId,
        int    $userId,
        string $planSlug
    ): CheckoutSession {
        self::init();
        return CheckoutSession::create([
            'customer'            => $customerId,
            'payment_method_types' => ['card'],
            'line_items'          => [[
                'price'    => $priceId,
                'quantity' => 1,
            ]],
            'mode'                => 'subscription',
            'success_url'         => APP_URL . '/billing?success=1',
            'cancel_url'          => APP_URL . '/plans',
            'metadata'            => [
                'user_id'   => $userId,
                'plan_slug' => $planSlug,
            ],
            'subscription_data'   => [
                'metadata' => ['user_id' => $userId, 'plan_slug' => $planSlug],
            ],
        ]);
    }

    public static function createBillingPortalSession(string $customerId): BillingPortalSession
    {
        self::init();
        return BillingPortalSession::create([
            'customer'   => $customerId,
            'return_url' => APP_URL . '/billing',
        ]);
    }

    public static function constructWebhookEvent(string $payload, string $sigHeader): \Stripe\Event
    {
        return Webhook::constructEvent($payload, $sigHeader, STRIPE_WEBHOOK_SECRET);
    }

    public static function retrieveSubscription(string $id): \Stripe\Subscription
    {
        self::init();
        return \Stripe\Subscription::retrieve($id);
    }

    public static function getPriceIdForPlan(string $planSlug): ?string
    {
        return match ($planSlug) {
            'medium'  => STRIPE_PRICE_MEDIUM,
            'starter' => STRIPE_PRICE_STARTER,
            'pro'     => STRIPE_PRICE_PRO,
            default   => null,
        };
    }
}
