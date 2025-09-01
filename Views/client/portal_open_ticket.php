<?php
$this->extend('client/template_portal');
$this->section('window_title');
echo 'Abrir un Nuevo Ticket - Austrobank';
$this->endSection();
?>

<?php $this->section('content'); ?>
<div class="container" style="max-width: 800px; margin-top: 3rem; margin-bottom: 3rem;">
    <div class="card shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="fas fa-pencil-alt fa-3x text-primary mb-3"></i>
                <h2>Nueva Solicitud de Soporte</h2>
                <p class="text-muted">Completa el formulario para crear un nuevo ticket.</p>
            </div>
            
            <?php if (session('validation')): ?>
                <div class="alert alert-danger"><?= session('validation')->listErrors() ?></div>
            <?php endif; ?>

            <?= form_open_multipart(route_to('portal_austrobank_send')) ?>
                <?= csrf_field() ?>

                <h5 class="form-section-title">Tus Datos</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fullname">Nombre Completo</label>
                            <input type="text" name="fullname" id="fullname" class="form-control" value="<?= client_online() ? esc(client_data('fullname')) : old('fullname') ?>" <?= client_online() ? 'readonly' : 'required' ?>>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Correo Electrónico</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?= client_online() ? esc(client_data('email')) : old('email') ?>" <?= client_online() ? 'readonly' : 'required' ?>>
                        </div>
                    </div>
                </div>

                <h5 class="form-section-title mt-4">Detalles de la Solicitud</h5>
                <div class="form-group">
                    <label for="subject">Asunto</label>
                    <input type="text" name="subject" id="subject" class="form-control" value="<?= old('subject') ?>" required>
                </div>
                <div class="form-group">
                    <label for="message">Describe tu solicitud</label>
                    <textarea name="message" id="message" rows="6" class="form-control" required><?= old('message') ?></textarea>
                </div>

                <h5 class="form-section-title mt-4">Archivos Adjuntos</h5>
                <div id="attachment-container">
                    <div class="form-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="attachment[]" id="attachment1">
                            <label class="custom-file-label" for="attachment1">Elegir archivo...</label>
                        </div>
                    </div>
                </div>
                <button type="button" id="add-attachment" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-plus mr-1"></i> Añadir otro archivo
                </button>
                <hr class="my-4">

                <?php if (!client_online()): // --- LÓGICA CONDICIONAL: MUESTRA EL BLOQUE SOLO SI NO HA INICIADO SESIÓN --- ?>
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
                    <?php endif; ?>

                <button type="submit" id="submit-btn" class="btn btn-austro btn-block btn-lg" <?= !client_online() ? 'disabled' : '' ?>>Enviar Ticket</button>
            <?= form_close() ?>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>

<?php $this->section('script_block'); ?>
<script>
$(document).ready(function() {
    
    // --- LÓGICA PARA ARCHIVOS ADJUNTOS ---
    let attachmentCount = 1;
    function updateFileLabel() {
        $('.custom-file-input').on('change', function(e) {
            let fileName = e.target.files[0] ? e.target.files[0].name : 'Elegir archivo...';
            $(this).next('.custom-file-label').text(fileName);
        });
    }
    updateFileLabel();
    $('#add-attachment').on('click', function() {
        attachmentCount++;
        let newAttachment = `<div class="form-group mt-2"><div class="custom-file"><input type="file" class="custom-file-input" name="attachment[]" id="attachment${attachmentCount}"><label class="custom-file-label" for="attachment${attachmentCount}">Elegir archivo...</label></div></div>`;
        $('#attachment-container').append(newAttachment);
        updateFileLabel();
    });

    // --- LÓGICA DEL CAPTCHA (SOLO SE EJECUTA SI LOS ELEMENTOS EXISTEN) ---
    if ($('#captcha-input').length) {
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
                    feedbackText.text('¡Correcto! Ya puedes enviar el formulario.').removeClass('text-danger').addClass('text-success');
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
    }
});
</script>
<?php $this->endSection(); ?>