<?php

namespace App\Models;

use App\Core\Database;

class Payment
{
    public static function forUser(int $userId, int $limit = 20): array
    {
        return Database::fetchAll(
            'SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . (int) $limit,
            [$userId]
        );
    }

    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO payments (user_id, stripe_invoice_id, stripe_payment_intent_id, amount, currency, status, description, paid_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['user_id'],
                $data['stripe_invoice_id']         ?? null,
                $data['stripe_payment_intent_id']   ?? null,
                $data['amount']                    ?? 0,
                $data['currency']                  ?? 'eur',
                $data['status']                    ?? 'paid',
                $data['description']               ?? '',
                $data['paid_at']                   ?? date('Y-m-d H:i:s'),
            ]
        );
    }

    public static function existsByInvoiceId(string $invoiceId): bool
    {
        $row = Database::fetch('SELECT id FROM payments WHERE stripe_invoice_id = ?', [$invoiceId]);
        return $row !== null;
    }
}
