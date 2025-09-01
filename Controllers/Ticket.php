<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */

namespace App\Controllers;


use App\Libraries\reCAPTCHA;
use App\Libraries\Tickets;
use Config\Services;

class Ticket extends BaseController
{

    public function selectDepartmentOld()
    {

        if($this->request->getPost('do') == 'submit'){
            $departments = Services::departments();
            $validation = Services::validation();
            $validation->setRule('department','department','required|is_natural_no_zero|is_not_unique[departments.id]');
            if($validation->withRequest($this->request)->run() == false){
                $error_msg = lang('Client.error.selectValidDepartment');
            }elseif(!$department = $departments->getByID($this->request->getPost('department'))){
                $error_msg = lang('Client.error.selectValidDepartment');
            }else{
                return redirect()->route('submit_ticket_department', [$department->id, url_title($department->name)]);
            }
        }
        return view('client/ticket_departments',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
        ]);
    }

    public function selectDepartment()
    {
        helper('departments'); // Cargar helper con getAllowedDepartments()

        // Si el formulario fue enviado
        if ($this->request->getPost('do') === 'submit') {
            $departmentsService = Services::departments();
            $validation = Services::validation();

            $validation->setRule(
                'department',
                'department',
                'required|is_natural_no_zero|is_not_unique[departments.id]'
            );

            if (!$validation->withRequest($this->request)->run()) {
                $error_msg = lang('Client.error.selectValidDepartment');
            } elseif (!$department = $departmentsService->getByID($this->request->getPost('department'))) {
                $error_msg = lang('Client.error.selectValidDepartment');
            } else {
                return redirect()->route('submit_ticket_department', [
                    $department->id,
                    url_title($department->name)
                ]);
            }
        }

        // Obtener departamentos permitidos para el usuario
        $dataStaff = findStaffByEmail(client_data('email'));
        $dataUser = findUserByEmail(client_data('email'));
        $dptsUser = unserialize($dataStaff->department);
        $departments = getDepartments(true);

        $allowedDepartments = getAllowedDepartments(
            $departments,
            is_array($dptsUser) ? $dptsUser : [],
            $dataUser
        );

        return view('client/ticket_departments', [
            'allowedDepartments' => $allowedDepartments,
            'error_msg' => $error_msg ?? null
        ]);
    }

    //Crear el ticket
    public function create($department_id)
    {
        $departments = Services::departments();
        if(!$department = $departments->getByID($department_id)){
            return redirect()->route('submit_ticket');
        }

        $tickets = new Tickets();
        $validation = Services::validation();
        $reCAPTCHA = new reCAPTCHA();
        
        if($this->request->getPost('do') == 'submit'){
            $attachments = Services::attachments();

            $this->applyCommonValidations($validation);

            #Consulta parametrizaciones sistema para validaciones de formulario en ATENCION DEL CLIENTE y PAGOS RECIBIDOS PRESTAMOS
            $paramAttentionClient = getParam('DEPARTMENT_ATTENTION_CLIENT');
            $paramLoanPayment = getParam('DEPARTMENT_LOAN_PAYMENTS');
            $nameDep = getNamesDepAdjuntosById($department->id);
            $typePerson = $this->request->getPost('typePerson');

            //Se consulta parametro del sistema para adjuntar departamentos al enviar un ticket.
            $departments_attachment = getParam('DEPARTMENTS_ATTACHMENT');

            //Se consutla parametro de Proceso de Creditos.
            $paramCreditProcess = getParam('CREDIT_PROCESS');

            //Valida proceso, Atencion al Cliente
            if($paramAttentionClient != null && trim($paramAttentionClient->param_text) === $nameDep) {

                $validation->setRule('nombreCliente','nombreCliente',
                    'required|min_length[5]|regex_match[/\A[a-zA-Z áéíóúÁÉÍÓÚñÑ -.& ]+\z/i]',
                [
                    'required' => lang('Client.error.enterNameCliente'),
                    'min_length' => lang('Client.error.validNameClient'),
                    'regex_match' => lang('Nombre del cliente solo caracteres alfanuméricos más caracteres especiales (. - &).')
                ]);

                $validation->setRule('destino1','destino1','required|min_length[5]|regex_match[/\A[a-zA-Z áéíóúÁÉÍÓÚñÑ ]+\z/i]',
                [
                    'required' => 'Ingrese nombre del destinatario final 1',
                    'min_length' => 'Nombre del destinatario 1 solo caracteres alfanuméricos, y mínimo debe tener 5 carácteres.',
                    'regex_match' => 'Nombre del destinatario 1 solo caracteres alfanuméricos, y mínimo debe tener 5 carácteres.'
                ]);

                $validation->setRule('emailCliente','emailCliente','required|valid_email|differs[emailEjecutivo]',
                [
                    'required' => lang('Client.error.enterValidEmail'),
                    'valid_email' => lang('Client.error.enterValidEmail'),
                    'differs' => 'El correo electrónico del cliente destino 1, tiene que ser diferente al correo electrónico del Asesor.'
                ]);


                if($typePerson === 'jur'){
                    $validaDestino2 = $this->request->getPost('destino2');
                    $validaEmail2 = $this->request->getPost('emailCliente2');

                    $validation->setRule('identificacion','identificacion',
                        'required|regex_match[/\A[A-Z0-9-]+\z/i]|max_length[18]',
                    [
                        'required' => 'Ingresa la identificación del cliente.',
                        'regex_match' => 'La identificación es alfanumérico, incluyendo el guion medio (-).',
                        'max_length' => 'Número de identificación, máximo de 18 carácteres.'
                    ]);

                    if($validaDestino2 !='' || $validaEmail2 != '') {
                        $validation->setRule('destino2','destino2','required|min_length[5]|regex_match[/\A[a-zA-Z áéíóúÁÉÍÓÚñÑ ]+\z/i]',
                        [
                            'required' => 'Ingrese nombre del destinatario final 2',
                            'min_length' => 'Nombre del destinatario 2 solo caracteres alfanuméricos, y mínimo debe tener 5 carácteres.',
                            'regex_match' => 'Nombre del destinatario 2 solo caracteres alfanuméricos, y mínimo debe tener 5 carácteres.'
                        ]);

                        $validation->setRule('emailCliente2','emailCliente2','required|valid_email|differs[emailEjecutivo]',
                        [
                            'required'=>'Introduce una dirección de correo electrónico válida para el destinatario 2',
                            'valid_email'=>'Introduce una dirección de correo electrónico válida para el destinatario 2',
                            'differs' => 'El correo electrónico del cliente destino 2, tiene que ser diferente al correo electrónico del Asesor.'
                        ]);
                    }
                } else {
                    $validation->setRule('identificacion','identificacion',
                        'required|regex_match[/\A[A-Z0-9-]+\z/i]|max_length[13]',
                    [
                        'required' => 'Ingresa la identificación del cliente.',
                        'regex_match' => 'La identificación es alfanumérico, incluyendo el guion medio (-).',
                        'max_length' => 'Número de identificación, máximo de 13 carácteres.'
                    ]);
                }
            //Pagos Recibidos Prestamos
            } else if ($paramLoanPayment != null && trim($paramLoanPayment->param_text) === $nameDep){
                $validation->setRule('nombre','nombre','required|min_length[5]|regex_match[/\A[a-zA-Z áéíóúÁÉÍÓÚñÑ ]+\z/i]',
                [
                    'required' => 'Ingrese nombre del cliente.',
                    'min_length' => 'Nombre del cliente solo caracteres alfanuméricos, y mínimo debe tener 5 carácteres.',
                    'regex_match' => 'Nombre del cliente solo caracteres alfanuméricos, y mínimo debe tener 5 carácteres.'
                ]);

                $validation->setRule('numeroPrestamo','numeroPrestamo',
                    'required|regex_match[/\A[A-Z0-9]+\z/i]|max_length[16]',
                [
                    'required' => 'Ingresa número del préstamo.',
                    'regex_match' => 'El número de préstamo es alfanumérico.',
                    'max_length' => 'Número de préstamo, máximo de 16 carácteres.'
                ]);

                $validation->setRule('email','email','required|valid_email',
                [
                    'required'=>'Introduce una dirección de correo electrónico válida para el cliente.',
                    'valid_email'=>'Introduce una dirección de correo electrónico válida para el cliente.'
                ]);
            //Otros procesos
            } else {
                //Valijas (Subject)
                if(isset($_POST['remitente'])){
                    $validation->setRule('subject','subject', 'required|min_length[5]|max_length[10]|alpha_numeric',[
                        'required' => lang('Client.error.enterNumeroGuia'),
                        'alpha_numeric' => lang('Client.error.minNumeroGuia'),
                        'min_length' => lang('Client.error.minNumeroGuia'),
                        'max_length' => lang('Client.error.minNumeroGuia')
                    ]);
                //Originacion de creditos (Subject)
                } else {
                    if($paramCreditProcess !=null && trim($paramCreditProcess->param_text) === $nameDep) {
                        $validation->setRule('typeClientCreditProcess','typeClientCreditProcess','required|is_natural_no_zero',[
                            'required' => 'Selecciona el tipo de cliente',
                            'is_natural_no_zero' => 'Selecciona el tipo de cliente'
                        ]);

                        $validation->setRule('subject','subject', 'required|min_length[5]|alpha_numeric_space',[
                            'required' => lang('Client.error.enterSubject'),
                            'min_length' => lang('Client.error.validateSubject'),
                            'alpha_numeric_space' => lang('Client.error.validateSubject'),
                        ]); 
                    } else {
                        $validation->setRule('subject','subject', 'required',[
                            'required' => lang('Client.error.enterSubject')
                        ]);
                    }
                }

                if($departments_attachment != null){
                    $array_dpts_at = explode(",", $departments_attachment->param_text);
                   if(is_array($array_dpts_at)){
                        foreach ($array_dpts_at as $dpta){
                            if((int)$dpta === (int)$department->id){
                               #Validacion para seleccionar departamentos adjunto
                                $validation->setRule('departamentos','departamentos', 'required',[
                                    'required' => lang('Client.error.checkDtoAdjunto')
                                ]);   
                            }
                        }
                    }
                }

                if(!isset($_POST['remitente'])){
                    $validation->setRule('message','message', 'required|min_length[10]',[
                    'required' => lang('Client.error.enterYourMessage'),
                    'min_length' => lang('Client.error.validateYourMessage')
                    ]);
                }
            }

            //Valida proceso Tickets sin respuesta.
            if(trim(getParamText('ONE_WAY_TICKET')) === $nameDep) {
                $validation->setRule('advisor','advisor', 'required',[
                    'required' => lang('Client.error.enterAdvisor')
                ]);
            }

            //Se valida archivos adjuntos por departamento
            $configAttachmentDep = getConfigDepartmentById($department->id, 'advisor');
            if($configAttachmentDep == null) {
                //Configuracion global del sitio
                if($this->settings->config('ticket_attachment')){
                    $max_size = $this->settings->config('ticket_file_size')*1024;
                    $allowed_extensions = unserialize($this->settings->config('ticket_file_type'));
                    $allowed_extensions = implode(',', $allowed_extensions);
                    $validation->setRule('attachment', 'attachment', 'ext_in[attachment,'.$allowed_extensions.']|max_size[attachment,'.$max_size.']',[
                        'ext_in' => lang('Client.error.fileNotAllowed'),
                        'max_size' => lang_replace('Client.error.fileBig', ['%size%' => number_to_size($max_size*1024, 2)])
                    ]);
                }
            } else {
                if($configAttachmentDep->ticket_attachment){
                    $max_size = $configAttachmentDep->ticket_file_size*1024;
                    $allowed_extensions = unserialize($configAttachmentDep->ticket_file_type);
                    $allowed_extensions = implode(',', $allowed_extensions);

                    #Se valida que adjunte un archivo al dar Chek ACTIVACIÓN DE CUENTA 
                    $solicitudes = $this->request->getPost('solicitudes');
                    $activateAccount = getParam('ACTIVATE_ACCOUNT');
                    $flagActivateAccount = false;
                    if($activateAccount != null && is_array($solicitudes)){
                        foreach($solicitudes as $item){
                            if((int)$activateAccount->param_number === (int)$item){
                                $flagActivateAccount = true;
                                $nameSolicitude = $this->settingsAbo->getSolicitudeById($item);
                                $validation->setRule('attachment', 'attachment', 'required|ext_in[attachment,'.$allowed_extensions.']|max_size[attachment,'.$max_size.']',[
                                    'required' => lang_replace('Client.error.attachmentActiveAccount', ['%solicitude%' => $nameSolicitude->description]),
                                    'ext_in' => lang('Admin.error.fileNotAllowed'),                        
                                    'max_size' => lang_replace('Admin.error.fileBig', ['%size%' => number_to_size($max_size*1024, 2)])
                                ]);
                            break;
                            }
                        }
                    }

                    if(!$flagActivateAccount){
                        $validation->setRule('attachment', 'attachment', 'ext_in[attachment,'.$allowed_extensions.']|max_size[attachment,'.$max_size.']',[
                            'ext_in' => lang('Admin.error.fileNotAllowed'),                        
                            'max_size' => lang_replace('Admin.error.fileBig', ['%size%' => number_to_size($max_size*1024, 2)])
                        ]);
                    } 
                }
            }
            
            $customFieldList = array();
            if($customFields = $tickets->customFieldsFromDepartment($department->id)){
                foreach ($customFields as $customField){
                    $value = '';
                    if(in_array($customField->type, ['text','textarea','password','email','date'])){
                        $value = $this->request->getPost('custom')[$customField->id];
                    }elseif(in_array($customField->type, ['radio','select'])){
                        $options = explode("\n", $customField->value);
                        $value = $options[$this->request->getPost('custom')[$customField->id]];
                    }elseif ($customField->type == 'checkbox'){
                        $options = explode("\n", $customField->value);
                        $checkbox_list = array();
                        if(is_array($this->request->getPost('custom')[$customField->id])){
                            foreach ($this->request->getPost('custom')[$customField->id] as $k){
                                $checkbox_list[] = $options[$k];
                            }
                            $value = implode(', ',$checkbox_list);
                        }
                    }
                    $customFieldList[] = [
                        'title' => $customField->title,
                        'value' => $value
                    ];
                    if($customField->required == '1'){
                        $validation->setRule('custom.'.$customField->id, $customField->title, 'required');
                    }
                }
            }

            //Proceso para registrar el Ticket
            if(!$reCAPTCHA->validate()){
                $error_msg = lang('Client.error.invalidCaptcha');
            }elseif($departments_attachment == null){
                $error_msg = "Error al obtener el parametro del sistema <strong>DEPARTMENTS_ATTACHMENT</strong>";
            }elseif($validation->withRequest($this->request)->run() == false){
                  $error_msg = $validation->listErrors();  
            }else{
                if($this->settings->config('ticket_attachment')){
                    if($uploaded_files = $attachments->ticketUpload()){
                        $files = $uploaded_files;
                    }
                }
                if($this->client->isOnline()){
                    $client_id = $this->client->getData('id');
                }else{
                    $client_id = $this->client->getClientID($this->request->getPost('fullname'), $this->request->getPost('email'));
                }

                /**
                 * Create Ticket
                 */
                //Proceso Atencion al Cliente
                if(isset($_POST['identificacion']) && isset($_POST['nombreCliente']) ) {
                    $identificacion = $this->request->getPost('identificacion');
                    $nombreCliente = $this->request->getPost('nombreCliente');
                    $destino1 = $this->request->getPost('destino1');
                    $emailCliente = $this->request->getPost('emailCliente');
                    $destino2 = $this->request->getPost('destino2');
                    $email2 = $this->request->getPost('emailCliente2');
                    $solicitudes = $this->request->getPost('solicitudes');

                    $subject = 'SOL-'.$identificacion;
                    $ticket_id = $tickets->createTicket(
                        $client_id, $subject, $department->id, $this->request->getPost('priority')
                    );
                    #Guarda en la tabla historica <<hdz_client_solicitude>>
                    $tickets->createClientSolicitude(
                        $identificacion, 
                        $typePerson, 
                        $nombreCliente, 
                        $destino1,
                        $emailCliente, 
                        $destino2,
                        $email2,
                        $ticket_id, 
                        serialize($solicitudes)
                    );
                //Para registrar ticket PAGOS RECIBIDOS PRESTAMOS
                } else if (isset($_POST['nombre']) && isset($_POST['numeroPrestamo'])){          
                    $clientName = $this->request->getPost('nombre');
                    $loanNumber = $this->request->getPost('numeroPrestamo');
                    $loanEmail = $this->request->getPost('email');

                    $subject = 'PRESTAMO-'.$loanNumber;
                    $ticket_id = $tickets->createTicket(
                        $client_id, $subject, $department->id, $this->request->getPost('priority')
                    );
                    #Guarda en la tabla historica <<hdz_client_solicitude>>
                    $tickets->createLoanPaymentSolicitude(
                        $clientName, 
                        $loanNumber, 
                        $loanEmail, 
                        $ticket_id, 
                    );
                //Otros procesos
                } else {
                    $ticket_id = $tickets->createTicket(
                        $client_id, $this->request->getPost('subject'), $department->id, $this->request->getPost('priority')
                    );
                }
                
                //Custom field
                $tickets->updateTicket([
                    'custom_vars' => serialize($customFieldList)
                ], $ticket_id);

                //Message
                if(isset($_POST['remitente'])){
                    $valSubmit = null;
                        $items1 = ($_POST['remitente']);
                        $items2 = ($_POST['destinatario']);
                        $items3 = ($_POST['referencia']);
                        $items4 = ($_POST['cantidad']);

                        while(true){
                            //// RECUPERAR LOS VALORES DE LOS ARREGLOS ////////
                            $item1 = current($items1);
                            $item2 = current($items2);
                            $item3 = current($items3);
                            $item4 = current($items4);

                            //// ASIGNARLOS A VARIABLES ///////////////////
                            $rem=(( $item1 !== false) ? $item1 : ", &nbsp;");
                            $desti=(( $item2 !== false) ? $item2 : ", &nbsp;");
                            $ref=(( $item3 !== false) ? $item3 : ", &nbsp;");
                            $cant=(( $item4 !== false) ? $item4 : ", &nbsp;");

                            //// CONCATENAR LOS VALORES EN ORDEN PARA SU FUTURA INSERCIÓN ////////
                            $valores = 'Remitente: '.$rem.",".
                                       'Destinatario: '.$desti.",".
                                       'Referencia: '.$ref.",".
                                       'Cantidad: '.$cant.";";
                            
                            //Concateno el valor el array al string Subtmit
                            $valSubmit .= $valores;

                            // Up! Next Value
                            $item1 = next( $items1 );
                            $item2 = next( $items2 );
                            $item3 = next( $items3 );
                            $item4 = next( $items4 );

                            // Check terminator
                            if($item1 === false && $item2 === false && $item3 === false && $item4 == false) 
                            break;  
                        }                          
                    $message_id = $tickets->addMessage($ticket_id, $valSubmit, 0);
                } elseif($paramAttentionClient !=null && trim($paramAttentionClient->param_text) === $nameDep) {
                    $identificacion = $this->request->getPost('identificacion');
                    $nombreCliente = $this->request->getPost('nombreCliente');
                    $destino1 = $this->request->getPost('destino1');
                    $emailCliente = $this->request->getPost('emailCliente');
                    $destino2 = $this->request->getPost('destino2');
                    $email2 = $this->request->getPost('emailCliente2');
                    $solicitudes = $this->request->getPost('solicitudes');

                    
                    $solicitude = array();

                    if($typePerson === 'nat'){
                        $solicitude [] = [   
                            'identificacion' => $identificacion,
                            'nombre' => $nombreCliente,
                            'destino1' =>$destino1,
                            'email' => $emailCliente,
                            'tipopersona' => $typePerson,
                            'solicitudes' => serialize($solicitudes)
                        ];
                    } else {
                        $solicitude [] = [   
                            'identificacion' => $identificacion,
                            'nombre' => $nombreCliente,
                            'destino1' =>$destino1,
                            'email' => $emailCliente,
                            'destino2' => $destino2,
                            'email2' => $email2,
                            'tipopersona' => $typePerson,
                            'solicitudes' => serialize($solicitudes)
                        ];
                    }
                    
                    $message_id = $tickets->addMessage($ticket_id, serialize($solicitude),0);

                } elseif (trim(getParamText('ONE_WAY_TICKET')) === $nameDep) {
                    $message_executive = array();
                    $message_executive [] = [
                        'advisor' => $this->request->getPost('advisor'),
                        'message' => $this->request->getPost('message')
                    ];
                    $message_id = $tickets->addMessage($ticket_id, serialize($message_executive),0);

                } else {
                  $message_id = $tickets->addMessage($ticket_id, nl2br(esc($this->request->getPost('message'))), 0);  
                }
                
                //File
                if(isset($files)){
                    $attachments->addTicketFiles($ticket_id, $message_id, $files);
                }


                $ticket = $tickets->getTicket(['id' => $ticket_id]);
                $tickets->newTicketNotification($ticket);
                $tickets->staffNotification($ticket);
                $ticket_preview = sha1($ticket->id);
                $this->session->set('ticket_preview', $ticket_preview);
                return redirect()->route('ticket_preview', [$ticket->id, $ticket_preview]);
            }
        }

        #Obtengo el parametro para filtrar los asesores comerciales
        $filter_advisors = getParam('FILTER_COMMERCIAL');
        return view('client/ticket_form',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'department' => $department,
            'validation' => $validation,
            'captcha' => $reCAPTCHA->display(),
            'ticket_priorities' => $tickets->getPriorities(),
            'ticket_solicitude' => $this->settingsAbo->getSolicitude(),
            'customFields' => $tickets->customFieldsFromDepartment($department->id),
            'advisors_commercial' => $this->staff->getAdvisorsCommercial($filter_advisors != null ? $filter_advisors->param_text : '')
        ]);
    }

    private function applyCommonValidations($validation)
    {
        if (!$this->client->isOnline()) {
            $validation->setRule('fullname', 'fullname', 'required', [
                'required' => lang('Client.error.enterFullName')
            ]);
            $validation->setRule('email', 'email', 'required|valid_email', [
                'required' => lang('Client.error.enterValidEmail'),
                'valid_email' => lang('Client.error.enterValidEmail')
            ]);
        }
    }


    public function confirmedTicket($ticket_id, $preview_code)
    {
        if(!$this->session->has('ticket_preview')){
            return redirect()->route('submit_ticket');
        }

        if($this->session->get('ticket_preview') != $preview_code || sha1($ticket_id) != $preview_code){
            return redirect()->route('submit_ticket');
        }

        $tickets = new Tickets();
        if(!$ticket = $tickets->getTicket(['id'=>$ticket_id])){
            return redirect()->route('submit_ticket');
        }

        return view('client/ticket_confirmation',[
            'ticket' => $ticket
        ]);
    }

    public function clientTickets()
    {
        $tickets = new Tickets();
        $pagination = $tickets->clientTickets($this->client->getData('id'));
        return view('client/tickets',[
            'result_data' => $pagination['result'],
            'pager' => $pagination['pager'],
            'error_msg' => isset($error_msg) ? $error_msg : null
        ]);
    }

    public function clientShow($ticket_id, $page=1)
    {
        $tickets = new Tickets();
        $attachments = Services::attachments();
        if(!$info = $tickets->getTicket(['id' => $ticket_id,'user_id' => $this->client->getData('id')])){
            $this->session->setFlashdata('error_msg', lang('Client.viewTickets.notFound'));
            return redirect()->route('view_tickets');
        }
        if($this->request->getGet('download')){
            if(!$file = $attachments->getRow(['id' => $this->request->getGet('download'),'ticket_id' => $info->id])){
                return view('client/error',[
                    'title' => lang('Client.error.fileNotFound'),
                    'body' => lang('Client.error.fileNotFoundMsg'),
                    'footer' => ''
                ]);
            }
            return $attachments->download($file);
        }

        //Para visualizar el archivo PDF (Proceso Atención al Cliente)
        if($this->request->getGet('view')){
            if(!$file = $attachments->getRow(['id' => $this->request->getGet('view'),'ticket_id' => $info->id])){
                return view('client/error',[
                    'title' => lang('Client.error.fileNotFound'),
                    'body' => lang('Client.error.fileNotFoundMsg'),
                    'footer' => ''
                ]);
            }
            return $attachments->viewPDF($file);
        }



        if($this->request->getPost('do') == 'reply')
        {
            $validation = Services::validation();
            $validation->setRule('message','message','required',[
                'required' => lang('Client.error.enterYourMessage')
            ]);

            //Se consulta la parametrizacion del departamento para archivos adjuntos
            $configAttachmentDep = getConfigDepartmentById($info->department_id, 'executive');
            if($configAttachmentDep == null){
                //Configuracion Global de Tickets para archivos adjuntos
                if($this->settings->config('ticket_attachment')){
                    $max_size = $this->settings->config('ticket_file_size')*1024;
                    $allowed_extensions = unserialize($this->settings->config('ticket_file_type'));
                    $allowed_extensions = implode(',', $allowed_extensions);
                    $validation->setRule('attachment', 'attachment', 'ext_in[attachment,'.$allowed_extensions.']|max_size[attachment,'.$max_size.']',[
                        'ext_in' => lang('Client.error.fileNotAllowed'),
                        'max_size' => lang_replace('Client.error.fileBig', ['%size%' => number_to_size($max_size*1024, 2)])
                    ]);
                }
            } else {
                if($configAttachmentDep->ticket_attachment){
                    $max_size = $configAttachmentDep->ticket_file_size*1024;
                    $allowed_extensions = unserialize($configAttachmentDep->ticket_file_type);
                    $allowed_extensions = implode(',', $allowed_extensions);
                    $validation->setRule('attachment', 'attachment', 'ext_in[attachment,'.$allowed_extensions.']|max_size[attachment,'.$max_size.']',[
                        'ext_in' => lang('Admin.error.fileNotAllowed'),
                        'max_size' => lang_replace('Admin.error.fileBig', ['%size%' => number_to_size($max_size*1024, 2)])
                    ]);
                } 
            }
            
            if($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            }else{
                if($this->settings->config('ticket_attachment')){
                    if($uploaded_files = $attachments->ticketUpload()){
                        $files = $uploaded_files;
                    }
                }
                //Message
                $message_id = $tickets->addMessage($info->id, nl2br(esc($this->request->getPost('message'))));
                $tickets->updateTicketReply($info->id, $info->status);
                //File
                if(isset($files)){
                    $attachments->addTicketFiles($info->id, $message_id, $files);
                }

                $tickets->staffNotification($info);
                $this->session->setFlashdata('form_success',lang('Client.viewTickets.replySent'));
                return redirect()->to(current_url());
            }
        }

        $data = $tickets->getMessages($info->id);

        return view('client/ticket_view', [
            'ticket' => $info,
            'result_data' => $data['result'],
            'pager' => $data['pager'],
            'ticket_status' => lang('Client.form.'.$tickets->statusName($info->status)),
            'error_msg' => isset($error_msg) ? $error_msg : null,
        ]);
    }

}