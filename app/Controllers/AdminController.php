<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use App\Models\Bot;
use App\Models\BotTemplate;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

class AdminController
{
    /**
     * Verifica que el usuario sea admin. Redirige si no lo es.
     */
    private function requireAdmin(): array
    {
        Auth::require();
        $user = Auth::user();
        if ((int)$user['id'] !== 1) {
            http_response_code(403);
            Auth::flash('error', 'No tienes permisos de administrador.');
            View::redirect('/dashboard');
        }
        return $user;
    }

    private function verifyCsrf(): void
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Auth::verifyCsrf($token)) {
            http_response_code(403);
            exit('Token CSRF inválido.');
        }
    }

    // ================================================================
    // Dashboard principal admin
    // ================================================================
    public function index(): void
    {
        $user = $this->requireAdmin();

        $stats = [
            'total_users'         => (int)(Database::fetch('SELECT COUNT(*) as cnt FROM users')['cnt'] ?? 0),
            'total_bots'          => (int)(Database::fetch('SELECT COUNT(*) as cnt FROM bots')['cnt'] ?? 0),
            'total_templates'     => (int)(Database::fetch('SELECT COUNT(*) as cnt FROM bot_templates')['cnt'] ?? 0),
            'active_subs'         => (int)(Database::fetch("SELECT COUNT(*) as cnt FROM subscriptions WHERE status IN ('active','trialing')")['cnt'] ?? 0),
            'free_subs'           => (int)(Database::fetch("SELECT COUNT(*) as cnt FROM subscriptions WHERE status = 'free'")['cnt'] ?? 0),
            'running_bots'        => (int)(Database::fetch("SELECT COUNT(*) as cnt FROM bots WHERE coolify_status = 'running'")['cnt'] ?? 0),
            'total_revenue'       => (int)(Database::fetch("SELECT COALESCE(SUM(amount),0) as total FROM payments WHERE status = 'paid'")['total'] ?? 0),
            'users_today'         => (int)(Database::fetch("SELECT COUNT(*) as cnt FROM users WHERE DATE(created_at) = CURDATE()")['cnt'] ?? 0),
        ];

        $recent_users = Database::fetchAll('SELECT * FROM users ORDER BY created_at DESC LIMIT 5');
        $recent_bots  = Database::fetchAll(
            'SELECT b.*, u.name as user_name FROM bots b JOIN users u ON u.id = b.user_id ORDER BY b.created_at DESC LIMIT 5'
        );

        $plans_distribution = Database::fetchAll(
            "SELECT p.name, p.slug, COUNT(s.id) as total
             FROM plans p
             LEFT JOIN subscriptions s ON s.plan_id = p.id AND s.status IN ('active','trialing','free')
             WHERE p.is_active = 1
             GROUP BY p.id ORDER BY p.sort_order"
        );

        View::render('admin/index', compact('user', 'stats', 'recent_users', 'recent_bots', 'plans_distribution'), 'admin');
    }

    // ================================================================
    // Usuarios
    // ================================================================
    public function users(): void
    {
        $user = $this->requireAdmin();
        $users = Database::fetchAll(
            'SELECT u.*, 
                    (SELECT COUNT(*) FROM bots WHERE user_id = u.id) as bot_count,
                    (SELECT p.name FROM subscriptions s JOIN plans p ON p.id = s.plan_id WHERE s.user_id = u.id AND s.status IN ("active","trialing","free") ORDER BY s.id DESC LIMIT 1) as plan_name
             FROM users u ORDER BY u.created_at DESC'
        );
        View::render('admin/users', compact('user', 'users'), 'admin');
    }

    // ================================================================
    // Bots
    // ================================================================
    public function bots(): void
    {
        $user = $this->requireAdmin();
        $bots = Database::fetchAll(
            'SELECT b.*, u.name as user_name, u.email as user_email
             FROM bots b JOIN users u ON u.id = b.user_id
             ORDER BY b.created_at DESC'
        );
        View::render('admin/bots', compact('user', 'bots'), 'admin');
    }

    // ================================================================
    // Suscripciones
    // ================================================================
    public function subscriptions(): void
    {
        $user = $this->requireAdmin();
        $subs = Database::fetchAll(
            'SELECT s.*, u.name as user_name, u.email as user_email, p.name as plan_name, p.slug as plan_slug
             FROM subscriptions s
             JOIN users u ON u.id = s.user_id
             JOIN plans p ON p.id = s.plan_id
             ORDER BY s.created_at DESC'
        );
        View::render('admin/subscriptions', compact('user', 'subs'), 'admin');
    }

    // ================================================================
    // Pagos
    // ================================================================
    public function payments(): void
    {
        $user = $this->requireAdmin();
        $payments = Database::fetchAll(
            'SELECT p.*, u.name as user_name, u.email as user_email
             FROM payments p JOIN users u ON u.id = p.user_id
             ORDER BY p.created_at DESC'
        );
        View::render('admin/payments', compact('user', 'payments'), 'admin');
    }

    // ================================================================
    // Plantillas — Listado
    // ================================================================
    public function templates(): void
    {
        $user = $this->requireAdmin();
        $templates = BotTemplate::allIncludingInactive();
        View::render('admin/templates/index', compact('user', 'templates'), 'admin');
    }

    // ================================================================
    // Plantillas — Crear (formulario)
    // ================================================================
    public function templateCreate(): void
    {
        $user = $this->requireAdmin();
        View::render('admin/templates/form', ['user' => $user, 'template' => null], 'admin');
    }

    // ================================================================
    // Plantillas — Guardar nueva
    // ================================================================
    public function templateStore(): void
    {
        $user = $this->requireAdmin();
        $this->verifyCsrf();

        $data = $this->getTemplateFormData();

        if (!$data['slug'] || !$data['name']) {
            Auth::flash('error', 'Slug y nombre son obligatorios.');
            View::redirect('/admin/templates/create');
        }

        if (BotTemplate::findBySlug($data['slug'])) {
            Auth::flash('error', 'Ya existe una plantilla con ese slug.');
            View::redirect('/admin/templates/create');
        }

        BotTemplate::create($data);
        Auth::flash('success', 'Plantilla creada correctamente.');
        View::redirect('/admin/templates');
    }

    // ================================================================
    // Plantillas — Editar (formulario)
    // ================================================================
    public function templateEdit(string $id): void
    {
        $user = $this->requireAdmin();
        $template = BotTemplate::find((int)$id);
        if (!$template) {
            Auth::flash('error', 'Plantilla no encontrada.');
            View::redirect('/admin/templates');
        }
        View::render('admin/templates/form', compact('user', 'template'), 'admin');
    }

    // ================================================================
    // Plantillas — Actualizar
    // ================================================================
    public function templateUpdate(string $id): void
    {
        $user = $this->requireAdmin();
        $this->verifyCsrf();

        $template = BotTemplate::find((int)$id);
        if (!$template) {
            Auth::flash('error', 'Plantilla no encontrada.');
            View::redirect('/admin/templates');
        }

        $data = $this->getTemplateFormData();

        // Si cambió el slug, verificar unicidad
        if ($data['slug'] !== $template['slug']) {
            $existing = BotTemplate::findBySlug($data['slug']);
            if ($existing) {
                Auth::flash('error', 'Ya existe otra plantilla con ese slug.');
                View::redirect('/admin/templates/' . $id . '/edit');
            }
        }

        BotTemplate::update((int)$id, $data);
        Auth::flash('success', 'Plantilla actualizada correctamente.');
        View::redirect('/admin/templates');
    }

    // ================================================================
    // Plantillas — Eliminar
    // ================================================================
    public function templateDelete(string $id): void
    {
        $user = $this->requireAdmin();
        $this->verifyCsrf();

        $template = BotTemplate::find((int)$id);
        if (!$template) {
            Auth::flash('error', 'Plantilla no encontrada.');
            View::redirect('/admin/templates');
        }

        BotTemplate::delete((int)$id);
        Auth::flash('success', 'Plantilla eliminada.');
        View::redirect('/admin/templates');
    }

    // ================================================================
    // Planes
    // ================================================================
    public function plans(): void
    {
        $user = $this->requireAdmin();
        $plans = Database::fetchAll('SELECT * FROM plans ORDER BY sort_order');
        View::render('admin/plans', compact('user', 'plans'), 'admin');
    }

    // ================================================================
    // Helper: extraer datos del formulario de plantilla
    // ================================================================
    private function getTemplateFormData(): array
    {
        return [
            'slug'               => trim($_POST['slug'] ?? ''),
            'name'               => trim($_POST['name'] ?? ''),
            'description'        => trim($_POST['description'] ?? ''),
            'short_description'  => trim($_POST['short_description'] ?? ''),
            'platform'           => $_POST['platform'] ?? 'telegram',
            'category'           => trim($_POST['category'] ?? 'utility'),
            'icon'               => trim($_POST['icon'] ?? '🤖'),
            'docker_image'       => trim($_POST['docker_image'] ?? 'python:3.11-slim'),
            'git_repo_url'       => trim($_POST['git_repo_url'] ?? '') ?: null,
            'default_env_vars'   => trim($_POST['default_env_vars'] ?? '{}'),
            'required_env_vars'  => trim($_POST['required_env_vars'] ?? '[]'),
            'ram_mb_min'         => (int)($_POST['ram_mb_min'] ?? 128),
            'min_plan_slug'      => $_POST['min_plan_slug'] ?? 'free',
            'difficulty'         => $_POST['difficulty'] ?? 'easy',
            'tags'               => trim($_POST['tags'] ?? ''),
            'documentation_url'  => trim($_POST['documentation_url'] ?? '') ?: null,
            'setup_instructions' => trim($_POST['setup_instructions'] ?? '') ?: null,
            'is_featured'        => isset($_POST['is_featured']) ? 1 : 0,
            'is_active'          => isset($_POST['is_active']) ? 1 : 0,
            'sort_order'         => (int)($_POST['sort_order'] ?? 0),
        ];
    }
}
