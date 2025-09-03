<?php
namespace App\Controllers;

use Config\Services;
use Gregwar\Captcha\CaptchaBuilder;
class AustrobankPortal extends BaseController
{

    const DEFAULT_PRIORITY_ID = 1; 

    public function index()
    {
        if (Services::client()->isOnline()) {
            return redirect()->route('portal_dashboard');
        }

        return view('client/portal_austrobank');
    }



    public function openTicketShow()
    {
        $data = []; 
        if (!client_online()) {
            $data['captcha_image_inline'] = create_custom_captcha();
        }

        return view('client/portal_open_ticket', $data);
    }

    public function checkTicketShow()
    {
        return view('client/portal_check_ticket', [
            'captcha_image_inline' => create_custom_captcha()
        ]);
    }

 
    public function enviarTicket()
    {
        // Reglas de validación base
        $rules = [
            'fullname' => 'required|min_length[3]',
            'email'    => 'required|valid_email',
            'subject'  => 'required|min_length[5]',
            'message'  => 'required|min_length[10]',
        ];


        if (!client_online()) {
            $rules['captcha'] = 'required|matches_session[captcha_phrase]';
        }

        $errors = [
            'captcha' => [
                'required'        => 'Por favor, ingresa el código de seguridad.',
                'matches_session' => 'El código de seguridad es incorrecto.'
            ]
        ];

        if (!$this->validate($rules, $errors)) {
            return redirect()->to(site_url('portal/abrir-ticket'))
                            ->withInput()
                            ->with('validation', $this->validator);
        }

        if (!client_online()) {
            session()->remove('captcha_phrase');
        }

        $tickets = new \App\Libraries\Tickets();
        $client = new \App\Libraries\Client();

        $clientId = $client->getClientIDForPortal(
            $this->request->getPost('fullname'),
            $this->request->getPost('email')
        );

        helper('helpdesk');
        $departmentModel = new \App\Models\Departments();

        $default_dept_name = trim(getParamText('DEPARTAMENTO_CLIENTE_ESPECIAL'));
        $department_id_to_use = null;
        if (!empty($default_dept_name)) {
            $departmentObject = $departmentModel->where('name', $default_dept_name)->first();
            if ($departmentObject) {
                $department_id_to_use = $departmentObject->id;
            }
        }


        $ticketId = $tickets->createTicket(
            $clientId,
            $this->request->getPost('subject'),
            $department_id_to_use,
            self::DEFAULT_PRIORITY_ID
        );

        $messageId = $tickets->addMessage($ticketId, esc($this->request->getPost('message')), 0);

        $attachments = new \App\Libraries\Attachments();

        if ($files_uploaded = $attachments->ticketUpload()) {
            $attachments->addTicketFiles($ticketId, $messageId, $files_uploaded);
        }

        $ticketData = $tickets->getTicket(['id' => $ticketId]);

        $tickets->staffNotification($ticketData);
        $tickets->newTicketNotificationPortal($ticketData);

        return redirect()->route('portal_ticket_confirmation', [$ticketId]);
    }


