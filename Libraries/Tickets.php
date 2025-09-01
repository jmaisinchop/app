<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */

namespace App\Libraries;

use App\Models\CannedModel;
use App\Models\CustomFields;
use App\Models\PriorityModel;
use App\Models\TicketNotesModel;
use App\Models\TicketsMessage;
use App\Models\ClientSolicitudeModel;
use App\Models\SystemParamsModel;
use App\Models\TypeSolicitude;
use App\Models\LoanPaymentModel;
use Config\Database;
use Config\Services;

class Tickets
{
    protected $ticketsModel;
    protected $clientSolicitudeModel;
    protected $messagesModel;
    protected $settings;
    protected $loanPaymentModel;
    public function __construct()
    {
        $this->settings = Services::settings();
        $this->ticketsModel = new \App\Models\Tickets();
        $this->clientSolicitudeModel = new \App\Models\ClientSolicitudeModel();
        $this->messagesModel = new TicketsMessage();
        $this->loanPaymentModel = new LoanPaymentModel();
    }
    public function createTicket($client_id, $subject, $department_id=1, $priority_id)
    {
        $departmentsCheckBox = array();

        //Parametros para guardar un ticket tipo Crédito-Desembolso
        $request = Services::request();
        $advisor_id = $request->getPost('advisor') ?? 0;
        $status_ticket = 1;
        $director_commercial = 0;
        $nameDep = getNamesDepAdjuntosById($department_id);

        #Proceso Crédito-Desembolso, creo el ticket con estatus cerrado.
        if(trim(getParamText('ONE_WAY_TICKET')) === $nameDep){
            $status_ticket = 5;
            $director_commercial = getParamNumber('CHIEF_DEPARTMENT_COMMERCIAL');
        }

        //Obtengo POST Array departamentos
        if(isset($_POST['departamentos'])){      
            $departmentsCheckBox = $_POST['departamentos'];

            //Le agrego departamento Padre
            //array_push($departmentsCheckBox, $department_id);
        }else {
            if(trim(getParamText('ONE_WAY_TICKET')) === $nameDep){
                $departmentsCheckBox = getParamTextArray('NOTIFY_DEPARTMENT_CREDIT_DISBURSEMENT');
            } else {
                $departmentsCheckBox = is_array($departmentsCheckBox) ? $departmentsCheckBox : array();    
            } 
        }

        $departments = Services::departments();
        if($department_id != 1){
            if(!$departments->isValid($department_id)){
                $department_id = 1;
            }
        }
        $this->ticketsModel->protect(false);      
        $this->ticketsModel->insert([
            'department_id' => $department_id,
            'department_id_child' => serialize($departmentsCheckBox),
            'priority_id' => $priority_id,
            'user_id' => $client_id,
            'advisor_id' => $advisor_id,
            'director_commercial_id' => $director_commercial,
            'subject' => $subject,
            'date' => time(),
            'last_update' => time(),
            'status' => $status_ticket,
            'last_replier' => 0,
        ]);
        $this->ticketsModel->protect(true);
        return $this->ticketsModel->getInsertID();
    }

    /*
     * --------------------------------
     * Client Email
     * --------------------------------
     */
    public function createClientEmail ($solicitude_email, $name, $email)
    {
        $clientEmailModel = new \App\Models\ClientEmailModel();
        $clientEmailModel->protect(false);
        $clientEmailModel->insert([
            'solicitude_id' => $solicitude_email,
            'name' => $name,
            'email' => $email,
            'date' => time()
        ]);
        $clientEmailModel->protect(false);
    }

    /*
     * --------------------------------
     * Client Solicitude
     * --------------------------------
     */
    public function createClientSolicitude($identificacion, $typePerson, $nombreCliente, $nameDestino1, $emailCliente, $nameDestino2, $email2, $ticket_id, $solicitudes)
    {
        $this->clientSolicitudeModel->protect(false);

        $this->clientSolicitudeModel->insert([
            'identification' => $identificacion,
            'type_person' => $typePerson,
            'date' => time(),
            'last_date' => time(),
            'client_name' => $nombreCliente,
            'name_destino1' => $nameDestino1,
            'email' => $emailCliente,
            'name_destino2' => $nameDestino2,
            'email2' => $email2,
            'ticket' => $ticket_id,
            'msg_id' => 0,
            'solicitude' =>$solicitudes,
        ]);
        $this->clientSolicitudeModel->protect(true);
    }

    public function updateClientSolicitude($id, $email, $email2, $send_email, $msg_id=0)
    {
        $this->clientSolicitudeModel->protect(false);
        $this->clientSolicitudeModel->set('email', $email)
                                ->set('email2', $email2)
                                ->set('send_email', $send_email)
                                ->set('last_date', time())
                                ->set('msg_id', $msg_id)
                                ->update($id);
        $this->clientSolicitudeModel->protect(true);
    }

    public function addMessage($ticket_id, $message, $staff_id=0, $detect_ip=true)
    {
        $this->messagesModel->protect(false);
        $this->messagesModel->insert([
            'ticket_id' => $ticket_id,
            'date' => time(),
            'customer' => ($staff_id == 0 ? 1 : 0),
            'staff_id' => $staff_id,
            'message' => $message,
            'ip' => ($detect_ip ? Services::request()->getIPAddress() : ''),
            'email' => ($detect_ip ? 0: 1),
        ]);
        $this->messagesModel->protect(true);
        return $this->messagesModel->getInsertID();
    }

    public function updateTicketReply($ticket_id, $ticket_status, $staff=false)
    {
        $this->ticketsModel->protect(false);
        if($staff){
            if(!in_array($ticket_status,[4,5])){
                $this->ticketsModel->set('status',2);
            }
            $this->ticketsModel->set('last_update',time())
                ->set('replies','replies+1',false)
                ->set('last_replier', Services::staff()->getData('id'))
                ->update($ticket_id);
        }else{
            if(in_array($ticket_status,[2,5])){
                $this->ticketsModel->set('status',3);
            }
            $this->ticketsModel->set('last_update',time())
                ->set('replies','replies+1',false)
                ->set('last_replier', 0)
                ->update($ticket_id);
        }
        $this->ticketsModel->protect(true);
    }

