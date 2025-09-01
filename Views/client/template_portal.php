<?php
/**
 * @var $this \CodeIgniter\View\View
 * Plantilla Maestra para el Portal de Clientes Austrobank.
 */
?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php $this->renderSection('window_title');?></title>

    <?php
    echo link_tag('favicon.ico','icon','image/x-icon').
        link_tag('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap').
        link_tag('assets/components/fontawesome/css/all.min.css').
        link_tag('assets/components/bootstrap/css/bootstrap.min.css').
        link_tag('assets/css/client_theme.css');
    $this->renderSection('css_block');
    ?>
</head>
<body class="portal-body">

<header class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= site_url('portal-atencion-cliente') ?>">
            <img src="<?= site_logo(); ?>" alt="Logo Austrobank" style="height: 40px; margin-right: 10px;">
            <span class="navbar-brand-text">Sistema de Tickets</span>
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#portalNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="portalNavbar">
            <?php if (client_online()): ?>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('portal/dashboard') ?>">Mi Panel</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('portal/abrir-ticket') ?>">Crear Ticket</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                            <i class="fas fa-user-circle mr-1"></i> <?= esc(client_data('fullname')) ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="<?= site_url('account/profile') ?>">Gestionar Perfil</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="<?= site_url('portal/logout') ?>">Cerrar Sesión</a>
                        </div>
                    </li>
                </ul>
            <?php else: ?>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('portal/abrir-ticket') ?>">Abrir Ticket</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('portal/ver-ticket') ?>">Ver Ticket</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= site_url('portal/login') ?>" class="btn btn-austro btn-sm ml-lg-2 mt-2 mt-lg-0">
                            Iniciar Sesión
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="main-content">
    <?php $this->renderSection('content'); ?>
</main>

<footer class="text-center py-4 mt-auto">
    <div class="container">
        <p class="text-muted small">Copyright &copy; <?= date('Y'); ?> <?= site_config('site_name'); ?>. Todos los derechos reservados.</p>
    </div>
</footer>

<?php
echo script_tag('assets/components/jquery/jquery.min.js').
    script_tag('assets/components/bootstrap/js/bootstrap.bundle.min.js');
$this->renderSection('script_block');
?>
</body>
</html>