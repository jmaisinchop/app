<?php namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override('App\Controllers\Kb::error404');
$routes->setAutoRoute(true);

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->add('maintenance-mode', 'Pages::maintenance',[
    'as' => 'maintenance'
]);
$routes->add('/', 'Kb::home', [
    'as' => 'home',
    'filter' => 'userAuth'
]);
$routes->add('category/(:num)-(:any)', 'Kb::category/$1', [
    'as' => 'category',
    'filter' => 'userAuth'
]);
$routes->add('article/(:num)-(:any)', 'Kb::article/$1', [
    'as' => 'article',
    'filter' => 'userAuth'
]);
$routes->add('search', 'Kb::search',[
    'as' => 'search',
    'filter' => 'userAuth'
]);
#Para descargar los archivos de Base de Conocimiento
$routes->add('download/docs/(:any)', 'Kb::downloadFilePublic/$1',[
    'as' => 'docs',
    'filter' => 'userAuth:visitor'
]);
$routes->add('download/article-(:num)-(:num)', 'Kb::download/$1/$2',[
    'as' => 'download_article',
    'filter' => 'userAuth'
]);
$routes->add('submit-ticket', 'Ticket::selectDepartment',[
    'as' => 'submit_ticket',
    'filter' => 'userAuth:user'
]);
$routes->add('submit-ticket/(:num)-(:any)', 'Ticket::create/$1',[
    'as' => 'submit_ticket_department',
    'filter' => 'userAuth:user'
]);
$routes->add('submit-ticket/confirmation/(:num)/(:any)', 'Ticket::confirmedTicket/$1/$2',[
    'as' => 'ticket_preview',
    'filter' => 'userAuth:user'
]);
#Guest
$routes->add('login','UserAuth::login',[
    'as' => 'login',
    'filter' => 'userAuth:visitor'
]);
$routes->add('forgot-password','UserAuth::forgot',[
    'as' => 'forgot_password',
    'filter' => 'userAuth:visitor'
]);
#User
$routes->add('tickets','Ticket::clientTickets',[
    'as' => 'view_tickets',
    'filter' => 'userAuth:user'
]);
$routes->add('tickets/(:num)','Ticket::clientTickets',[
    'filter' => 'userAuth:user'
]);
$routes->add('tickets/show/(:num)', 'Ticket::clientShow/$1',[
    'as' => 'show_ticket',
    'filter' => 'userAuth:user'
]);
$routes->add('account/profile', 'UserAuth::profile',[
    'as' => 'profile',
    'filter' => 'userAuth:user'
]);
$routes->add('account/logout', 'UserAuth::logout',[
    'as' => 'logout',
    'filter' => 'userAuth:user'
]);

#Imap
$routes->add('imap_fetcher', 'MailFetcher::imap');
#Pipe
$routes->add('mail_pipe', 'MailFetcher::pipe');

