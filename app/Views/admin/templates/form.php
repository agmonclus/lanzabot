<?php
$pageTitle   = $template ? 'Editar plantilla' : 'Nueva plantilla';
$isEdit      = (bool)$template;
$formAction  = $isEdit ? APP_URL . '/admin/templates/' . $template['id'] . '/update' : APP_URL . '/admin/templates';
$t           = $template ?? [];
?>
<div class="page-header">
    <div>
        <a href="<?= APP_URL ?>/admin/templates" class="breadcrumb">← Plantillas</a>
        <h1><?= $isEdit ? 'Editar' : 'Nueva' ?> plantilla</h1>
    </div>
</div>

<div class="form-card" style="max-width: 800px">
    <form method="POST" action="<?= $formAction ?>">
        <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">

        <div class="form-row">
            <div class="form-group" style="flex:1">
                <label for="name">Nombre <span class="required">*</span></label>
                <input type="text" id="name" name="name" class="form-control" value="<?= \App\Core\View::e($t['name'] ?? '') ?>" required>
            </div>
            <div class="form-group" style="flex:1">
                <label for="slug">Slug <span class="required">*</span></label>
                <input type="text" id="slug" name="slug" class="form-control" value="<?= \App\Core\View::e($t['slug'] ?? '') ?>" required pattern="[a-z0-9\-]+">
                <small class="form-hint">Solo minúsculas, números y guiones</small>
            </div>
        </div>

        <div class="form-group">
            <label for="short_description">Descripción corta</label>
            <input type="text" id="short_description" name="short_description" class="form-control" value="<?= \App\Core\View::e($t['short_description'] ?? '') ?>" maxlength="255">
        </div>

        <div class="form-group">
            <label for="description">Descripción completa</label>
            <textarea id="description" name="description" class="form-control" rows="4"><?= \App\Core\View::e($t['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group" style="flex:1">
                <label for="platform">Plataforma</label>
                <select id="platform" name="platform" class="form-control">
                    <?php foreach (['telegram','discord','slack','whatsapp','twitch','matrix','reddit','mastodon','multi','other'] as $p): ?>
                    <option value="<?= $p ?>" <?= ($t['platform'] ?? 'telegram') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex:1">
                <label for="category">Categoría / Funcionalidad</label>
                <select id="category" name="category" class="form-control">
                    <?php
                    $cats = [
                        'ai' => 'IA y Agentes', 'communication' => 'Comunicaciones',
                        'finance' => 'Finanzas y Comercio', 'moderation' => 'Moderación y Seguridad',
                        'marketing' => 'Marketing y Desarrollo',
                        'productivity' => 'Productividad', 'commerce' => 'Comercio',
                        'ecommerce' => 'E-Commerce', 'entertainment' => 'Entretenimiento',
                        'gaming' => 'Gaming', 'security' => 'Seguridad',
                        'social' => 'Social', 'education' => 'Educación',
                        'monitoring' => 'Monitoreo', 'developer' => 'Desarrollo', 'utility' => 'Utilidad',
                        'starter' => 'Inicio'
                    ];
                    foreach ($cats as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($t['category'] ?? 'utility') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="width:80px">
                <label for="icon">Icono</label>
                <input type="text" id="icon" name="icon" class="form-control" value="<?= \App\Core\View::e($t['icon'] ?? '🤖') ?>" maxlength="10" style="text-align:center;font-size:1.3rem">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="flex:1">
                <label for="docker_image">Imagen Docker</label>
                <input type="text" id="docker_image" name="docker_image" class="form-control" value="<?= \App\Core\View::e($t['docker_image'] ?? 'python:3.11-slim') ?>">
            </div>
            <div class="form-group" style="flex:1">
                <label for="git_repo_url">URL repositorio Git</label>
                <input type="url" id="git_repo_url" name="git_repo_url" class="form-control" value="<?= \App\Core\View::e($t['git_repo_url'] ?? '') ?>" placeholder="https://github.com/...">
            </div>
        </div>

        <div class="form-group">
            <label for="default_env_vars">Variables de entorno por defecto (JSON)</label>
            <textarea id="default_env_vars" name="default_env_vars" class="form-control code-editor" rows="3"><?= \App\Core\View::e($t['default_env_vars'] ?? '{}') ?></textarea>
            <small class="form-hint">Formato: {"CLAVE": "valor_ejemplo", "OTRA": ""}</small>
        </div>

        <div class="form-group">
            <label for="required_env_vars">Variables requeridas (JSON array)</label>
            <textarea id="required_env_vars" name="required_env_vars" class="form-control code-editor" rows="3"><?= \App\Core\View::e($t['required_env_vars'] ?? '[]') ?></textarea>
            <small class="form-hint">Formato: [{"key": "BOT_TOKEN", "label": "Token del bot", "placeholder": "abc...", "required": true}]</small>
        </div>

        <div class="form-row">
            <div class="form-group" style="flex:1">
                <label for="ram_mb_min">RAM mínima (MB)</label>
                <input type="number" id="ram_mb_min" name="ram_mb_min" class="form-control" value="<?= (int)($t['ram_mb_min'] ?? 128) ?>" min="64">
            </div>
            <div class="form-group" style="flex:1">
                <label for="min_plan_slug">Plan mínimo</label>
                <select id="min_plan_slug" name="min_plan_slug" class="form-control">
                    <?php foreach (['free','starter','medium','pro'] as $slug): ?>
                    <option value="<?= $slug ?>" <?= ($t['min_plan_slug'] ?? 'free') === $slug ? 'selected' : '' ?>><?= ucfirst($slug) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex:1">
                <label for="difficulty">Dificultad</label>
                <select id="difficulty" name="difficulty" class="form-control">
                    <?php foreach (['easy','medium','advanced'] as $d): ?>
                    <option value="<?= $d ?>" <?= ($t['difficulty'] ?? 'easy') === $d ? 'selected' : '' ?>><?= ucfirst($d) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="tags">Tags (separados por coma)</label>
            <input type="text" id="tags" name="tags" class="form-control" value="<?= \App\Core\View::e($t['tags'] ?? '') ?>" placeholder="telegram, python, ai, bot">
        </div>

        <div class="form-group">
            <label for="documentation_url">URL documentación</label>
            <input type="url" id="documentation_url" name="documentation_url" class="form-control" value="<?= \App\Core\View::e($t['documentation_url'] ?? '') ?>" placeholder="https://docs.example.com">
        </div>

        <div class="form-group">
            <label for="more_info_url">URL más información (home del fabricante)</label>
            <input type="url" id="more_info_url" name="more_info_url" class="form-control" value="<?= \App\Core\View::e($t['more_info_url'] ?? '') ?>" placeholder="https://proyecto.example.com">
            <small class="form-hint">Enlace a la página principal del proyecto o fabricante del bot</small>
        </div>

        <div class="form-group">
            <label for="setup_instructions">Instrucciones de configuración</label>
            <textarea id="setup_instructions" name="setup_instructions" class="form-control" rows="5" placeholder="Paso a paso para configurar este bot..."><?= \App\Core\View::e($t['setup_instructions'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group" style="width:100px">
                <label for="sort_order">Orden</label>
                <input type="number" id="sort_order" name="sort_order" class="form-control" value="<?= (int)($t['sort_order'] ?? 0) ?>">
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:1.5rem;padding-top:1.5rem">
                <label class="toggle-label">
                    <input type="checkbox" name="is_featured" <?= !empty($t['is_featured']) ? 'checked' : '' ?>>
                    <span>⭐ Destacada</span>
                </label>
                <label class="toggle-label">
                    <input type="checkbox" name="is_active" <?= ($t['is_active'] ?? 1) ? 'checked' : '' ?>>
                    <span>✅ Activa</span>
                </label>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= APP_URL ?>/admin/templates" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Guardar cambios' : 'Crear plantilla' ?></button>
        </div>
    </form>
</div>
