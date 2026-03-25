<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\Bot;
use App\Models\Payment;

class DashboardController
{
    public function index(): void
    {
        Auth::require();
        $user     = Auth::user();
        $plan     = Auth::plan();
        $sub      = Auth::subscription();
        $bots     = Bot::forUser($user['id']);
        $payments = Payment::forUser($user['id'], 5);

        View::render('dashboard/index', compact('user', 'plan', 'sub', 'bots', 'payments'));
    }
}
