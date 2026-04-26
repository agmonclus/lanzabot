<?php $pageTitle = 'Eliminar base de datos'; ?>

<div class="page-header">
    <div>
        <a href="<?= APP_URL ?>/databases/<?= $db['id'] ?>" class="breadcrumb">← Volver</a>
        <h1>Eliminar base de datos</h1>
    </div>
</div>

<section class="section" style="max-width:520px">
    <div class="flash flash-error" style="margin-bottom:1.5rem">
        <strong>Acción irreversible.</strong>
        Se eliminarán <strong>todos los datos</strong> de la base de datos
        "<strong><?= \App\Core\View::e($db['label']) ?></strong>" de forma permanente.
        Esta acción no se puede deshacer.
    </div>

    <p>Para confirmar, escribe el nombre exacto de la base de datos:</p>
    <p style="font-size:1.1rem;font-weight:700;font-family:monospace;background:var(--surface);padding:.5rem .75rem;border-radius:6px;display:inline-block">
        <?= \App\Core\View::e($db['label']) ?>
    </p>

    <form method="POST" action="<?= APP_URL ?>/databases/<?= $db['id'] ?>/delete" style="margin-top:1rem">
        <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">

        <div class="form-group">
            <input
                type="text"
                name="confirm_name"
                class="form-control"
                placeholder="Escribe el nombre exacto aquí"
                required
                autofocus
                autocomplete="off"
                style="max-width:340px"
            >
        </div>

        <div style="display:flex;gap:.75rem;margin-top:1rem">
            <button type="submit" class="btn btn-danger">Eliminar definitivamente</button>
            <a href="<?= APP_URL ?>/databases/<?= $db['id'] ?>" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</section>
