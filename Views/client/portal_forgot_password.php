<?php
$this->extend('client/template_portal');
$this->section('window_title');
echo 'Recuperar Contraseña - Portal Austrobank';
$this->endSection();
?>

<?php $this->section('content'); ?>
<div class="container" style="max-width: 550px; margin-top: 4rem; margin-bottom: 4rem;">
    <div class="auth-card">
        <div class="text-center mb-4">
            <h2 class="mt-3">¿Olvidaste tu Contraseña?</h2>
            <p class="text-muted">No te preocupes. Ingresa tu correo electrónico y te enviaremos instrucciones para recuperarla.</p>
        </div>

        <?php if (session('validation')): ?>
            <div class="alert alert-danger"><?= session('validation')->listErrors() ?></div>
        <?php endif; ?>

        <?= form_open(route_to('portal_forgot_process')) ?>
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email">Correo Electrónico Registrado</label>
                <input type="email" name="email" id="email" class="form-control" value="<?= old('email') ?>" required>
            </div>

            <div class="form-group">
                <label>Código de Seguridad</label>
                <div class="d-flex align-items-center flex-wrap">
                    <div class="mr-2" id="captcha-image-container">
                        <img src="<?= $captcha_image_inline ?? '' ?>" alt="captcha" id="captcha-image">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="captcha-refresh-btn" title="Generar otro código">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <input type="text" name="captcha" id="captcha-input" class="form-control mx-3" style="width: 150px; flex-grow: 1;" required autocomplete="off" placeholder="Escribe aquí">
                    <button type="button" class="btn btn-info btn-sm" id="captcha-validate-btn">Validar</button>
                </div>
                <div id="captcha-feedback-text" class="small mt-2"></div>
            </div>
            <button type="submit" id="submit-btn" class="btn btn-austro btn-block" disabled>Recuperar Contraseña</button>

            <div class="text-center mt-3">
                <a href="<?= site_url('portal/login') ?>"><small>Regresar a Iniciar Sesión</small></a>
            </div>
        <?= form_close() ?>
    </div>
</div>
<?php $this->endSection(); ?>

<?php $this->section('script_block'); ?>
<script>
$(document).ready(function() {
    // --- LÓGICA DEL CAPTCHA DINÁMICO (copiada de los otros formularios) ---
    const csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>'; 

    const captchaValidateUrl = '<?= site_url(route_to("portal_captcha_validate")) ?>';
    const captchaRefreshUrl = '<?= site_url(route_to("portal_captcha_refresh")) ?>';

    const captchaInput = $('#captcha-input');
    const submitBtn = $('#submit-btn');
    const feedbackText = $('#captcha-feedback-text');
    const refreshBtn = $('#captcha-refresh-btn');
    const validateBtn = $('#captcha-validate-btn');
    const captchaImage = $('#captcha-image');

    refreshBtn.on('click', function() {
        $.get(captchaRefreshUrl, function(data) {
            if (data.success && data.image) {
                captchaImage.attr('src', data.image);
                captchaInput.val('');
                submitBtn.prop('disabled', true);
                feedbackText.text('').removeClass('text-success text-danger');
                csrfHash = data.csrf_hash;
                $('input[name=' + csrfName + ']').val(csrfHash);
            }
        });
    });

    validateBtn.on('click', function() {
        const userInput = captchaInput.val();
        if (!userInput) {
            feedbackText.text('Por favor, ingresa el código.').addClass('text-danger');
            return;
        }
        feedbackText.text('Validando...').removeClass('text-success text-danger');
        let postData = { 'captcha': userInput };
        postData[csrfName] = csrfHash;
        $.post(captchaValidateUrl, postData, function(response) {
            csrfHash = response.csrf_hash;
            $('input[name=' + csrfName + ']').val(csrfHash);
            if (response.success) {
                feedbackText.text('¡Correcto! Ya puedes continuar.').removeClass('text-danger').addClass('text-success');
                submitBtn.prop('disabled', false);
            } else {
                feedbackText.text('El código es incorrecto. Inténtalo de nuevo.').removeClass('text-success').addClass('text-danger');
                submitBtn.prop('disabled', true);
                refreshBtn.click();
            }
        }).fail(function() {
            feedbackText.text('Error de conexión. Por favor, intenta de nuevo.').addClass('text-danger');
        });
    });

    captchaInput.on('input', function() {
        submitBtn.prop('disabled', true);
        feedbackText.text('').removeClass('text-success text-danger');
    });
});
</script>
<?php $this->endSection(); ?>