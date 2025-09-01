<?php
/**
 * @var $this \CodeIgniter\View\View
 */
$this->extend('staff/template');
$this->section('content');
?>
    <!-- Page Header -->
    <div class="page-header row no-gutters py-4">
        <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
            <span class="text-uppercase page-subtitle"><?php echo lang('Reportes');?></span>
            <h3 class="page-title"><?php echo lang('Atención al cliente');?></h3>
        </div>
    </div>
    <!-- End Page Header -->

    <?php
        if(isset($error_msg)){
            echo '<div class="alert alert-danger">'.$error_msg.'</div>';
        }
        if(isset($success_msg)){
            echo '<div class="alert alert-success">'.$success_msg.'</div>';
        }
    ?>

    <!-- Iframe oculto para descarga -->
    <iframe id="downloadFrame" name="downloadFrame" style="display:none;"></iframe>

    <div class="d-flex justify-content-center align-items-end" style="min-height: 20vh;">
        <div class="card col-md-6" style="width: 100%; max-width: 600px; margin-bottom: 20px;">
            <div class="card-body">
                <?php
                echo form_open(site_url(route_to('report_generate')), ['method' => 'post', 'id' => 'reportForm'], ['do' => 'submit']);
                ?>
                    <div class="form-group">
                        <label><?php echo lang('Fecha Inicio:'); ?></label>
                        <input type="text" name="fechaInicio" class="form-control" placeholder="dd/mm/yyyy"
                            value="<?php echo set_value('fechaInicio',isset($param) ? $param->param_text : '' );?>">
                    </div>

                    <div class="form-group">
                        <label><?php echo lang('Fecha Fin:'); ?></label>
                        <input type="text" name="fechaFin" class="form-control" placeholder="dd/mm/yyyy"
                            value="<?php echo set_value('fechaFin',isset($param) ? $param->param_text : '' );?>">
                    </div>

                    <!--
                    <div class="form-group">
                        <label><?php echo lang('Cliente:'); ?></label>
                        <input type="text" name="cliente" class="form-control"
                            value="<?php echo set_value('cliente',isset($param) ? $param->param_text : '' );?>">
                    </div>-->
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><?php echo lang('Generar Excel');?></button>
                        <a href="<?php echo site_url(route_to('staff_params_settings'));?>" class="btn btn-secondary"><?php echo lang('Admin.form.goBack');?></a>
                    </div>
  
                <?php
                echo form_close();
                ?>
            </div>
        </div>
    </div>

    <!-- Fullscreen Loading Overlay -->
    <div id="loadingOverlay">
        <div class="spinner-container">
            <img src="<?= base_url('assets/helpdeskz/images/loading.gif') ?>" alt="Cargando..." />
        </div>
    </div>

<?php
$this->endSection();

$this->section('script_block') ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>


<style>
    /* Overlay de pantalla completa */
    #loadingOverlay {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0,0,0,0.5);
        z-index: 9999;
    }

    .spinner-container {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
    }

    .spinner-container img {
        width: 64px;
        height: 64px;
    }
</style>

<script>
    $(document).ready(function () {
        // Aplicar máscara a los inputs de fecha
        $('#fechaInicio, #fechaFin').mask('00/00/0000');
    });

    // Mostrar el loading al enviar el formulario
    $('#reportForm').on('submit', function () {
        $('#loadingOverlay').show(); // Mostrar el gif
        $('button[type="submit"]').prop('disabled', true); // Deshabilitar el botón
    });

    // Ocultar loading cuando termine de renderizar todo (útil si la misma vista se recarga con mensaje)
    $(window).on('load', function () {
        $('#loadingOverlay').fadeOut();
    });
</script>

<?= $this->endSection() ?>