    public function consultarTicket()
    {
        $rules = [
            'check_ticket_id' => 'required',
            'captcha'         => 'required|matches_session[captcha_phrase]'
        ];

        $errors = [
            'captcha' => [
                'required'        => 'Por favor, ingresa el código de seguridad.',
                'matches_session' => 'El código de seguridad es incorrecto.'
            ]
        ];

        if (!$this->validate($rules, $errors)) {
        
            return redirect()->to(site_url('portal/ver-ticket'))
                            ->withInput()
                            ->with('validation', $this->validator);
        }

        session()->remove('captcha_phrase');


        $attachments = new \App\Libraries\Attachments();
        if ($this->request->getGet('download') && $this->request->getGet('ticket_id')) {
            $fileId = $this->request->getGet('download');
            $ticketId = $this->request->getGet('ticket_id');
            if (!$file = $attachments->getRow(['id' => $fileId, 'ticket_id' => $ticketId])) {
                return redirect()->back()->with('error_check', 'El archivo solicitado no existe o no pertenece a este ticket.');
            }
            return $attachments->download($file);
        }

        $tickets = new \App\Libraries\Tickets();
        $rawTicketId = $this->request->getPost('check_ticket_id');
        $ticketId = preg_replace('/[^0-9]/', '', $rawTicketId);

        if (empty($ticketId)) {
            return redirect()->to(site_url('portal/ver-ticket'))->withInput()->with('error_check', 'El número de ticket no es válido.');
        }

        $ticket = $tickets->getTicketForPortal($ticketId);

        if (!$ticket) {
            return redirect()->to(site_url('portal/ver-ticket'))->with('error_check', 'No se encontró ningún ticket con ese número.');
        }

        $messages = $tickets->getMessages($ticketId);

        return view('client/portal_austrobank_ticket', [
            'ticket' => $ticket,
            'messages' => $messages['result']
        ]);
    }

    public function ticketConfirmation($ticketId)
    {
        $tickets = new \App\Libraries\Tickets();
        $ticket = $tickets->getTicketForPortal($ticketId);

        if (!$ticket) {
            return redirect()->to(site_url('portal-atencion-cliente'))
                             ->with('error_check', 'No se pudo encontrar el ticket recién creado.');
        }

        return view('client/portal_ticket_confirmation', [
            'ticket' => $ticket
        ]);
    }

    public function loginShow()
    {
        if (Services::client()->isOnline()) {
            return redirect()->route('portal_dashboard');
        }
        return view('client/portal_login', [
            'captcha_image_inline' => create_custom_captcha()
        ]);
    }


    public function loginProcess()
    {
        $client = Services::client();
        if ($client->isOnline()) {
            return redirect()->route('portal_dashboard');
        }

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
            'captcha'  => 'required|matches_session[captcha_phrase]'
        ];

        $errors = [
            'captcha' => [
                'required'        => 'Por favor, ingresa el código de seguridad.',
                'matches_session' => 'El código de seguridad es incorrecto.'
            ]
        ];

        if (!$this->validate($rules, $errors)) {
            return redirect()->route('portal_login')
                            ->withInput()
                            ->with('validation', $this->validator);
        }

        session()->remove('captcha_phrase');

        if (!$client_data = $client->getRow(['email' => $this->request->getPost('email'), 'status' => 1])) {
            return redirect()->route('portal_login')->withInput()->with('error', 'Correo o contraseña incorrectos.');
        }

        if (!password_verify($this->request->getPost('password'), $client_data->password)) {
            return redirect()->route('portal_login')->withInput()->with('error', 'Correo o contraseña incorrectos.');
        }

