<?php
$this->extend('client/template_portal');
$this->section('window_title');
echo 'Consulta de Ticket #' . esc($ticket->id);
$this->endSection();

$this->section('content');

// Lógica para traducir el estado del ticket
$status_list = [
    1 => ['text' => 'Abierto', 'color' => 'success'],
    2 => ['text' => 'Respondido', 'color' => 'primary'],
    3 => ['text' => 'Esperando Respuesta', 'color' => 'info'],
    4 => ['text' => 'En Proceso', 'color' => 'warning'],
    5 => ['text' => 'Cerrado', 'color' => 'secondary']
];
$ticket_status = $status_list[$ticket->status] ?? ['text' => 'Desconocido', 'color' => 'dark'];
?>

<div class="container my-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <h1 class="h3 font-weight-bold mb-3 mb-md-0">Detalle del Ticket <span class="text-primary">#<?= esc($ticket->id) ?></span></h1>
        <a href="<?= site_url('portal-atencion-cliente') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Volver al Portal
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <h4 class="card-title mb-3"><?= esc($ticket->subject) ?></h4>
            <div class="row">
                <div class="col-md-4 mb-2 mb-md-0">
                    <div class="text-muted small">ESTADO</div>
                    <span class="badge badge-<?= $ticket_status['color'] ?> p-2 font-weight-bold"><?= esc($ticket_status['text']) ?></span>
                </div>
                <div class="col-md-4 mb-2 mb-md-0">
                    <div class="text-muted small">DEPARTAMENTO</div>
                    <div class="font-weight-bold"><?= esc($ticket->department_name) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">ÚLTIMA ACTUALIZACIÓN</div>
                    <div class="font-weight-bold"><?= dateFormat($ticket->last_update) ?></div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="h4 mb-3"><i class="fas fa-comments text-muted mr-2"></i>Historial de Conversación</h3>
    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $message): ?>
            <div class="card mb-3 <?= ($message->customer == 1) ? 'message-user' : 'message-staff' ?>">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas <?= ($message->customer == 1) ? 'fa-user-circle' : 'fa-headset' ?> mr-2"></i>
                        <strong><?= ($message->customer == 1) ? esc($ticket->fullname) : 'Soporte Austrobank' ?></strong>
                    </span>
                    <small class="text-muted"><?= dateFormat($message->date) ?></small>
                </div>
                <div class="card-body">
                    <div class="message-content">
                        <?= $message->message ?>
                    </div>
                     <?php if ($files = ticket_files($ticket->id, $message->id)): ?>
                        <hr>
                        <div class="attachments-section">
                            <strong>Archivos Adjuntos:</strong>
                            <ul class="list-unstyled mt-2">
                                <?php foreach ($files as $file): ?>
                                    <li>
                                        <a href="<?= current_url(true)->setQuery('download='.$file->id.'&ticket_id='.$ticket->id) ?>">
                                            <i class="fas fa-paperclip mr-2"></i>
                                            <?= esc($file->name) ?> (<?= number_to_size($file->filesize, 2) ?>)
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center text-muted">No hay mensajes en este ticket todavía.</p>
    <?php endif; ?>
    
    <div class="alert alert-info text-center border mt-5">
        <i class="fas fa-info-circle mr-2"></i>
        Para responder a este ticket, por favor <a href="<?= site_url('portal/login') ?>">inicia sesión</a> o <a href="<?= site_url('portal/activar-cuenta') ?>">activa tu cuenta</a>.
    </div>
    </div>

<?php $this->endSection(); ?>