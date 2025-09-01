<?php
$this->extend('client/template_portal');
$this->section('window_title');
echo 'Bienvenido al Portal de Atención - Austrobank';
$this->endSection();
$this->section('css_block');
?>
<style>
    .welcome-hero {
        position: relative;
        padding: 5rem 1rem;
        background-color: #f8f9fa;
        background-image: url('https://images.unsplash.com/photo-1554224155-1696413565d3?q=80&w=2070&auto=format&fit=crop');
        background-size: cover;
        background-position: center;
        color: #fff;
        text-align: center;
    }
    .welcome-hero::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: rgba(0, 30, 80, 0.7);
    }
    .welcome-hero .container {
        position: relative;
        z-index: 2;
    }
    .welcome-hero h1 { font-size: 2.8rem; font-weight: 700; }
    .welcome-hero .lead { font-size: 1.25rem; margin-top: 1rem; max-width: 700px; margin-left: auto; margin-right: auto; }
    .features-section { padding: 4rem 0; }
    .feature-item { text-align: center; }
    .feature-item .icon { font-size: 3rem; color: #0056b3; margin-bottom: 1rem; }
</style>
<?php
$this->endSection();

$this->section('content');
?>

<div class="welcome-hero">
    <div class="container">
        <h1 class="display-4">Bienvenidos al Sistema de Tickets Austrobank</h1>
        <p class="lead">
            Un servicio centralizado para gestionar todas sus solicitudes de soporte de manera eficiente y transparente.
        </p>
        <a href="<?= site_url('portal/abrir-ticket') ?>" class="btn btn-primary btn-lg mt-4">
            <i class="fas fa-pencil-alt mr-2"></i>Crear un Ticket Ahora
        </a>
    </div>
</div>

<div class="features-section bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-4 feature-item mb-4">
                <div class="icon"><i class="fas fa-ticket-alt"></i></div>
                <h4>Seguimiento Fácil</h4>
                <p class="text-muted">A cada solicitud se le asigna un número de ticket único para que pueda rastrear el progreso y las respuestas en línea.</p>
            </div>
            <div class="col-md-4 feature-item mb-4">
                <div class="icon"><i class="fas fa-history"></i></div>
                <h4>Historial Completo</h4>
                <p class="text-muted">Proporcionamos archivos completos e historial de todas sus solicitudes de soporte para su referencia.</p>
            </div>
            <div class="col-md-4 feature-item mb-4">
                <div class="icon"><i class="fas fa-user-circle"></i></div>
                <h4>Cuenta Personal</h4>
                <p class="text-muted">Cree una cuenta para gestionar todos sus tickets desde un solo lugar y agilizar futuras solicitudes.</p>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>