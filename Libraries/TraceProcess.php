<?php
/**
 * @package AboHelpdesk
 * @author: Adrian Carchipulla
 * @Copyright (c) 2022
 * @link https://www.linkedin.com/in/adrian-carchipulla/
 */

namespace App\Libraries;

use Config\Database;
use Config\Services;
use App\Models\ClientEmailModel;
use App\Models\Tickets;
use App\Models\Departments;

class TraceProcess 
{

    protected $ticketsModel;
    protected $clientEmailModel;
    protected $settings;
    protected $departmentsModel;
    public function __construct()
    {
        $this->settings = Services::settings();
        $this->clientEmailModel = new ClientEmailModel();
        $this->ticketsModel = new Tickets();
        $this->departmentsModel = new Departments();

    }

    public function getListEmailsClient ()
    {
        $q = $this->clientEmailModel->select('u.fullname as advisor, cs.id, cs.identification, cs.client_name as cliente, cs.type_person , cs.ticket, cs.date datecreate, client_email.*')
            ->join('client_solicitude as cs','cs.id=client_email.solicitude_id')
            ->join('tickets as t','t.id=cs.ticket')
            ->join('users as u','u.id=t.user_id')
            ->orderBy('date',$this->settings->config('client_email_order'))
            ->paginate($this->settings->config('client_emails_page'), 'default');
        return [
            'result' => $q,
            'pager' => $this->clientEmailModel->pager
        ];
    }

    public function getListValijas()
    {
        $request = Services::request();
        $id_process = $request->getGet('id_proceso');
        if($request->getGet('do')==='search' && $id_process != null){
            $q = $this->ticketsModel->select('tickets.*, d.name as department_name, p.name as priority_name, p.color as priority_color, u.fullname as user, dusr.department as dep_user, u.email, staffresp.fullname as staff, staffresp.department as dep_staff, tm.id as id_message, tm.message, tm.date as frespuesta')
                ->join('departments as d','d.id=tickets.department_id')
                ->join('priority as p','p.id=tickets.priority_id')
                ->join('users as u','u.id=tickets.user_id') 
                ->join('tickets_messages tm', 'tm.ticket_id=tickets.id')
                ->join('staff as staffresp', 'staffresp.id=tm.staff_id', 'left')
                ->join('staff as dusr', 'dusr.email=u.email', 'left')
                ->where('tickets.department_id', $id_process)
                //->notLike('tm.staff_id', 0) //Para mostrar las respuestas de los mensajes de los agentes
                ->orderBy('tickets.id', 'desc')
                ->orderBy('tm.id', 'asc')
                ->paginate($this->settings->config('client_emails_page'), 'default');
            return [
                'result' => $q,
                'pager' => $this->ticketsModel->pager
            ];
        } else {
            $q = $this->ticketsModel->select('tickets.*, d.name as department_name, p.name as priority_name, p.color as priority_color, u.fullname as user, dusr.department as dep_user, u.email, staffresp.fullname as staff, staffresp.department as dep_staff, tm.id as id_message, tm.message, tm.date as frespuesta')
                ->join('departments as d','d.id=tickets.department_id')
                ->join('priority as p','p.id=tickets.priority_id')
                ->join('users as u','u.id=tickets.user_id') 
                ->join('tickets_messages tm', 'tm.ticket_id=tickets.id')
                ->join('staff as staffresp', 'staffresp.id=tm.staff_id', 'left')
                ->join('staff as dusr', 'dusr.email=u.email', 'left')
                ->notLike('tickets.department_id', 14) //Omite el proceso de Atención al cliente
                ->orderBy('tickets.id', 'desc')
                ->orderBy('tm.id', 'asc')
                ->paginate($this->settings->config('client_emails_page'), 'default');
            return [
                'result' => $q,
                'pager' => $this->ticketsModel->pager
            ];
        }
    }

    public function getNameStatus($id)
    {
        switch($id){
            case 1:
                echo '<span class="badge badge-success">'.lang('Admin.form.open').'</span>';
                break;
            case 2:
                echo '<span class="badge badge-dark">'.lang('Admin.form.answered').'</span>';
                break;
            case 3:
                echo '<span class="badge badge-warning">'.lang('Admin.form.awaiting_reply').'</span>';
                break;
            case 4:
                echo '<span class="badge badge-info">'.lang('Admin.form.in_progress').'</span>';
                break;
            case 5:
                echo '<span class="badge badge-danger">'.lang('Admin.form.closed').'</span>';
                break;
        }
    }

    public function getProcess()
    {
        return $process = array(
            '4' => 'Valijas',
            '10' => 'Créditos - Originación Comercial'
        );
    }

    public function getDepartmentProcess()
    {
        $q = $this->departmentsModel->Where('id_padre', 0)
            ->notLike('id', 14)
            ->orderBy('dep_order','asc')
            ->get();
        if($q->resultID->num_rows == 0){
            return null;
        }
        $result = $q->getResult();
        $q->freeResult();
        return $result;
    }

}