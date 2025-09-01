<?php
$this->extend('client/template_portal');
$this->section('window_title');
echo 'Detalle del Ticket #' . esc($ticket->id);
$this->endSection();
?>

<?php $this->section('content'); ?>
<div class="container my-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <h1 class="h3 font-weight-bold mb-3 mb-md-0">Detalle del Ticket <span class="text-primary">#<?= esc($ticket->id) ?></span></h1>
        <a href="<?= site_url('portal/dashboard') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Volver a Mi Panel
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <h4 class="card-title mb-3"><?= esc($ticket->subject) ?></h4>
            <div class="row">
                <div class="col-md-4 mb-2 mb-md-0">
                    <div class="text-muted small">ESTADO</div>
                    <span class="badge badge-info p-2 font-weight-bold"><?= esc($ticket_status) ?></span>
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
    <?php if ($result_data): ?>
        <?php foreach ($result_data as $item): ?>
            <div class="card mb-3 <?= ($item->customer == 1) ? 'message-user' : 'message-staff' ?>">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas <?= ($item->customer == 1) ? 'fa-user-circle' : 'fa-headset' ?> mr-2"></i>
                        <strong><?= ($item->customer == 1) ? esc($ticket->fullname) : 'Soporte Austrobank' ?></strong>
                    </span>
                    <small class="text-muted"><?= dateFormat($item->date) ?></small>
                </div>
                <div class="card-body">
                    <div class="message-content">
                        <?= $item->message ?>
                    </div>
                    <?php if ($files = ticket_files($ticket->id, $item->id)): ?>
                        <hr>
                        <div class="attachments-section">
                            <strong>Archivos Adjuntos:</strong>
                            <ul class="list-unstyled mt-2">
                                <?php foreach ($files as $file): ?>
                                    <li>
                                        <a href="<?= current_url(true)->setQuery('download='.$file->id) ?>">
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

    <?php if ($ticket->status != 5): ?>
        <div class="card shadow-sm mt-5" id="reply-section">
            <div class="card-body p-4">
                <h4 class="card-title mb-3"><i class="fas fa-reply mr-2"></i>Añadir una Respuesta</h4>
                <?php if (isset($error_msg)): ?><div class="alert alert-danger"><?= $error_msg ?></div><?php endif; ?>
                <?php if (session()->getFlashdata('form_success')): ?><div class="alert alert-success"><?= session()->getFlashdata('form_success') ?></div><?php endif; ?>
                
                <?= form_open(site_url(route_to('portal_responder_ticket', $ticket->id))) ?>
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <textarea name="message" class="form-control" rows="6" placeholder="Escribe tu respuesta aquí..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-austro btn-block py-2">
                        <i class="fas fa-paper-plane mr-2"></i> Enviar Respuesta
                    </button>
                <?= form_close() ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary text-center border mt-5">
            <i class="fas fa-lock mr-2"></i> Este ticket ha sido cerrado y ya no se pueden añadir más respuestas.
        </div>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>