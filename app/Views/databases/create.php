<?php $pageTitle = 'Nueva base de datos'; ?>

<div class="page-header">
    <div>
        <a href="<?= APP_URL ?>/databases" class="breadcrumb">← Bases de datos</a>
        <h1>Nueva base de datos</h1>
    </div>
</div>

<?php if (!$canCreate): ?>
<div class="flash flash-error">
    Has alcanzado el límite de bases de datos de tu plan (<?= (int)$maxDbs ?>).
    <a href="<?= APP_URL ?>/plans">Actualiza tu plan</a> para crear más.
</div>
<?php else: ?>

<section class="section" style="max-width:560px">
    <form method="POST" action="<?= APP_URL ?>/databases">
        <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">

        <div class="form-group">
            <label class="form-label">Nombre descriptivo</label>
            <input
                type="text"
                name="label"
                class="form-control"
                placeholder="Ej: Mi Bot DB, tienda-prod…"
                maxlength="60"
                required
                autofocus
            >
            <small class="text-muted">Solo para identificarla en el panel. Máx. 60 caracteres.</small>
        </div>

        <div class="form-group">
            <label class="form-label">Motor de base de datos</label>
            <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:.5rem">

                <?php if ($pgEnabled): ?>
                <label style="flex:1;min-width:200px;cursor:pointer">
                    <input type="radio" name="type" value="postgresql" checked style="margin-right:.4rem">
                    <strong>🐘 PostgreSQL</strong>
                    <p class="text-muted" style="margin:.3rem 0 0 1.4rem;font-size:.85rem">
                        Relacional · SQL estándar · ideal para datos estructurados
                    </p>
                </label>
                <?php else: ?>
                <div style="flex:1;min-width:200px;opacity:.45">
                    <strong>🐘 PostgreSQL</strong>
                    <p class="text-muted" style="margin:.3rem 0 0 0;font-size:.85rem">No disponible (no configurado)</p>
                </div>
                <?php endif; ?>

                <?php if ($mongoEnabled): ?>
                <label style="flex:1;min-width:200px;cursor:pointer">
                    <input type="radio" name="type" value="mongodb" <?= !$pgEnabled ? 'checked' : '' ?> style="margin-right:.4rem">
                    <strong>🍃 MongoDB</strong>
                    <p class="text-muted" style="margin:.3rem 0 0 1.4rem;font-size:.85rem">
                        Documental · JSON nativo · ideal para datos flexibles
                    </p>
                </label>
                <?php else: ?>
                <div style="flex:1;min-width:200px;opacity:.45">
                    <strong>🍃 MongoDB</strong>
                    <p class="text-muted" style="margin:.3rem 0 0 0;font-size:.85rem">No disponible (no configurado)</p>
                </div>
                <?php endif; ?>

            </div>
            <?php if (!$pgEnabled && !$mongoEnabled): ?>
            <p class="text-muted" style="margin-top:.75rem;color:var(--danger)">
                Ningún servidor de base de datos está configurado en el sistema.
                Contacta con soporte.
            </p>
            <?php endif; ?>
        </div>

        <?php if ($pgEnabled || $mongoEnabled): ?>
        <div style="margin-top:1.5rem">
            <button type="submit" class="btn btn-primary">Crear base de datos</button>
            <a href="<?= APP_URL ?>/databases" class="btn btn-outline" style="margin-left:.5rem">Cancelar</a>
        </div>
        <?php endif; ?>
    </form>
</section>

<?php endif; ?>