    public function getTicketFromEmail($client_id, $subject)
    {
        if(!preg_match('/\[#[0-9]+]/', $subject, $regs)){
            return null;
        }
        $ticket_id = str_replace(['[#',']'],'', $regs[0]);
        if(!$ticket = $this->getTicket(['user_id' => $client_id, 'id' => $ticket_id])){
            return null;
        }
        return $ticket;
    }

    //Muestra el ticket despues de crear
    public function getTicket($field,$value='')
    {
        if(!is_array($field)){
            $field = array($field => $value);
        }
        foreach ($field as $k => $v){
            $this->ticketsModel->where('tickets.'.$k, $v);
        }
        $q = $this->ticketsModel->select('tickets.*, d.name as department_name, p.name as priority_name, p.color as priority_color, u.fullname, u.email, u.avatar')
            ->join('departments as d','d.id=tickets.department_id')
            ->join('priority as p','p.id=tickets.priority_id')
            ->join('users as u','u.id=tickets.user_id')
            ->get(1);
        if($q->resultID->num_rows == 0){
            return null;
        }
        return $q->getRow();
    }

    public function countTickets($data)
    {
        return $this->ticketsModel->where($data)
            ->countAllResults();
    }


    /*
     * --------------------------------
     * Custom Fields
     * --------------------------------
     */
    public function getCustomFields()
    {
        $db = Database::connect();
        $builder = $db->table('custom_fields');
        $q = $builder->orderBy('display','asc')
            ->get();
        if($q->resultID->num_rows == 0){
            return null;
        }
        $r = $q->getResult();
        $q->freeResult();
        return $r;
    }

    public function getCustomFieldsType()
    {
        return [
            'text' => lang('Admin.tools.textField'),
            'textarea' => lang('Admin.tools.textArea'),
            'password' => lang('Admin.tools.password'),
            'checkbox' => lang('Admin.tools.checkbox'),
            'radio' => lang('Admin.tools.radio'),
            'select' => lang('Admin.tools.dropdownSelect'),
            'date' => lang('Admin.tools.date'),
            'email' => lang('Admin.tools.email'),
        ];
    }

    public function insertCustomField()
    {
        $customFieldsModel = new CustomFields();
        $request = Services::request();
        if(in_array($request->getPost('type'), ['checkbox','radio','select'])){
            $values = esc($request->getPost('options'));
        }elseif (in_array($request->getPost('type'), ['text','textarea','password'])){
            $values = esc($request->getPost('value'));
        }else{
            $values = '';
        }
        $customFieldsModel->protect(false);
        if($data = $this->customFieldLastPosition()){
            $position = $data->display+1;
        }else{
            $position = 1;
        }
        $customFieldsModel->insert([
                'type' => $request->getPost('type'),
                'title' => $request->getPost('title'),
                'value' => $values,
                'required' => $request->getPost('required'),
                'departments' => ($request->getPost('department_list') == '0' ? '' : serialize($request->getPost('departments'))),
                'display' => $position,
            ]);
        $customFieldsModel->protect(true);
    }

    public function updateCustomField($field_id)
    {
        $customFieldsModel = new CustomFields();
        $request = Services::request();
        if(in_array($request->getPost('type'), ['checkbox','radio','select'])){
            $values = $request->getPost('options');
        }elseif (in_array($request->getPost('type'), ['text','textarea','password'])){
            $values = $request->getPost('value');
        }else{
            $values = '';
        }
        $customFieldsModel->protect(false);
        $customFieldsModel->update($field_id, [
            'type' => $request->getPost('type'),
            'title' => $request->getPost('title'),
            'value' => $values,
            'required' => $request->getPost('required'),
            'departments' => ($request->getPost('department_list') == '0' ? '' : serialize($request->getPost('departments'))),
            'columns' =>$request->getPost('column'),
        ]);
        $customFieldsModel->protect(true);
    }

    public function customFieldFirstPosition()
    {
        $customFieldsModel = new CustomFields();
        $q = $customFieldsModel->select('id, display')
            ->orderBy('display','asc')
            ->get(1);
        if($q->resultID->num_rows == 0){
            return null;
        }
        return $q->getRow();
    }

    public function customFieldLastPosition()
    {
        $customFieldsModel = new CustomFields();
        $q = $customFieldsModel->select('id, display')
            ->orderBy('display','desc')
            ->get(1);
        if($q->resultID->num_rows == 0){
            return null;
        }
        return $q->getRow();
    }

    public function getCustomField($id)
    {
        $customFieldsModel = new CustomFields();
        if($data = $customFieldsModel->find($id)){
            return $data;
        }
        return null;
    }

    public function deleteCustomField($id)
    {
        $customFieldsModel = new CustomFields();
        $customFieldsModel->protect(false)->delete($id);
        $customFieldsModel->protect(true);
    }

    public function customFieldMoveUp($id)
    {
        if(!$customField = $this->getCustomField($id)){
            return false;
        }
        $customFieldsModel = new CustomFields();
        $q = $customFieldsModel->select('id, display')
            ->where('display<', $customField->display)
            ->orderBy('display','desc')
            ->get(1);
        if($q->resultID->num_rows > 0){
            $prev = $q->getRow();
            $customFieldsModel->protect(false);
            $customFieldsModel->update($customField->id, [
                'display' => $prev->display
            ]);
            $customFieldsModel->update($prev->id, [
                'display' => $customField->display
            ]);
            $customFieldsModel->protect(true);
        }
        return true;
    }

