<?php
$this->extend('client/template_portal');
$this->section('window_title');
echo 'Consultar Ticket - Austrobank';
$this->endSection();
?>

<?php $this->section('content'); ?>
<div class="container" style="max-width: 700px; margin-top: 3rem; margin-bottom: 3rem;">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h2 class="text-center mb-4">Consultar Ticket Existente</h2>

            <?php if (session('validation')): ?>
                <div class="alert alert-danger"><?= session('validation')->listErrors() ?></div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('error_check')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error_check') ?></div>
            <?php endif; ?>

            <?= form_open(route_to('portal_austrobank_check')) ?>
                <?= csrf_field() ?>
                <div class="form-group">
                    <label><strong>Número de Ticket</strong></label>
                    <input type="text" name="check_ticket_id" class="form-control" placeholder="Ej: 12345" value="<?= old('check_ticket_id') ?>" required>
                </div>

                <div class="form-group">
                    <label><strong>Código de Seguridad</strong></label>
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
                <button type="submit" id="submit-btn" class="btn btn-austro btn-block mt-4" disabled>Consultar</button>
            <?= form_close() ?>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>

<?php $this->section('script_block'); ?>
<script>
$(document).ready(function() {
    // --- LÓGICA DEL CAPTCHA DINÁMICO ---
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

    // 1. Lógica para refrescar la imagen
    refreshBtn.on('click', function() {
        $.get(captchaRefreshUrl, function(data) {
            if (data.success && data.image) {
                captchaImage.attr('src', data.image);
                captchaInput.val('');
                submitBtn.prop('disabled', true);
                feedbackText.text('').removeClass('text-success text-danger');
                
                // Actualizamos el token CSRF
                csrfHash = data.csrf_hash;
                $('input[name=' + csrfName + ']').val(csrfHash);
            }
        });
    });

    // 2. Lógica para el botón de "Validar"
    validateBtn.on('click', function() {
        const userInput = captchaInput.val();
        if (!userInput) {
            feedbackText.text('Por favor, ingresa el código.').addClass('text-danger');
            return;
        }

        feedbackText.text('Validando...').removeClass('text-success text-danger');
        
        let postData = { 'captcha': userInput };
        postData[csrfName] = csrfHash; // Usamos el token guardado

        $.post(captchaValidateUrl, postData, function(response) {
            // Actualizamos el token CSRF con la respuesta
            csrfHash = response.csrf_hash;
            $('input[name=' + csrfName + ']').val(csrfHash);
            
            if (response.success) {
                feedbackText.text('¡Correcto! Ya puedes consultar tu ticket.').removeClass('text-danger').addClass('text-success');
                submitBtn.prop('disabled', false); // ¡Habilita el botón!
            } else {
                feedbackText.text('El código es incorrecto. Inténtalo de nuevo.').removeClass('text-success').addClass('text-danger');
                submitBtn.prop('disabled', true);
                refreshBtn.click(); // Refresca automáticamente si es incorrecto
            }
        }).fail(function() {
            feedbackText.text('Error de conexión. Por favor, intenta de nuevo.').addClass('text-danger');
        });
    });
    
    // 3. Si el usuario modifica el campo después de validar, se debe re-validar
    captchaInput.on('input', function() {
        submitBtn.prop('disabled', true); // Deshabilita el botón si se cambia el texto
        feedbackText.text('').removeClass('text-success text-danger');
    });
});
</script>
<?php $this->endSection(); ?>