        $client->login($client_data->id, $client_data->password);
        return redirect()->route('portal_dashboard');
    }

    public function registerShow()
    {
        if (Services::client()->isOnline()) {
            return redirect()->route('portal_dashboard');
        }

        return view('client/portal_register', [
            'captcha_image_inline' => create_custom_captcha()
        ]);
    }

    public function registerProcess()
    {
        $client = Services::client();
        if ($client->isOnline()) {
            return redirect()->route('portal_dashboard');
        }

        $rules = [
            'fullname'         => 'required',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[6]',
            'password_confirm' => 'matches[password]',
            'captcha'          => 'required|matches_session[captcha_phrase]'
        ];

        $errors = [
            'captcha' => [
                'required'        => 'Por favor, ingresa el código de seguridad.',
                'matches_session' => 'El código de seguridad es incorrecto.'
            ]
        ];

        if (!$this->validate($rules, $errors)) {
            return redirect()->route('portal_register')
                            ->withInput()
                            ->with('validation', $this->validator);
        }

        session()->remove('captcha_phrase');
        $client->createAccount(
            $this->request->getPost('fullname'),
            $this->request->getPost('email'),
            $this->request->getPost('password'),
            true
        );

        return redirect()->route('portal_login')->with('success', '¡Cuenta creada con éxito! Por favor, inicia sesión.');
    }


    public function claimAccountShow()
    {
        return view('client/portal_claim_account', [
            'captcha_image_inline' => create_custom_captcha()
        ]);
    }

    public function claimAccountProcessPortal()
    {
        $rules = [
            'email'   => 'required|valid_email',
            'captcha' => 'required|matches_session[captcha_phrase]'
        ];

        $errors = [
            'captcha' => [
                'required'        => 'Por favor, ingresa el código de seguridad.',
                'matches_session' => 'El código de seguridad es incorrecto.'
            ]
        ];

        if (!$this->validate($rules, $errors)) {
            return redirect()->back()
                            ->withInput()
                            ->with('validation', $this->validator);
        }

        session()->remove('captcha_phrase');

        $client = Services::client();
        $email = $this->request->getPost('email');

        if (!$client_data = $client->getRow(['email' => $email])) {
            return redirect()->back()->withInput()->with('error', 'No se encontró una cuenta con ese correo. Por favor, crea una cuenta nueva desde la sección de registro.');
        }

        $client->setupTemporaryPasswordForPortal($client_data);

        return redirect()->to(site_url('portal/login'))
                        ->with('success', '¡Excelente! Hemos enviado una contraseña temporal a tu correo. Úsala para iniciar sesión.');
    }
    

    public function forceChangePasswordShow()
    {
        return view('client/portal_force_password_change');
    }

    public function forceChangePasswordProcess()
    {
        $validation = Services::validation();
        $validation->setRules([
            'current_password'   => 'required',
            'new_password'       => 'required|min_length[6]',
            'new_password_confirm' => 'matches[new_password]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('validation', $validation);
        }

        $client = Services::client();
        $client_data = $client->getRow(['id' => session()->get('clientID')]);

        if (!password_verify($this->request->getPost('current_password'), $client_data->password)) {
            return redirect()->back()->withInput()->with('error', 'La contraseña temporal actual es incorrecta.');
        }

        $client->update([
            'password' => password_hash($this->request->getPost('new_password'), PASSWORD_BCRYPT),
            'force_password_change' => 0
        ], $client_data->id);


        $client->logout();

        return redirect()->to(site_url('portal/login'))
                        ->with('success', '¡Contraseña actualizada con éxito! Por favor, inicia sesión de nuevo.');

    }

    
    public function dashboard()
    {
        $tickets = new \App\Libraries\Tickets();
        $client = Services::client();

        $pagination = $tickets->clientTickets($client->getData('id'));

        return view('client/portal_dashboard', [
            'client'      => $client,
            'result_data' => $pagination['result'],
            'pager'       => $pagination['pager'],
        ]);
    }

    public function logout()
    {
        Services::client()->logout();
        return redirect()->to(site_url('portal-atencion-cliente'));
    }



    public function verTicketDelPortal($ticketId)
    {
        $attachments = new \App\Libraries\Attachments();
        if ($this->request->getGet('download')) {
            $fileId = $this->request->getGet('download');
            if (!$file = $attachments->getRow(['id' => $fileId, 'ticket_id' => $ticketId])) {
                return redirect()->back()->with('error_msg', 'El archivo solicitado no existe.');
            }
            return $attachments->download($file);
        }

        $tickets = new \App\Libraries\Tickets();
        $client = Services::client();

        if (!$ticket = $tickets->getTicket(['id' => $ticketId, 'user_id' => $client->getData('id')])) {
            return redirect()->to(site_url('portal/dashboard'))->with('error_check', 'El ticket solicitado no existe o no tienes permiso para verlo.');
        }

        $messages = $tickets->getMessages($ticketId);

        return view('client/portal_ticket_view', [
            'ticket' => $ticket,
            'result_data' => $messages['result'],
            'pager' => $messages['pager'],
            'ticket_status' => lang('Client.form.' . $tickets->statusName($ticket->status)),
            'error_msg' => session()->getFlashdata('error_msg')
        ]);
    }


    public function responderTicketDelPortal($ticketId)
    {
        $tickets = new \App\Libraries\Tickets();
        $client = Services::client();
        $attachments = new \App\Libraries\Attachments();

        // Verificación de seguridad: Asegurarse de que el ticket pertenece al usuario logueado.
        if (!$ticket = $tickets->getTicket(['id' => $ticketId, 'user_id' => $client->getData('id')])) {
            return redirect()->to(site_url('portal/dashboard'));
        }

        $validation = Services::validation();
        $validation->setRule('message', 'message', 'required', ['required' => 'El mensaje no puede estar vacío.']);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->to(site_url(route_to('portal_ver_ticket', $ticketId)))->withInput()->with('error_msg', $validation->listErrors());
        }
        
        if ($this->settings->config('ticket_attachment')) {
            if ($uploaded_files = $attachments->ticketUpload()) {
                $files = $uploaded_files;
            }
        }

        $messageContent = nl2br(esc($this->request->getPost('message')));
        $message_id = $tickets->addMessage($ticket->id, $messageContent);
        $tickets->updateTicketReply($ticket->id, $ticket->status);
        
        if (isset($files)) {
            $attachments->addTicketFiles($ticket->id, $message_id, $files);
        }

        $tickets->notifyStaffOfPortalReply($ticket, $messageContent);
        
        return redirect()->to(site_url(route_to('portal_ver_ticket', $ticketId)))->with('form_success', 'Tu respuesta ha sido enviada con éxito.');
    }



    /**
     * Genera una nueva imagen de CAPTCHA y la devuelve como JSON,
     * incluyendo un nuevo token CSRF.
     */
    public function refreshCaptcha()
    {
        $newCaptchaImage = create_custom_captcha();

        return $this->response->setJSON([
            'success'   => true,
            'image'     => $newCaptchaImage,
            'csrf_hash' => csrf_hash()
        ]);
    }

    /**
     * Valida el CAPTCHA vía AJAX y devuelve el resultado,
     * incluyendo un nuevo token CSRF.
     */
    public function validateCaptchaAjax()
    {
        $userInput = $this->request->getPost('captcha');
        $correctPhrase = session()->get('captcha_phrase');
        $isValid = ($userInput && strtolower($userInput) === strtolower($correctPhrase));

        return $this->response->setJSON([
            'success'   => $isValid,
            'csrf_hash' => csrf_hash()
        ]);
    }

    public function forgotPasswordShow()
    {
        return view('client/portal_forgot_password', [
            'captcha_image_inline' => create_custom_captcha()
        ]);
    }


    public function forgotPasswordProcess()
    {
        $rules = [
            'email'   => 'required|valid_email',
            'captcha' => 'required|matches_session[captcha_phrase]'
        ];

        $errors = [
            'captcha' => [
                'required'        => 'Por favor, ingresa el código de seguridad.',
                'matches_session' => 'El código de seguridad es incorrecto.'
            ]
        ];

        if (!$this->validate($rules, $errors)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        session()->remove('captcha_phrase');

        $client = Services::client();
        $email = $this->request->getPost('email');

        if (!$client_data = $client->getRow(['email' => $email])) {
            return redirect()->to(site_url('portal/login'))
                            ->with('success', 'Si tu correo electrónico está en nuestra base de datos, recibirás un enlace para restablecer tu contraseña.');
        }

        $client->setupTemporaryPasswordForPortal($client_data);

        return redirect()->to(site_url('portal/login'))
                        ->with('success', 'Si tu correo electrónico está en nuestra base de datos, recibirás un enlace para restablecer tu contraseña.');
    }
}