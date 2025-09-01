<?php
$this->extend('client/template_portal');
$this->section('window_title');
echo 'Ticket Enviado Exitosamente - Austrobank';
$this->endSection();
$this->section('css_block');
?>
<style>
    .confirmation-card {
        max-width: 600px;
        margin: 4rem auto;
        border: 1px solid #d6e9c6;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.05);
    }
    .confirmation-icon {
        font-size: 4rem;
        color: #28a745; /* verde de éxito */
    }
    .ticket-number {
        font-size: 1.75rem;
        font-weight: 700;
        color: #0056b3; /* color primario de Austrobank */
        background-color: #f0f5fa;
        padding: 0.5rem 1rem;
        border-radius: 0.25rem;
        display: inline-block;
        margin-top: 1rem;
    }
</style>
<?php
$this->endSection();

$this->section('content');
?>

<div class="container">
    <div class="card confirmation-card">
        <div class="card-body text-center p-5">
            <div class="confirmation-icon mb-3">
                <i class="fas fa-check-circle"></i>
            </div>

            <h1 class="h3">¡Tu ticket ha sido enviado satisfactoriamente!</h1>

            <p class="text-muted mt-3">
                Hemos recibido tu solicitud y nuestro equipo se pondrá en contacto contigo a la brevedad posible.
                Guarda el siguiente número de ticket para futuras referencias.
            </p>

            <p class="mt-4">Tu número de ticket es:</p>
            <div class="ticket-number">
                #<?= esc($ticket->id) ?>
            </div>

            <hr class="my-4">

            <?php if (client_online()): // <-- INICIO DE LA CONDICIÓN ?>

                <a href="<?= site_url(route_to('portal_dashboard')) ?>" class="btn btn-primary">
                    <i class="fas fa-user-circle mr-2"></i>Ir a Mi Panel
                </a>

            <?php else: ?>

                <a href="<?= site_url('portal-atencion-cliente') ?>" class="btn btn-primary">
                    <i class="fas fa-home mr-2"></i>Volver al Inicio
                </a>
                <a href="<?= site_url('portal/ver-ticket') ?>" class="btn btn-outline-secondary">
                    Consultar otro ticket
                </a>

            <?php endif; // <-- FIN DE LA CONDICIÓN ?>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>