#Install
$routes->add('install', 'Install::home',[
    'as' => 'install_home'
]);
$routes->add('install/install-wizard', 'Install::install',[
    'as' => 'install_wizard'
]);
$routes->add('install/install-wizard/success', 'Install::installComplete',[
    'as' => 'install_complete'
]);
$routes->add('install/upgrade-wizard', 'Install::upgrade',[
    'as' => 'upgrade_wizard'
]);
#Staff
$routes->group(Helpdesk::STAFF_URI, [
    'filter' => 'staffAuth'
], function($routes){
    $routes->add('login','Staff\Auth::login',[
        'filter' => 'staffAuth:login',
        'as' => 'staff_login'
    ]);
    $routes->add('/',function (){
        return redirect()->route('staff_tickets');
    },[
        'as' => 'staff_dashboard'
    ]);
    $routes->add('tickets', 'Staff\Tickets::manage/main',[
        'as' => 'staff_tickets'
    ]);
    $routes->add('tickets/overdue', 'Staff\Tickets::manage/overdue',[
        'as' => 'staff_tickets_overdue'
    ]);
    $routes->add('tickets/answered', 'Staff\Tickets::manage/answered',[
        'as' => 'staff_tickets_answered'
    ]);
    $routes->add('tickets/closed', 'Staff\Tickets::manage/closed',[
        'as' => 'staff_tickets_closed'
    ]);
    $routes->add('tickets/search', 'Staff\Tickets::manage/search',[
        'as' => 'staff_tickets_search'
    ]);
    $routes->add('tickets/view/(:num)', 'Staff\Tickets::view/$1',[
        'as' => 'staff_ticket_view'
    ]);
    $routes->add('tickets/create', 'Staff\Tickets::create',[
        'as' => 'staff_ticket_new'
    ]);
    $routes->add('tickets/kb','Staff\Misc::getKB',[
        'as' => 'staff_ajax_kb'
    ]);
    $routes->add('upload/editor', 'Staff\Misc::uploadEditor',[
        'as' => 'staff_editor_uploader',
    ]);
    $routes->add('canned-responses', 'Staff\Tickets::cannedResponses',[
        'as' => 'staff_canned',
    ]);
    $routes->add('canned-responses/edit/(:num)', 'Staff\Tickets::editCannedResponses/$1',[
        'as' => 'staff_canned_edit',
    ]);
    $routes->add('canned-responses/new', 'Staff\Tickets::newCannedResponse',[
        'as' => 'staff_new_canned'
    ]);
    $routes->add('kb/categories', 'Staff\Kb::categories',[
        'as' => 'staff_kb_categories'
    ]);
    $routes->add('kb/category/new', 'Staff\Kb::newCategory',[
        'as' => 'staff_kb_new_category'
    ]);
    $routes->add('kb/category/edit/(:num)', 'Staff\Kb::editCategory/$1',[
        'as' => 'staff_kb_edit_category'
    ]);
    $routes->add('kb/articles', 'Staff\Kb::articles',[
        'as' => 'staff_kb_articles'
    ]);
    $routes->add('kb/articles/category/(:num)', 'Staff\Kb::articles/$1');
    $routes->add('kb/article/new', 'Staff\Kb::newArticle',[
        'as' => 'staff_kb_new_article'
    ]);
    $routes->add('kb/article/edit/(:num)','Staff\Kb::editArticle/$1',[
        'as' => 'staff_kb_edit_article'
    ]);
    $routes->add('departments','Staff\Departments::manage',[
        'as' => 'staff_departments'
    ]);
    $routes->add('departments/edit/(:num)', 'Staff\Departments::edit/$1', [
        'as' => 'staff_department_id'
    ]);
    $routes->add('departments/new', 'Staff\Departments::create',[
       'as' => 'staff_department_new',
    ]);
    $routes->add('agents','Staff\Agents::manage',[
       'as' => 'staff_agents'
    ]);
    $routes->add('agents/edit/(:num)','Staff\Agents::edit/$1',[
        'as' => 'staff_agents_edit'
    ]);
    $routes->add('agents/new','Staff\Agents::create',[
        'as' => 'staff_agents_new'
    ]);
    $routes->add('users','Staff\Users::manage',[
        'as' => 'staff_users'
    ]);
    $routes->add('users/new','Staff\Users::newAccount',[
        'as' => 'staff_users_new'
    ]);
    $routes->add('users/edit/(:num)','Staff\Users::editAccount/$1',[
        'as' => 'staff_users_edit'
    ]);
    $routes->add('setup/general', 'Staff\Settings::general',[
        'as' => 'staff_general_settings'
    ]);
    $routes->add('setup/security', 'Staff\Settings::security',[
        'as' => 'staff_security_settings'
    ]);
    $routes->add('setup/tickets', 'Staff\Settings::tickets',[
        'as' => 'staff_tickets_settings'
    ]);
    $routes->add('setup/kb', 'Staff\Settings::kb',[
        'as' => 'staff_kb_settings'
    ]);
    $routes->add('setup/email-templates', 'Staff\Settings::emailTemplates',[
        'as' => 'staff_email_templates'
    ]);
    $routes->add('setup/email-templates/edit/(:any)', 'Staff\Settings::emailTemplatesEdit/$1',[
        'as' => 'staff_email_templates_edit'
    ]);
    $routes->add('setup/email-addresses', 'Staff\Settings::emails',[
        'as' => 'staff_emails'
    ]);
    $routes->add('setup/email-addresses/new', 'Staff\Settings::emailsCreate',[
        'as' => 'staff_emails_new'
    ]);
    $routes->add('setup/email-addresses/edit/(:num)', 'Staff\Settings::emailsEdit/$1',[
        'as' => 'staff_emails_edit'
    ]);
    $routes->add('setup/api', 'Staff\Settings::api',[
        'as' => 'staff_api'
    ]);
    $routes->add('setup/api/new', 'Staff\Settings::apiCreate',[
        'as' => 'staff_api_new'
    ]);
    $routes->add('setup/api/edit/(:num)', 'Staff\Settings::apiEdit/$1',[
        'as' => 'staff_api_edit'
    ]);
    $routes->add('account/log-out','Staff\Auth::logout',[
        'as' => 'staff_logout'
    ]);
    $routes->add('account/profile','Staff\Auth::profile',[
        'as' => 'staff_profile'
    ]);
    $routes->add('tools/custom-fields','Staff\Tools::customFields',[
        'as' => 'staff_custom_fields'
    ]);
    $routes->add('tools/custom-fields/new','Staff\Tools::customFieldsCreate',[
        'as' => 'staff_new_custom_field'
    ]);
    $routes->add('tools/custom-fields/edit/(:num)', 'Staff\Tools::customFieldsEdit/$1',[
        'as' => 'staff_edit_custom_field'
    ]);

    #Configuración de parametros del sistema
    $routes->add('abo/setup', 'Staff\SettingsAbo::general',[
        'as' => 'staff_settings_abo'
    ]);
    $routes->add('abo/tsolicitudes', 'Staff\SettingsAbo::solicitudes',[
        'as' => 'staff_types_solicitude'
    ]);
    $routes->add('abo/tsolicitudes/new', 'Staff\SettingsAbo::newSolicitude',[
        'as' => 'staff_types_solicitude_new'
    ]);
    $routes->add('abo/tsolicitudes/edit/(:num)', 'Staff\SettingsAbo::solicitudeEdit/$1',[
        'as' => 'staff_solicitude_id'
    ]);
    $routes->add('abo/params', 'Staff\SettingsAbo::params',[
        'as' => 'staff_params_settings'
    ]);
    $routes->add('abo/params/new', 'Staff\SettingsAbo::newSystemParam', [
        'as' => 'staff_param_new'
    ]);
    $routes->add('abo/params/edit/(:num)', 'Staff\SettingsAbo::paramsEdit/$1', [
        'as' => 'staff_param_id'
    ]);
    #Pametrizacion de archivos adjuntos por departamento
    $routes->add('abo/attachments', 'Staff\SettingsAbo::AttachmentsDepartment',[
       'as' => 'staff_attachments_department',
    ]);
    $routes->add('abo/attachments/new', 'Staff\SettingsAbo::createAttachmentDepartment',[
       'as' => 'staff_attachments_department_new',
    ]);
    $routes->add('abo/attachments/edit/(:num)', 'Staff\SettingsAbo::attachmentsEdit/$1', [
        'as' => 'staff_tdepartment_id'
    ]);
    #Ruta para mostrar el listado de emails enviados al cliente
    $routes->add('trace/emails', 'Staff\TraceProcess::emails',[
        'as' => 'staff_emails_client'
    ]);

    #Ruta para mostrar el listado de procesos
    $routes->add('trace/allprocess', 'Staff\TraceProcess::allProcess',[
        'as' => 'staff_allprocess'
    ]);

    #Ruta para descargar reporte
    $routes->add('report/form', 'Staff\ReportController::form',[
        'as' => 'staff_report_form'
    ]);
    $routes->add('report/generate', 'Staff\ReportController::generateExcel',[
        'as' => 'report_generate'
    ]);

});

