<?php
/**
 * @package AboHelpdesk
 * @author: Adrian Carchipulla
 * @Copyright (c) 2022
 * @link https://www.linkedin.com/in/adrian-carchipulla/
 */

namespace App\Controllers\Staff;


use App\Controllers\BaseController;
use Config\Services;

class SettingsAbo extends BaseController
{

    #Funcion para cargar los parametros de configuracion general ABOHELPDESK.
    public function general (){

        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        if($this->request->getPost('do') == 'submit'){
            $validation = Services::validation();
            $validation->setRule('client_email_order','client_email_order','required|in_list[asc,desc]');
            $validation->setRule('client_emails_page','client_emails_page','required|is_natural_no_zero');


            if($validation->withRequest($this->request)->run() == false){
                $error_msg = $validation->listErrors();
            }elseif (defined('HDZDEMO')){
                $error_msg = 'This is not possible in demo version.';
            }else{
                $this->settings->save([
                    'client_email_order' => $this->request->getPost('client_email_order'),
                    'client_emails_page' => $this->request->getPost('client_emails_page'),
                ]);
                $this->session->remove('cron');
                $this->session->setFlashdata('form_success',lang('Admin.abo.updatedSettingsABO'));
                return redirect()->to(current_url());
            }
        }

        return view('staff/abo_general',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null
        ]);
    }

    #Funcion para cargar el listado de las parametrizaciones generales del sistema.
    public function params(){
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        $paramsLib = new \App\Libraries\SystemParams();
        return view('staff/abo_params',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'paramsList' => $paramsLib->getAllParams()
        ]);
    }

    #Funcion para crear parametro general del sistema.
    public function newSystemParam ()
    {

        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }
        echo 'value number: '.$this->request->getPost('vnumeroNew');
        if($this->request->getPost('do') == 'submit'){
            $validation = Services::validation();
            $validation->setRule('cparam', 'cparam', 'required',[
                'required' => lang('Admin.error.enterParam')
            ]);

            $validation->setRule('descripcion','descripcion', 'required',[
                'required' => lang('Admin.error.enterDescriptionParam')
            ]);

            if($this->request->getPost('type') === 'T'){
                $validation->setRule('vtextoNew', 'vtextoNew', 'required',
                    [
                        'required' => lang('Admin.error.enterParamText')
                        //,'alpha_space' => lang('Admin.error.validText')
                    ]);
            } else {
                $validation->setRule('vnumeroNew', 'vnumeroNew','required|is_natural',
                    [
                        'required' => lang('Admin.error.enterParamText'),
                        'is_natural' => lang('Admin.error.validaNumber')
                    ]);
            }

            if($validation->withRequest($this->request)->run() == false){
                $error_msg = $validation->listErrors();
            } else {
                $paramsLib = new \App\Libraries\SystemParams();
                $paramsLib->createParam(
                    $this->request->getPost('cparam'),
                    $this->request->getPost('type'),
                    $this->request->getPost('vtextoNew'),
                    $this->request->getPost('type') === 'T' ? null : $this->request->getPost('vnumeroNew'),
                    $this->request->getPost('descripcion')
                );

                $this->session->setFlashdata('form_success',lang('Admin.params.msgNewParam'));
                return redirect()->to(current_url());
            }
        }

        return view('staff/abo_params_form',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null
        ]);
    }

    #Funcion para actualizar parametro general del sistema.
    public function paramsEdit($id_param){
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        $paramsLib = new \App\Libraries\SystemParams();
        if(!$param = $paramsLib->getParamById($id_param)){
            return redirect()->route('staff_email_templates');
        }

        if($this->request->getPost('do') == 'submit'){
            $validation = Services::validation();
            $validation->setRule('cparam', 'cparam', 'required',[
                'required' => lang('Admin.error.enterParam')
            ]);

            $validation->setRule('descripcion','descripcion', 'required',[
                'required' => lang('Admin.error.enterDescriptionParam')
            ]);

            if($this->request->getPost('type') === 'T'){
                $validation->setRule('vtexto', 'vtexto', 'required',
                    [
                        'required' => lang('Admin.error.enterParamText')
                    //,'alpha_space' => lang('Admin.error.validText')
                    ]);
            } else {
                $validation->setRule('vnumero', 'vnumero','required|is_natural',
                    [
                        'required' => lang('Admin.error.enterParamText'),
                        'is_natural' => lang('Admin.error.validaNumber')
                    ]);
            }

            if($validation->withRequest($this->request)->run() == false){
                $error_msg = $validation->listErrors();
            } else {
                $paramsLib->updateParam([
                    'cparam' => $this->request->getPost('cparam'),
                    'type_param' => $this->request->getPost('type'),
                    'param_text' => $this->request->getPost('vtexto'),
                    'param_number' => $this->request->getPost('vnumero'),
                    'param_description' => $this->request->getPost('descripcion')
                ], $id_param);

                $this->session->setFlashdata('form_success',lang('Admin.params.msgEditParam'));
                return redirect()->to(current_url());
            }
        }

        return view('staff/abo_params_form',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'param' => $param
        ]);
    }

    #Para cargar el listado de la parametrizacion de adjuntos por departamento
    public function AttachmentsDepartment () {
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        #Instancia para obtener el listado de parametrizacion tickets por departamento.
        $DptTicketConfig = new \App\Libraries\SettingsDepartment();

        //Para eliminar la parametrizacion de tickets por departamento
        if($this->request->getPost('do') == 'remove'){
            $DptTicketConfig->deleteConfigDepTicket($this->request->getPost('department_ticket_id'));
            $this->session->setFlashdata('form_success',lang('Admin.abo.deleteConfigDepartment'));
            return redirect()->to(current_url());
        }

        return view('staff/abo_attachments',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'list_confdepartments' => $DptTicketConfig->getList(),
        ]);
    }

    #Insert parametrizacion de adjuntos por departamento
    public function createAttachmentDepartment ()
    {
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        $configDepNew = new \App\Libraries\SettingsDepartment();

        if($this->request->getPost('do') == 'submit'){
            $validation = Services::validation();
            $validation->setRules([
                'ticket_attachments_dep' => 'required|is_natural',
                'ticket_size_dep' => 'required|greater_than[0]',
                'ticket_type_dep' => 'required'
            ],[
                'ticket_attachments_dep' => [
                    'required' => lang('Admin.error.enterNumberAttachmets'),
                    'is_natural' => lang('Admin.error.no_zero_nroattachment')
                ],
                'ticket_size_dep' => [
                    'required' => lang('Admin.error.enterSizeAttachmets'),
                    'greater_than' => lang('Admin.error.no_zero_sizeattachment')
                ],
                'ticket_type_dep' => [
                    'required' => lang('Admin.error.enterTypeFileAttachment')
                ]
            ]);

            if($validation->withRequest($this->request)->run() == false){
                $error_msg = $validation->listErrors();
            } else {
                $file_types = explode(',', $this->request->getPost('ticket_type_dep'));
                $file_types = array_map(function ($e){
                    $e = trim($e);
                    if($e != ''){
                        return $e;
                    }else{
                        return null;
                    }
                }, $file_types);
                $file_types = array_filter($file_types, function ($e){
                    return (trim($e) != '');
                });
                $configDepNew->createConfigDepTicket(
                    $this->request->getPost('ticket_attachment_dep'),
                    $this->request->getPost('ticket_parameter_dep'),
                    $this->request->getPost('ticket_department_dep'),
                    $this->request->getPost('ticket_attachments_dep'),
                    $this->request->getPost('ticket_size_dep'),
                    serialize($file_types)
                );

                $this->session->setFlashdata('form_success',lang('Admin.abo.msgNewTicketDpt'));
                return redirect()->to(current_url());
            }
        }

        return view('staff/abo_attachments_form',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null
        ]);
    }

    #Update la parametrizacion de tickets por departamento
    public function attachmentsEdit ($id_configdep)
    {
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        $configDep = new \App\Libraries\SettingsDepartment();
        if(!$cad = $configDep->getConfigById($id_configdep)){
            return redirect()->route('staff_email_templates');
        }

        if($this->request->getPost('do') == 'submit'){
            $validation = Services::validation();
            $validation->setRules([
                'ticket_attachments_dep' => 'required|is_natural',
                'ticket_size_dep' => 'required|greater_than[0]',
                'ticket_type_dep' => 'required'
            ],[
                'ticket_attachments_dep' => [
                    'required' => lang('Admin.error.enterNumberAttachmets'),
                    'is_natural' => lang('Admin.error.no_zero_nroattachment')
                ],
                'ticket_size_dep' => [
                    'required' => lang('Admin.error.enterSizeAttachmets'),
                    'greater_than' => lang('Admin.error.no_zero_sizeattachment')
                ],
                'ticket_type_dep' => [
                    'required' => lang('Admin.error.enterTypeFileAttachment')
                ]
            ]);

            if($validation->withRequest($this->request)->run() == false){
                $error_msg = $validation->listErrors();
            } else {
                $file_types = explode(',', $this->request->getPost('ticket_type_dep'));
                $file_types = array_map(function ($e){
                    $e = trim($e);
                    if($e != ''){
                        return $e;
                    }else{
                        return null;
                    }
                }, $file_types);
                $file_types = array_filter($file_types, function ($e){
                    return (trim($e) != '');
                });
                $configDep->updateConfigDepTicket([
                    'ticket_attachment' => $this->request->getPost('ticket_attachment_dep'),
                    'source_parameter' => $this->request->getPost('ticket_parameter_dep'),
                    'department_id' => $this->request->getPost('ticket_department_dep'),
                    'ticket_attachment_number' => $this->request->getPost('ticket_attachments_dep'),
                    'ticket_file_size' => $this->request->getPost('ticket_size_dep'),
                    'ticket_file_type' => serialize($file_types)
                ], $id_configdep);

                $this->session->setFlashdata('form_success',lang('Admin.abo.msgEditTicketDpt'));
                return redirect()->to(current_url());
            }
        }

        return view('staff/abo_attachments_form',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'ta_department' => $cad
        ]);
    }

    #Para listar los tipos de solicitud
    public function solicitudes(){
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        return view('staff/abo_types_solicitude',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'types_solicitude' => $this->settingsAbo->getSolicitudeAll()
        ]);
    }

    public function newSolicitude ()
    {
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        if($this->request->getPost('do') == 'submit'){
            $validation = Services::validation();
            $validation->setRule('orden', 'orden', 'required|is_natural|greater_than[0]',[
                    'required' => lang('Admin.errorAbo.enterOrderSolicitude'),
                    'is_natural' => lang('Admin.errorAbo.nozero_orderSolicitude'),
                    'greater_than' => lang('Admin.errorAbo.nozero_orderSolicitude')
                ]);

            $validation->setRule('descripcion','descripcion', 'required',[
                    'required' => lang('Admin.errorAbo.enterDescripctionSolicitude'),
                ]);

            if($this->request->getPost('type') === 'select'){
                $validation->setRule('value', 'value', 'required',[
                    'required' => lang('Admin.errorAbo.enterTypeSolicitude'),
                ]);
            }

            if($validation->withRequest($this->request)->run() == false){
                $error_msg = $validation->listErrors();
            } else {
                $this->settingsAbo->newSolicitude([
                    'description' => $this->request->getPost('descripcion'),
                    'solicitude_order' => $this->request->getPost('orden'),
                    'type' => $this->request->getPost('type'),
                    'value' => $this->request->getPost('value'),
                    'multiple_select' => $this->request->getPost('isMultiple') ? true : false,
                    'enabled' => $this->request->getPost('status')
                ]);

                $this->session->setFlashdata('form_success',lang('Admin.abo.msgEditSolicitude'));
                return redirect()->to(current_url());
            }
        }

        return view('staff/abo_types_solicitude_form',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'type_solicitude' => $this->settingsAbo->typeList()
        ]);
    }

    public function solicitudeEdit ($id_solicitude)
    {
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        if(!$sol = $this->settingsAbo->getSolicitudeById($id_solicitude)){
            return redirect()->route('staff_settings_abo');
        }

        if($this->request->getPost('do') == 'submit'){
            $validation = Services::validation();
            $validation->setRule('orden', 'orden', 'required|is_natural|greater_than[0]',[
                'required' => lang('Admin.errorAbo.enterOrderSolicitude'),
                'is_natural' => lang('Admin.errorAbo.nozero_orderSolicitude'),
                'greater_than' => lang('Admin.errorAbo.nozero_orderSolicitude')
            ]);

            $validation->setRule('descripcion','descripcion', 'required',[
                'required' => lang('Admin.errorAbo.enterDescripctionSolicitude'),
            ]);

            $validation->setRule('color','color', 'required',[
                'required' => lang('Admin.errorAbo.enterColorSolicitude'),
            ]);

            if($this->request->getPost('type') === 'select'){
                $validation->setRule('value', 'value', 'required',[
                    'required' => lang('Admin.errorAbo.enterTypeSolicitude'),
                ]);
            }

            if($validation->withRequest($this->request)->run() == false){
                $error_msg = $validation->listErrors();
            } else {
                $this->settingsAbo->updateSolicitude([
                    'description' => $this->request->getPost('descripcion'),
                    'solicitude_order' => $this->request->getPost('orden'),
                    'type' => $this->request->getPost('type'),
                    'color' => $this->request->getPost('color'),
                    'value' => $this->request->getPost('value'),
                    'enabled' => $this->request->getPost('status')
                ], $id_solicitude);

                $this->session->setFlashdata('form_success',lang('Admin.abo.msgEditSolicitude'));
                return redirect()->to(current_url());
            }
        }

        return view('staff/abo_types_solicitude_form',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'solicitude' => $sol,
            'type_solicitude' => $this->settingsAbo->typeList()
        ]);
    }

}