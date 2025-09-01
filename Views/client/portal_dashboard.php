<?php
$this->extend('client/template_portal');
$this->section('window_title');
echo 'Mi Panel de Tickets - Austrobank';
$this->endSection();
$this->section('css_block');
?>
<style>
    .filter-card {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: .5rem;
    }
    .ticket-list-card {
        transition: all 0.2s ease-in-out;
    }
    .ticket-list-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.1);
    }
    .ticket-status-badge {
        font-size: 0.8rem;
        font-weight: 600;
        padding: .4em .8em;
    }
    .ticket-action-btn {
        font-size: 0.9rem;
        padding: .4rem .9rem;
    }
</style>
<?php
$this->endSection();

$this->section('content');
?>
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">Mi Panel de Tickets</h1>
            <p class="text-muted mb-0">¡Hola, <strong><?= esc($client->getData('fullname')) ?></strong>! Aquí puedes ver y gestionar todas tus solicitudes.</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('success_send')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success_send') ?></div>
    <?php endif; ?>

    <div class="card filter-card mb-4">
        <div class="card-body">
            <?= form_open(site_url('portal/dashboard'), ['method' => 'get']) ?>
            <div class="row align-items-end">
                <div class="col-md-5">
                    <div class="form-group mb-md-0">
                        <label for="code">Buscar por ID o Asunto</label>
                        <input type="text" name="code" id="code" class="form-control" value="<?= esc(service('request')->getGet('code')) ?>" placeholder="Ej: 12345 o 'problema con...'">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group mb-md-0">
                        <label for="status">Estado del Ticket</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">Todos los Estados</option>
                            <option value="1" <?= service('request')->getGet('status') == '1' ? 'selected' : '' ?>>Abierto</option>
                            <option value="2" <?= service('request')->getGet('status') == '2' ? 'selected' : '' ?>>Respondido</option>
                            <option value="3" <?= service('request')->getGet('status') == '3' ? 'selected' : '' ?>>Esperando Respuesta</option>
                            <option value="4" <?= service('request')->getGet('status') == '4' ? 'selected' : '' ?>>En Proceso</option>
                            <option value="5" <?= service('request')->getGet('status') == '5' ? 'selected' : '' ?>>Cerrado</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary btn-block">
                        <i class="fas fa-filter mr-1"></i> Filtrar
                    </button>
                </div>
            </div>
            <?= form_close() ?>
        </div>
    </div>
    <div class="ticket-list">
        <?php if ($result_data): ?>
            <?php foreach ($result_data as $item): ?>
                <div class="card ticket-list-card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-1">
                                    <a href="<?= site_url(route_to('portal_ver_ticket', $item->id)) ?>" class="text-dark font-weight-bold">
                                        <?= esc(resume_content($item->subject, 60)) ?>
                                    </a>
                                </h5>
                                <small class="text-muted">
                                    Ticket #<?= $item->id ?> &bull; Departamento: <?= esc($item->department_name) ?> &bull; Última actualización: <?= dateFormat($item->last_update) ?>
                                </small>
                            </div>
                            <div class="col-md-3 text-center text-md-left mt-2 mt-md-0">
                                <?php
                                    $status_map = [
                                        1 => ['text' => 'Abierto', 'class' => 'badge-success', 'icon' => 'fa-exclamation-circle'],
                                        2 => ['text' => 'Respondido', 'class' => 'badge-dark', 'icon' => 'fa-reply'],
                                        3 => ['text' => 'Esperando Respuesta', 'class' => 'badge-warning', 'icon' => 'fa-clock'],
                                        4 => ['text' => 'En Proceso', 'class' => 'badge-info', 'icon' => 'fa-sync-alt'],
                                        5 => ['text' => 'Cerrado', 'class' => 'badge-secondary', 'icon' => 'fa-lock']
                                    ];
                                    $status = $status_map[$item->status] ?? ['text' => 'Desconocido', 'class' => 'badge-light', 'icon' => 'fa-question-circle'];
                                ?>
                                <span class="badge ticket-status-badge <?= $status['class'] ?>">
                                    <i class="fas <?= $status['icon'] ?> mr-1"></i> <?= $status['text'] ?>
                                </span>
                            </div>
                            <div class="col-md-3 text-center text-md-right mt-3 mt-md-0">
                                <a href="<?= site_url(route_to('portal_ver_ticket', $item->id)) ?>" class="btn btn-outline-primary ticket-action-btn">
                                    <i class="fas fa-eye mr-1"></i> Ver Ticket
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No se encontraron tickets</h4>
                    <p>No tienes tickets que coincidan con los filtros actuales. ¡Intenta crear uno nuevo!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($pager->getPageCount() > 1): ?>
        <div class="mt-4 d-flex justify-content-center">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
</div>
<?php $this->endSection(); ?>