//API
$routes->presenter('api/users', ['controller' => 'Api\Users','filter'=>'apiAuth']);
$routes->presenter('api/departments', ['controller' => 'Api\Departments','filter'=>'apiAuth']);
$routes->presenter('api/tickets', ['controller' => 'Api\Tickets', 'filter'=>'apiAuth']);
$routes->presenter('api/messages', ['controller' => 'Api\Messages','filter'=>'apiAuth']);
$routes->presenter('api/attachments', ['controller' => 'Api\Attachments','filter'=>'apiAuth']);
$routes->presenter('api/staff', ['controller' => 'Api\Staff','filter'=>'apiAuth']);
$routes->post('api/staff/auth', 'Api\Staff::auth',['filter' => 'apiAuth']);

//RUTAS PORTAL CLIENTE AUSTROBANK
$routes->get('portal-atencion-cliente', 'AustrobankPortal::index', ['as' => 'portal_austrobank']);
$routes->get('portal/abrir-ticket', 'AustrobankPortal::openTicketShow', ['as' => 'portal_open_ticket']);
$routes->post('portal-atencion-cliente/enviar', 'AustrobankPortal::enviarTicket', ['as' => 'portal_austrobank_send']);
$routes->get('portal/ver-ticket', 'AustrobankPortal::checkTicketShow', ['as' => 'portal_check_ticket_form']);
$routes->post('portal-atencion-cliente/consultar', 'AustrobankPortal::consultarTicket', ['as' => 'portal_austrobank_check']);
$routes->post('portal-atencion-cliente/responder/(:num)', 'AustrobankPortal::agregarRespuesta/$1', ['as' => 'portal_austrobank_reply']);
$routes->get('portal/ticket-creado/(:num)', 'AustrobankPortal::ticketConfirmation/$1', ['as' => 'portal_ticket_confirmation']);
$routes->get('portal/login', 'AustrobankPortal::loginShow', ['as' => 'portal_login']);
$routes->post('portal/login', 'AustrobankPortal::loginProcess');
$routes->get('portal/register', 'AustrobankPortal::registerShow', ['as' => 'portal_register']);
$routes->post('portal/register', 'AustrobankPortal::registerProcess');
$routes->get('portal/activar-cuenta', 'AustrobankPortal::claimAccountShow', ['as' => 'portal_claim_account']);
$routes->post('portal/activar-cuenta-proceso', 'AustrobankPortal::claimAccountProcessPortal', ['as' => 'portal_claim_process']);
$routes->get('portal/forzar-cambio-clave', 'AustrobankPortal::forceChangePasswordShow', ['as' => 'portal_force_password_change', 'filter' => 'userAuth:user']);
$routes->post('portal/forzar-cambio-clave', 'AustrobankPortal::forceChangePasswordProcess', ['filter' => 'userAuth:user']);
$routes->get('portal/logout', 'AustrobankPortal::logout', ['as' => 'portal_logout']);
$routes->get('portal/dashboard', 'AustrobankPortal::dashboard', [
    'as' => 'portal_dashboard',
    'filter' => 'userAuth:user' 
]);
$routes->get('portal/ticket/ver/(:num)', 'AustrobankPortal::verTicketDelPortal/$1', [
    'as' => 'portal_ver_ticket', 
    'filter' => 'userAuth:user'  
]);
$routes->post('portal/ticket/responder/(:num)', 'AustrobankPortal::responderTicketDelPortal/$1', [
    'as' => 'portal_responder_ticket', 
    'filter' => 'userAuth:user'  
]);

$routes->get('portal/captcha/refresh', 'AustrobankPortal::refreshCaptcha', ['as' => 'portal_captcha_refresh']);
$routes->post('portal/captcha/validate', 'AustrobankPortal::validateCaptchaAjax', ['as' => 'portal_captcha_validate']);
$routes->get('portal/recuperar-clave', 'AustrobankPortal::forgotPasswordShow', ['as' => 'portal_forgot_password']);
$routes->post('portal/recuperar-clave', 'AustrobankPortal::forgotPasswordProcess', ['as' => 'portal_forgot_process']);

/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
