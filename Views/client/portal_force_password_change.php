<?php
$this->extend('client/template_portal');
$this->section('window_title');
echo 'Actualizar Contraseña - Austrobank';
$this->endSection();
?>

<?php $this->section('content'); ?>
<div class="container" style="max-width: 550px; margin-top: 4rem; margin-bottom: 4rem;">
    <div class="auth-card">
        <div class="text-center mb-4">
            <h2 class="mt-3">Actualiza tu Contraseña</h2>
            <p class="text-muted">Por tu seguridad, es necesario que establezcas una nueva contraseña personal para continuar.</p>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <?php if (session('validation')): ?>
            <div class="alert alert-danger"><?= session('validation')->listErrors() ?></div>
        <?php endif; ?>

        <?= form_open(site_url('portal/forzar-cambio-clave')) ?>
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="current_password">Contraseña Temporal (la que recibiste por correo)</label>
                <input type="password" name="current_password" id="current_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="new_password">Nueva Contraseña (mínimo 6 caracteres)</label>
                <input type="password" name="new_password" id="new_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="new_password_confirm">Confirmar Nueva Contraseña</label>
                <input type="password" name="new_password_confirm" id="new_password_confirm" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-austro btn-block">Guardar y Continuar al Panel</button>
        <?= form_close() ?>
    </div>
</div>
<?php $this->endSection(); ?>