    public function customFieldMoveDown($id)
    {
        if(!$customField = $this->getCustomField($id)){
            return false;
        }
        $customFieldsModel = new CustomFields();
        $q = $customFieldsModel->select('id, display')
            ->where('display>', $customField->display)
            ->orderBy('display','asc')
            ->get(1);
        if($q->resultID->num_rows > 0){
            $next = $q->getRow();
            $customFieldsModel->protect(false);
            $customFieldsModel->update($customField->id, [
                'display' => $next->display
            ]);
            $customFieldsModel->update($next->id, [
                'display' => $customField->display
            ]);
            $customFieldsModel->protect(true);
        }
        return true;
    }

    public function customFieldsFromDepartment($department_id)
    {
        $customFieldsModel = new CustomFields();
        $q = $customFieldsModel->where('departments','')
            ->orLike('departments', '"'.$department_id.'"')
            ->get();
        if($q->resultID->num_rows == 0){
            return null;
        }
        $r = $q->getResult();
        $q->freeResult();
        return $r;
    }

    /*
     * --------------------------------
     * Notifications
     * --------------------------------
     */
    public function newTicketNotification($ticket)
    {
        //Send Mail to client
        $emails = new Emails();
        $emails->sendFromTemplate('new_ticket',[
            '%client_name%' => $ticket->fullname,
            '%client_email%' => $ticket->email,
            '%ticket_id%' => $ticket->id,
            '%ticket_subject%' => $ticket->subject,
            '%ticket_department%' => $ticket->department_name,
            '%ticket_status%' => lang('Client.form.open'),
            '%ticket_priority%' => $ticket->priority_name,
        ], $ticket->email, $ticket->department_id);
    }

