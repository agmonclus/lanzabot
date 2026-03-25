<?php

namespace App\Models;

use App\Core\Database;

class Subscription
{
    public static function getActiveForUser(int $userId): ?array
    {
        return Database::fetch(
            "SELECT s.*, p.slug as plan_slug, p.name as plan_name, p.max_bots, p.ram_mb, p.disk_gb, p.max_databases, p.price_weekly
             FROM subscriptions s
             JOIN plans p ON p.id = s.plan_id
             WHERE s.user_id = ? AND s.status IN ('active', 'trialing', 'free')
             ORDER BY s.id DESC LIMIT 1",
            [$userId]
        );
    }

    public static function createFree(int $userId): int
    {
        $plan = Plan::findBySlug('free');
        return Database::insert(
            'INSERT INTO subscriptions (user_id, plan_id, status) VALUES (?, ?, ?)',
            [$userId, $plan['id'], 'free']
        );
    }

    public static function findByStripeId(string $stripeId): ?array
    {
        return Database::fetch('SELECT * FROM subscriptions WHERE stripe_subscription_id = ?', [$stripeId]);
    }

    public static function updateByStripeId(string $stripeId, array $data): void
    {
        $sets   = [];
        $params = [];
        foreach ($data as $col => $val) {
            $sets[]   = "{$col} = ?";
            $params[] = $val;
        }
        $params[] = $stripeId;
        Database::execute('UPDATE subscriptions SET ' . implode(', ', $sets) . ' WHERE stripe_subscription_id = ?', $params);
    }

    public static function createFromStripe(int $userId, int $planId, string $stripeSubId, string $stripeCustomerId, int $periodEnd): int
    {
        // Cancel previous active subscriptions
        Database::execute(
            "UPDATE subscriptions SET status = 'canceled' WHERE user_id = ? AND status IN ('active','trialing','free')",
            [$userId]
        );

        return Database::insert(
            'INSERT INTO subscriptions (user_id, plan_id, stripe_subscription_id, stripe_customer_id, status, current_period_end) VALUES (?, ?, ?, ?, ?, ?)',
            [$userId, $planId, $stripeSubId, $stripeCustomerId, 'active', date('Y-m-d H:i:s', $periodEnd)]
        );
    }
}
