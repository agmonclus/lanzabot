<?php $pageTitle = 'Nuevo bot'; ?>
<div class="page-header">
    <div>
        <h1>Desplegar nuevo bot</h1>
        <p class="text-muted">Completa los datos básicos para empezar</p>
    </div>
</div>

<div class="form-card">
    <form method="POST" action="<?= APP_URL ?>/bots">
        <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">

        <div class="form-group">
            <label for="name">Nombre del bot <span class="required">*</span></label>
            <input type="text" id="name" name="name" class="form-control" placeholder="Mi bot de ventas" required autofocus>
        </div>

        <div class="form-group">
            <label>Plataforma</label>
            <div class="platform-selector">
                <label class="platform-option">
                    <input type="radio" name="platform" value="telegram" checked>
                    <span>✈️ Telegram</span>
                </label>
                <label class="platform-option">
                    <input type="radio" name="platform" value="discord">
                    <span>🎮 Discord</span>
                </label>
                <label class="platform-option">
                    <input type="radio" name="platform" value="other">
                    <span>⚙️ Otro</span>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label for="docker_image">Imagen Docker</label>
            <input type="text" id="docker_image" name="docker_image" class="form-control" value="python:3.11-slim" placeholder="python:3.11-slim">
            <small class="form-hint">Imagen base para tu bot. Ejemplos: <code>python:3.11-slim</code>, <code>node:20-alpine</code></small>
        </div>

        <div class="form-group">
            <label for="description">Descripción (opcional)</label>
            <textarea id="description" name="description" class="form-control" rows="2" placeholder="Qué hace este bot..."></textarea>
        </div>

        <div class="form-actions">
            <a href="<?= APP_URL ?>/dashboard" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary">Crear bot →</button>
        </div>
    </form>
</div>