    public function staffNotification($ticket)
    {
        $emails = new Emails();
        $staffModel = new \App\Models\Staff();
        $namesDptsAdjunto = $this->getNamesDepAdjuntos($ticket);

        //Se consulta parametro de Proceso, Envío de tickets sin respuesta
        $request = Services::request();
        $nameDep = getNamesDepAdjuntosById($ticket->department_id);

        if(trim(getParamText('ONE_WAY_TICKET')) === $nameDep) {
            $arrayAdvisors = array($request->getPost('advisor'), getParamNumber('CHIEF_DEPARTMENT_COMMERCIAL'));
            $result = '';
            foreach($arrayAdvisors as $advisor){
                $q = $staffModel->where('id', $advisor)
                    ->get();
                if($q->resultID->num_rows > 0){
                    foreach($q->getResult() as $result){
                    $emails->sendFromTemplate('staff_ticketnotification',[
                        '%staff_name%' => $result->fullname,
                        '%ticket_id%' => $ticket->id,
                        '%ticket_subject%' => $ticket->subject,
                        '%ticket_dtpsAdjunto%' => $namesDptsAdjunto !="" ? $namesDptsAdjunto : 'N/D',
                        '%ticket_department%' => $ticket->department_name,
                        '%ticket_status%' => lang('closed'),
                        '%ticket_priority%' => $ticket->priority_name,
                    ],$result->email, $ticket->department_id);
                    }
                    $q->freeResult();
                }
            }
            //Se envia las notificaciones a los departamentos de Cumplimiento, Legal y Operaciones.
            $this->sendMailByDepartment($ticket);

        //Se envia notificaciones a correos prametrizados para el flujo PAGOS RECIBIDOS PRESTAMOS
        } else if (trim(getParamText('DEPARTMENT_LOAN_PAYMENTS')) === $nameDep) {
            $emailsRaw = getParamText('LOAN_PAYMENT_NOTIFICATION_EMAILS');

            if (!empty($emailsRaw)) {
                // Divide, elimina espacios en blanco y filtra valores vacíos
                $emailsArray = array_filter(array_map('trim', explode(';', $emailsRaw)));
        
                foreach ($emailsArray as $email) {
                    // Validación opcional del formato del correo
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $emails->sendFromTemplate('staff_ticketnotification', [
                            '%staff_name%' => 'Departamento Crédito',
                            '%ticket_id%' => $ticket->id,
                            '%ticket_subject%' => $ticket->subject,
                            '%ticket_dtpsAdjunto%' => $namesDptsAdjunto !== "" ? $namesDptsAdjunto : 'N/D',
                            '%ticket_department%' => $ticket->department_name,
                            '%ticket_status%' => lang('Client.form.open'),
                            '%ticket_priority%' => $ticket->priority_name,
                        ], $email, $ticket->department_id);
                    }
                }
            }
        } else {
            $this->sendMailByDepartment($ticket);
        }
    }

    public function replyTicketNotification($ticket, $message, $attachments=null, $notificationClient = false)
    {
        #Instancio Libraries Parametros del sistema
        $params = new SystemParams();
        $flagAttachmentsFiles = $params->getParam('ATTACHMENTS_FILES'); 
        //Obtiene parametro del Proceso Atencion al Cliente.
        $paramAT = getParam('DEPARTMENT_ATTENTION_CLIENT');
        $paramLoanPayment = getParam('DEPARTMENT_LOAN_PAYMENTS');
        
        #Si adjunto o NO archivos a la notificacion
        $files = array();
        if($flagAttachmentsFiles != null){
            if ((int)$flagAttachmentsFiles->param_number === 1){
                if(is_array($attachments)){
                    foreach ($attachments as $file){
                        $files[] = [
                            'name' => $file['name'],
                            'path' => WRITEPATH.'attachments/'.$file['encoded_name'],
                            'file_type' => $file['file_type']
                        ];
                    }
                } 
            }
        }
        
        //Send Mail to client
        $emails = new Emails();

        //Se obtiene el estado del ticket
        $isclose_ticket = getStatusTicketSolicitude($ticket->id);
        $status_ticket = $isclose_ticket =! null ? (int)$isclose_ticket->status : 0;
        
        $namesDptsAdjunto = $this->getNamesDepAdjuntos($ticket);
        //Send Mail to client
        $emails->sendFromTemplate('staff_reply', [
            '%client_name%' => $ticket->fullname,
            '%client_email%' => $ticket->email,
            '%ticket_id%' => $ticket->id,
            '%ticket_subject%' => $ticket->subject,
            '%ticket_department%' => $ticket->department_name,
            '%ticket_dtpsAdjunto%' => $namesDptsAdjunto !="" ? $namesDptsAdjunto : 'N/D',
            '%ticket_status%' => $this->statusName($ticket->status),
            '%ticket_priority%' => $ticket->priority_name,
            '%message%' => $message,
        ], $ticket->email, $ticket->department_id, $files); 


        //Notificacion al cliente con respuesta de la documentacion solicitada (Proceso Atencion al Cliente)
        if(trim($paramAT->param_text) === $ticket->department_name){
            setlocale(LC_TIME, "es_ES");
            $fechaActual = strftime("%d de %B de %Y"); //Para corregir la fecha que se agrega a la cabecera del email, agregado el 10-05-2023
            //Obtengo datos del cliente para enviar el email
            $ticket_client = $this->getEmailSolicitude($ticket->id);

            $files = array();
                if(is_array($attachments)){
                    foreach ($attachments as $file){
                        $files[] = [
                            'name' => $file['name'],
                            'path' => WRITEPATH.'attachments/'.$file['encoded_name'],
                            'file_type' => $file['file_type']
                        ];
                    }
                } 

            //Pregunto si esta habilitado el envio del email y si el ticket fue cerrado (estatus = 5)
            //Envio de documentos al cliente.
            if($ticket_client != null && (int)$ticket_client->send_email === 1 && $status_ticket === 5 && is_array($files)){
                if($ticket_client->type_person === 'jur'){               
                    $person1 = $ticket_client->email.','.$ticket_client->name_destino1;
                    $person2 = $ticket_client->email2.','.$ticket_client->name_destino2;
                    $dataPerson = array($person1, $person2); 
                    if(is_array($dataPerson)){
                        foreach($dataPerson as $data){
                            $splitPerson = explode(",", $data);
                            //Guardo el envio del email de las Personas Juridicas en hdz_client_email.
                            if($splitPerson[0] !='' && $splitPerson[1] !=''){
                                $this->createClientEmail($ticket_client->id, $splitPerson[1], $splitPerson[0]);   
                            }
                            $emails->sendFromTemplate('client_notification', [
                            '%client_name%' => $splitPerson[1],
                            '%client_email%' => $splitPerson[0],
                            '%ticket_id%' => $ticket->id,
                            '%ticket_subject%' => $ticket->subject,
                            '%ticket_department%' => $ticket->department_name,
                            '%date%' => $fechaActual,
                            '%message%' => $message,
                            ], $splitPerson[0], $ticket->department_id, $files); 
                        }
                    }
                } else {
                    //Guardo el envio del email al cliente en hdz_client_email
                    $this->createClientEmail($ticket_client->id, $ticket_client->name_destino1, $ticket_client->email);
                    $emails->sendFromTemplate('client_notification', [
                    '%client_name%' => $ticket_client->name_destino1,
                    '%client_email%' => $ticket_client->email,
                    '%ticket_id%' => $ticket->id,
                    '%ticket_subject%' => $ticket->subject,
                    '%date%' => $fechaActual,
                    '%message%' => $message,
                    ], $ticket_client->email, $ticket->department_id, $files);  
                }
            }
        }

        if(trim($paramLoanPayment->param_text) === $ticket->department_name){
            if( $notificationClient && $status_ticket === 5){
                $this->sendNotificationClientLoanPayment($ticket, $attachments, $message);
            }
        }

        #Notificacion a todos los dpts adjuntos, solo para el Proceso de Creditos.
        $notifyAllDtp = $params->getParam('NOTIFY_REPLY_ALL_DEPARTMENTS'); 
        
        if($notifyAllDtp != null){
            if($ticket->department_name === $notifyAllDtp->param_text){
                $this->sendMailByDepartment($ticket);
            }
        }      
    }

    private function sendNotificationClientLoanPayment ($ticket, $attachments, $message){
        //Send Mail to client
        $emails = new Emails();
        $ticketLoanPayment = getLoanPaymentByTicketId($ticket->id);

        setlocale(LC_TIME, "es_ES");
        $fechaActual = strftime("%d de %B de %Y"); //Para corregir la fecha que se agrega a la cabecera del email, agregado el 10-05-2023
        //Obtengo datos del cliente para enviar el email
        $ticket_client = $this->getEmailSolicitude($ticket->id);

        $files = array();
            if(is_array($attachments)){
                foreach ($attachments as $file){
                    $files[] = [
                        'name' => $file['name'],
                        'path' => WRITEPATH.'attachments/'.$file['encoded_name'],
                        'file_type' => $file['file_type']
                    ];
                }
            } 

        //Envio de documento al cliente.
        if(is_array($files)){
            //Guardo el envio del email al cliente en hdz_loan_payments_email
            $loanPaymenEmail = new \App\Libraries\LoanPaymentsEmail(); 
            $loanPaymenEmail->createLoanPaymentEmail($ticketLoanPayment->ticket_id, $ticketLoanPayment->client_name, $ticketLoanPayment->email);

            $emails->sendFromTemplate('client_notification_loan', [
            '%loan_number%' => $ticketLoanPayment->loan_number,
            '%client_email%' => $ticketLoanPayment->email,
            '%ticket_id%' => $ticket->id,
            '%ticket_subject%' => 'Envío de comprobante de pago',
            '%date%' => $fechaActual,
            '%message%' => $message,
            ], $ticketLoanPayment->email, $ticket->department_id, $files);  
        }        
    }

    /*
     * --------------------------------------------------------------------
     * Se notifica a los Departamentos Adjuntos cuando se crea el Ticket
     * --------------------------------------------------------------------
     */
    private function sendMailByDepartment($ticket)
    {
        $emails = new Emails();
        $staffModel = new \App\Models\Staff();
        $namesDptsAdjunto = $this->getNamesDepAdjuntos($ticket);
        $idDptsAdjunto = unserialize($ticket->department_id_child);

        if(is_array($idDptsAdjunto)){
           array_push($idDptsAdjunto, $ticket->department_id); 
           foreach($idDptsAdjunto as $idDep){
                $q = $staffModel->like('department', '"'.$idDep.'"')
                ->get();
                if($q->resultID->num_rows > 0){
                    foreach ($q->getResult() as $item){
                        $emails->sendFromTemplate('staff_ticketnotification',[
                            '%staff_name%' => $item->fullname,
                            '%ticket_id%' => $ticket->id,
                            '%ticket_subject%' => $ticket->subject,
                            '%ticket_dtpsAdjunto%' => $namesDptsAdjunto !="" ? $namesDptsAdjunto : 'N/D',
                            '%ticket_department%' => $ticket->department_name,
                            '%ticket_status%' => lang('closed'),
                            '%ticket_priority%' => $ticket->priority_name,
                        ],$item->email, $ticket->department_id);
                    }
                    $q->freeResult();
                }
            }
        } 
    }

    /*
     * -----------------------------
     * Get Messages
     * -----------------------------
     */
    public function getFirstMessage($ticket_id)
    {
        $q = $this->messagesModel->where('ticket_id', $ticket_id)
            ->orderBy('date','asc')
            ->get(1);
        if($q->resultID->num_rows == 0){
            return null;
        }
        return $q->getRow();
    }
    public function getMessages($ticket_id, $select='*')
    {
        $settings = Services::settings();
        $per_page = $settings->config('tickets_replies');
        $result = $this->messagesModel->select($select)
            ->where('ticket_id', $ticket_id)
            ->orderBy('date', $settings->config('reply_order'))
            ->paginate($per_page, 'default');

        return [
            'result' => $result,
            'pager' => $this->messagesModel->pager
        ];
    }

    public function getMessageById($ticket_id){
        $q = $this->messagesModel->select('*')
            ->where('id', $ticket_id)
            ->get(1);
        if($q->resultID->num_rows == 0){
            return null;
        }
        return $q->getRow();
    }

    /*
     * -------------------------
     * Canned Response
     * -------------------------
     */
    public function getCannedList()
    {
        $cannedModel = new CannedModel();
        $q = $cannedModel->orderBy('position','asc')
            ->get();
        if($q->resultID->num_rows == 0){
            return null;
        }
        $r = $q->getResult();
        $q->freeResult();
        return $r;
    }

    public function insertCanned($title, $message)
    {
        $cannedModel = new CannedModel();
        $next_position = $cannedModel->countAll()+1;
        $cannedModel->protect(false)
            ->insert([
                'title' => esc($title),
                'message' => $message,
                'position' => $next_position,
                'date' => time(),
                'last_update' => time(),
                'staff_id' => Services::staff()->getData('id')
            ]);
        $cannedModel->protect(true);
    }

    public function getCannedResponse($id)
    {
        $cannedModel = new CannedModel();
        if(!$canned = $cannedModel->find($id)){
            return null;
        }
        return $canned;
    }


    public function updateCanned($data, $id)
    {
        $cannedModel = new CannedModel();
        $cannedModel->protect(false)
            ->update($id, $data);
        $cannedModel->protect(true);
    }

    public function changeCannedPosition($position, $id)
    {
        $cannedModel = new CannedModel();
        $cannedModel->protect(false)
            ->update($id, [
                'position' => $position,
            ]);
        $cannedModel->protect(true);
    }
    public function lastCannedPosition()
    {
        $cannedModel = new CannedModel();

        $q = $cannedModel->select('position')
            ->orderBy('position','desc')
            ->get(1);
        if($q->resultID->num_rows == 0){
            return 0;
        }
        return $q->getRow()->position;
    }
    public function deleteCanned($id)
    {
        $cannedModel = new CannedModel();
        $cannedModel->protect(false)
            ->delete($id);
        $cannedModel->protect(true);
    }
    /*
     * --------------------------------
     * Status
     * -------------------------------
     */
    public function statusName($id)
    {
        return isset($this->statusList()[$id]) ? $this->statusList()[$id] : 'open';
    }

    public function statusList()
    {
        return $ticket_status = array(
            1 => 'open',
            2 => 'answered',
            3 => 'awaiting_reply',
            4 => 'in_progress',
            5 => 'closed'
        );
    }
    /*
     * -------------------------------
     * Priorities
     * -------------------------------
     */
    public function getPriorities()
    {
        $priorityModel = new PriorityModel();
        $q = $priorityModel->orderBy('id','asc')
            ->get();
        $r = $q->getResult();
        $q->freeResult();;
        return $r;
    }
    public function existPriority($id)
    {
        $priorityModel = new PriorityModel();
        return ($priorityModel->where('id', $id)->countAllResults() == 0) ? false : true;
    }

    /*
     * ----------------------------------
     * Ticket Actions
     * ----------------------------------
     */
    public function autoCloseTickets()
    {
        $date_left = time() - (60*60*$this->settings->config('ticket_autoclose'));
        $this->ticketsModel->protect(false)
            ->where('status', 2)
            ->where('last_update<=', $date_left)
            ->set('status', 5)
            ->update();
        $this->ticketsModel->protect(true);
    }

    public function deleteTicket($ticket_id)
    {
        $this->ticketsModel->delete($ticket_id);
        $this->messagesModel->where('ticket_id', $ticket_id)
            ->delete();
        $this->clientSolicitudeModel->where('ticket', $ticket_id)
            ->delete();
        Services::attachments()->deleteFiles(['ticket_id' => $ticket_id]);
    }

    public function updateTicket($data, $id)
    {
        $this->ticketsModel->protect(false)
            ->update($id, $data);
        $this->ticketsModel->protect(true);
    }

    /*
     * ----------------------------------
     * Client Panel
     * ---------------------------------
     */
    public function clientTickets($client_id)
    {
        $request = Services::request();
        $per_page = Services::settings()->config('tickets_page');
        if($request->getGet('do') == 'search'){
            if($request->getGet('code')){
                $code = str_replace(['[','#',']'],'', $request->getGet('code'));
                $this->ticketsModel->Like('tickets.id', $code);
            }
        }
        //echo 'CLIENTE ID....'.$client_id;
        $result = $this->ticketsModel->where('tickets.user_id', $client_id)
            ->orderBy('tickets.status','asc')
            ->orderBy('tickets.last_update','desc')
            ->join('departments as d','d.id=tickets.department_id')
            ->join('priority as p','p.id=tickets.priority_id')
            ->select('tickets.*, d.name as department_name, p.name as priority_name, p.color as priority_color')
            ->paginate($per_page, 'default', null, 2);
        return [
            'result' => $result,
            'pager' => $this->ticketsModel->pager
        ];
    }


    /*
     * ---------------------------------------
     * Staff Panel
     * ---------------------------------------
     */
    public function staffTickets($page='')
    {
        $staff = Services::staff();
        $request = Services::request();
        $staff_departments = $staff->getDepartments();
        $search_department = false;

        switch($page){
            case 'search':
                if($request->getGet('department')){
                    $key = array_search($request->getGet('department'), array_column($staff_departments, 'id'));
                    if(is_numeric($key)){
                        $this->ticketsModel->where('tickets.department_id', $staff_departments[$key]->id);
                    }
                    $search_department = true;
                }

                if($request->getGet('keyword') != ''){
                    $this->ticketsModel->groupStart()
                        ->where('tickets.id', $request->getGet('keyword'))
                        ->orLike('tickets.subject', $request->getGet('keyword'))
                        ->orLike('u.fullname', $request->getGet('keyword'))
                        ->orWhere('u.email', $request->getGet('keyword'))
                        ->groupEnd();
                }

                if(array_key_exists($request->getGet('status'), $this->statusList())){
                    $this->ticketsModel->where('tickets.status', $request->getGet('status'));
                }
                if($request->getGet('date_created')){
                    $dates = explode(' - ', $request->getGet('date_created'));
                    if(($start = strtotime($dates[0])) && ($end = strtotime($dates[1].' +1 day'))){
                        $this->ticketsModel->groupStart()
                            ->where('tickets.date>=',$start)
                            ->where('tickets.date<', $end)
                            ->groupEnd();
                    }
                }
                if($request->getGet('last_update')){
                    $dates = explode(' - ', $request->getGet('last_update'));
                    if(($start = strtotime($dates[0])) && ($end = strtotime($dates[1].' +1 day'))){
                        $this->ticketsModel->groupStart()
                            ->where('tickets.last_update>=',$start)
                            ->where('tickets.last_update<', $end)
                            ->groupEnd();
                    }
                }
                if($request->getGet('overdue') == '1'){
                    $this->ticketsModel->groupStart()
                        ->where('tickets.status', 1)
                        ->orWhere('tickets.status', 3)
                        ->orWhere('tickets.status', 4)
                        ->groupEnd()
                        ->where('tickets.last_update<', time()-($this->settings->config('overdue_time')*60*60));
                }
                break;
            case 'overdue':
                $this->ticketsModel->groupStart()
                    ->where('tickets.status', 1)
                    ->orWhere('tickets.status', 3)
                    ->orWhere('tickets.status', 4)
                    ->groupEnd()
                    ->where('tickets.last_update<', time()-($this->settings->config('overdue_time')*60*60));
                break;
            case 'answered':
                $this->ticketsModel->where('tickets.status',2);
                break;
            case 'closed':
                $this->ticketsModel->where('tickets.status',5);
                break;
            default:
                $this->ticketsModel->groupStart()
                    ->where('tickets.status', 1)
                    ->orWhere('tickets.status', 3)
                    ->orWhere('tickets.status', 4)
                    ->groupEnd();
                break;
        }

        /**
         *Carga el listado de los tickets por departamento
         **/
        if(!$search_department){ 
            $this->ticketsModel->groupStart();
            if(is_array($staff_departments)){
                foreach ($staff_departments as $item){
                    $this->ticketsModel->orWhere('tickets.department_id', $item->id)
                    ->orLike('department_id_child', '"'.$item->id.'"')
                    ->orLike('advisor_id', staff_data('id')) //Para listar tickets por Asesor
                    ->orLike('director_commercial_id', staff_data('id')); //Para listar tickets del Director Comercial
                }
            }
            $this->ticketsModel->groupEnd();
        }

        if($request->getGet('sort')){
            $sort_list = [
                'id' => 'tickets.id',
                'subject' => 'tickets.subject',
                'last_reply' => 'tickets.last_update',
                'department' => 'd.name',
                'priority' => 'p.id',
                'status' => 'tickets.status'
            ];
            if(array_key_exists($request->getGet('sort'), $sort_list)){
                $this->ticketsModel->orderBy($sort_list[$request->getGet('sort')],($request->getGet('order') == 'ASC' ? 'ASC' : 'DESC'));
            }
        }else{
            $this->ticketsModel->orderBy('tickets.last_update','desc');
        }

        /**
         * Obtiene el nombre del departamento
         **/
        $db = Database::connect();
        $result = $this->ticketsModel->select('tickets.*, u.fullname, d.name as department_name,
        p.name as priority_name, p.color as priority_color, 
        IF(last_replier=0, "", (SELECT username FROM '.$db->prefixTable('staff').' WHERE id=last_replier)) as staff_username')
            ->join('users as u', 'u.id=tickets.user_id')
            ->join('departments as d', 'd.id=tickets.department_id')
            ->join('priority as p','p.id=tickets.priority_id')
            ->paginate($this->settings->config('tickets_page'));
        return [
            'result' => $result,
            'pager' => $this->ticketsModel->pager
        ];
    }

    public function countStatus($status)
    {
        switch ($status){
            case 'active':
                $total = $this->ticketsModel->groupStart()
                    ->where('status', 1)
                    ->orWhere('status', 3)
                    ->orWhere('status', 4)
                    ->groupEnd()
                    ->countAllResults();
                break;
            case 'overdue':
                $total = $this->ticketsModel->groupStart()
                    ->where('status', 1)
                    ->orWhere('status', 3)
                    ->orWhere('status', 4)
                    ->groupEnd()
                    ->where('last_update<', time()-($this->settings->config('overdue_time')*60*60))
                    ->countAllResults();
                break;
            case 'answered':
                $total = $this->ticketsModel->where('status', 2)
                    ->countAllResults();
                break;
            case 'closed':
                $total = $this->ticketsModel->where('status', 5)
                    ->countAllResults();
                break;
            default:
                $total = 0;
                break;
        }
        return $total;
    }

    public function isOverdue($date, $status)
    {
        $timeleft = time()-$date;
        if($timeleft >= ($this->settings->config('overdue_time')*60*60) && in_array($status,[1,3,4])){
            return true;
        }
        return false;
    }

    public function purifyHTML($message)
    {
        $config = \HTMLPurifier_Config::createDefault();
        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($message);
    }

    /*
     * ----------------------------------------
     * Notes
     * ----------------------------------------
     */
    public function getNotes($ticket_id)
    {
        $ticketsNotesModel = new TicketNotesModel();
        $q = $ticketsNotesModel->select('ticket_notes.*, staff.username, staff.fullname')
            ->orderBy('date','desc')
            ->join('staff', 'staff.id=ticket_notes.staff_id')
            ->where('ticket_id', $ticket_id)
            ->get();
        if($q->resultID->num_rows == 0){
            return null;
        }
        $r =$q->getResult();
        $q->freeResult();
        return $r;
    }
    public function getNote($note_id)
    {
        $ticketsNoteModel = new TicketNotesModel();
        return $ticketsNoteModel->find($note_id);
    }
    public function addNote($ticket_id, $staff_id, $note)
    {
        $ticketNotesModel = new TicketNotesModel();
        return $ticketNotesModel->insert([
            'ticket_id' => $ticket_id,
            'staff_id' => $staff_id,
            'date' => time(),
            'message' => esc($note)
        ]);
    }
    public function deleteNote($ticket_id, $note_id)
    {
        $ticketNotesModel = new TicketNotesModel();
        $ticketNotesModel->where('ticket_id', $ticket_id)
            ->where('id', $note_id)
            ->delete();
    }
    public function updateNote($note, $note_id)
    {
        $ticketNotesModel = new TicketNotesModel();
        $ticketNotesModel->update($note_id, [
            'message' => esc($note)
        ]);
    }

    /**
     * Funcion para obtener nombres de los departamentos adjuntos.
     * */
    public function getNamesDepAdjuntos($ticket){
        $namesDep = "";
        $departments = Services::departments();
        $departments_list = $departments->getAll();
        $departments_child = unserialize($ticket->department_id_child);
        if(is_array($departments_child) && count($departments_child) !=0){
            foreach($departments_child as $dep){
                foreach($departments_list as $itemDep){
                    if($itemDep->id == $dep)
                        $namesDep .= $itemDep->name.", ";
                }
            }
            $namesDep= substr($namesDep, 0, -2);
        }
        return $namesDep;  
    }


    public function getNamesDepAdjuntosById($idChild){
        $names= "";
        $departments = Services::departments();
        $departments_list = $departments->getAll();
        $departments_child = unserialize($idChild);
        if(is_array($departments_child) && count($departments_child) !=0){
            foreach($departments_child as $dep){
                foreach($departments_list as $itemDep){
                    if($itemDep->id == $dep)
                        $names .= $itemDep->name.", ";
                }
            }
            $names= substr($names, 0, -2);
        } else {
            foreach($departments_list as $itemDep){
                if($itemDep->id == $idChild)
                    $names = $itemDep->name;
            }
        }
        return $names;
    }

    /**
     * Funcion para contabilizar las respuestas del ticket
     * Y validar que al menos tenga una respuesta para cerrar el ticket
     */
    public function getCountReplies($ticket_id)
    {
        $replies = $this->ticketsModel->select('replies')
        ->where('id', $ticket_id)
        ->get(1);
        if($replies->resultID->num_rows == 0){
            return null;
        }
        return $replies->getRow();  
    }

    /**
     * Funcion para listado de departamentos
     * */
    public function getListTickets()
    {
        $q = $this->ticketsModel->orderBy('id','asc')
            ->get();
        /*if($q->resultID->num_rows == 0){
            return null;
        }*/
        $result = $q->getResult();
        $q->freeResult();
        return $result;
    }

    public function getEmailSolicitude($ticket_id)
    {
        $q = $this->clientSolicitudeModel->select('*')
                ->where('ticket', $ticket_id)
                ->get(1);
        if($q->resultID->num_rows == 0 ){
            return null;
        }
        return $q->getRow();
    }

    public function getStatusTicketSolicitude($ticket_id)
    {
        $q = $this->ticketsModel->select('*')
            ->where('id', $ticket_id)
            ->get(1);
        if($q->resultID->num_rows == 0){
            return null;
        }
        return $q->getRow();
    }

    /*
     * --------------------------------
     * Create Loan Payments Received
     * --------------------------------
     */
    public function createLoanPaymentSolicitude($nameClient, $loanNumber, $email, $ticket_id)
    {
        $this->loanPaymentModel->protect(false);

        $this->loanPaymentModel->insert([
            'client_name' => $nameClient,
            'loan_number' => $loanNumber,
            'date' => time(),
            'last_date' => time(),
            'email' => $email,
            'ticket_id' => $ticket_id,
        ]);
        $this->loanPaymentModel->protect(true);
    }   

    /** METODOS PORTAL CLIENTE */
        public function getTicketForPortal($ticketId)
    {
        // Usamos LEFT JOIN para asegurar que el ticket se encuentre
        $q = $this->ticketsModel->select('tickets.*, d.name as department_name, p.name as priority_name, p.color as priority_color, u.fullname, u.email, u.avatar')
            ->join('departments as d', 'd.id=tickets.department_id', 'left')
            ->join('priority as p', 'p.id=tickets.priority_id', 'left')
            ->join('users as u', 'u.id=tickets.user_id', 'left')
            ->where('tickets.id', $ticketId) // La búsqueda es solo por ID
            ->get(1);

        if ($q->resultID->num_rows == 0) {
            return null;
        }
        return $q->getRow();
    }
    /**
     * [MÉTODO EXCLUSIVO DEL PORTAL]
     * Envía una notificación por correo al cliente cuando se crea un
     * ticket desde el portal, usando una plantilla y logo personalizados.
     */
    public function newTicketNotificationPortal($ticket)
    {
        // Instancia la librería de correos
        $emails = new Emails();
        
        // Define la URL específica del portal para el enlace
        $portal_url = site_url('portal-atencion-cliente');
        
        // Define la URL del nuevo logo de Austrobank
        $logo_url = 'https://cert-austrobank-homebanking.fit-bank.com/imagenNotificacion';
        
        // Crea el código HTML completo para la imagen del logo
        $logo_html = '<img src="' . $logo_url . '" alt="' . site_config('site_name') . '" style="max-width: 180px; height: auto;">';

        // Llama a la librería de correos para enviar el mensaje
        $emails->sendFromTemplate(
            'new_ticket_portal', // <--- Usa la nueva plantilla
            [
                '%client_name%'       => $ticket->fullname,
                '%client_email%'      => $ticket->email,
                '%ticket_id%'         => $ticket->id,
                '%ticket_subject%'    => $ticket->subject,
                '%ticket_department%' => $ticket->department_name,
                '%ticket_status%'     => lang('Client.form.open'),
                '%ticket_priority%'   => $ticket->priority_name,
                '%portal_url%'        => $portal_url,
                '%logo_html%'         => $logo_html, // <-- Pasa el HTML del logo a la plantilla
            ], 
            $ticket->email, 
            $ticket->department_id
        );
    }


    public function notifyStaffOfPortalReply($ticket, $message)
    {
        $emails = new Emails();
        $staffModel = new \App\Models\Staff();
        
        $departmentIds = unserialize($ticket->department_id_child) ?: [];
        $departmentIds[] = $ticket->department_id;
        $departmentIds = array_unique($departmentIds);

        $staffToNotify = $staffModel->groupStart();
        foreach ($departmentIds as $depId) {
            $staffToNotify->orLike('department', '"' . $depId . '"');
        }
        $staffList = $staffToNotify->groupEnd()->get()->getResult();

        $ticket_url = site_url(route_to('staff_ticket_view', $ticket->id));
        $namesDptsAdjunto = $this->getNamesDepAdjuntos($ticket);
        
        foreach ($staffList as $staffMember) {
            $emails->sendFromTemplate(
                'portal_client_reply', 
                [
                    '%staff_name%'         => $staffMember->fullname,
                    '%client_name%'        => $ticket->fullname,
                    '%ticket_id%'          => $ticket->id,
                    '%ticket_subject%'     => $ticket->subject,
                    '%message%'            => $message,
                    '%ticket_url%'         => $ticket_url,
                    '%ticket_department%'  => $ticket->department_name,
                    '%ticket_dtpsAdjunto%' => $namesDptsAdjunto != "" ? $namesDptsAdjunto : 'N/D',
                    '%ticket_status%'      => lang('Client.form.' . $this->statusName($ticket->status)),
                    '%ticket_priority%'    => $ticket->priority_name,
                    '%company_name%'       => site_config('site_name'),
                ],
                $staffMember->email,
                $ticket->department_id
            );
        }
    }


    public function replyTicketNotificationPortal($ticket, $message, $attachments = null)
    {
        $emails = new Emails();

        // Construimos la URL segura y directa al ticket DENTRO del portal
        $portal_ticket_url = base_url(route_to('portal_ver_ticket', $ticket->id));

        $files = [];
        if (is_array($attachments)) {
            foreach ($attachments as $file) {
                $files[] = [
                    'name'      => $file['name'],
                    'path'      => WRITEPATH . 'attachments/' . $file['encoded_name'],
                    'file_type' => $file['file_type']
                ];
            }
        }

        $emails->sendFromTemplate(
            'staff_reply_portal', // <-- Usamos la nueva plantilla 'staff_reply_portal'
            [
                '%client_name%'        => $ticket->fullname,
                '%ticket_id%'          => $ticket->id,
                '%ticket_subject%'     => $ticket->subject,
                '%message%'            => $message,
                '%ticket_department%'  => $ticket->department_name,
                '%ticket_dtpsAdjunto%' => $this->getNamesDepAdjuntos($ticket),
                '%ticket_status%'      => lang('Client.form.' . $this->statusName($ticket->status)),
                '%ticket_priority%'    => $ticket->priority_name,
                '%company_name%'       => site_config('site_name'),
                '%portal_ticket_url%'  => $portal_ticket_url, // URL exclusiva del portal
                '%ticket_url%'         => $portal_ticket_url 
            ],
            $ticket->email,
            $ticket->department_id,
            $files
        );
    }
}