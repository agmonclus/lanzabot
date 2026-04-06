<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\BotTemplate;

class HelpController
{
    public function index(): void
    {
        Auth::require();
        $user = Auth::user();
        $plan = Auth::plan();
        $templates = BotTemplate::all();

        // Agrupar por plataforma
        $byPlatform = [];
        foreach ($templates as $t) {
            $byPlatform[$t['platform']][] = $t;
        }

        View::render('help/index', compact('user', 'plan', 'templates', 'byPlatform'));
    }

    public function guide(string $slug): void
    {
        Auth::require();
        $user = Auth::user();
        $plan = Auth::plan();

        $template = BotTemplate::findBySlug($slug);
        if (!$template) {
            Auth::flash('error', 'Guía no encontrada.');
            View::redirect('/help');
            return;
        }

        $requiredVars = json_decode($template['required_env_vars'], true) ?? [];
        $defaultVars  = json_decode($template['default_env_vars'], true) ?? [];

        View::render('help/guide', compact('user', 'plan', 'template', 'requiredVars', 'defaultVars'));
    }
}
