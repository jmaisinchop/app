<?php
function set_timezone($timezone)
{
    if($timezone != '' && in_array($timezone, timezone_identifiers_list())){
        date_default_timezone_set($timezone);
    }
}
function site_logo()
{
    return \Config\Services::settings()->getLogo();
}

function site_config($var)
{
    return \Config\Services::settings()->config($var);
}

function client_online()
{
    return \Config\Services::client()->isOnline();
}

function client_data($var)
{
    return \Config\Services::client()->getData($var);
}

function staff_data($var)
{
    return \Config\Services::staff()->getData($var);
}

function staff_avatar($avatarFile='')
{
    $default_avatar = base_url('assets/helpdeskz/images/agent.jpg');
    if($avatarFile == ''){
        return $default_avatar;
    }
    $upload_avatar = \Config\Helpdesk::UPLOAD_PATH.DIRECTORY_SEPARATOR.$avatarFile;
    if(!file_exists($upload_avatar)){
        return $default_avatar;
    }
    return base_url('upload/'.$avatarFile);
}

function user_avatar($avatarFile='')
{
    $default_avatar = base_url('assets/helpdeskz/images/user.jpg');
    if($avatarFile == ''){
        return $default_avatar;
    }
    $upload_avatar = \Config\Helpdesk::UPLOAD_PATH.DIRECTORY_SEPARATOR.$avatarFile;
    if(!file_exists($upload_avatar)){
        return $default_avatar;
    }
    return base_url('upload/'.$avatarFile);
}

function staff_info($staff_id)
{
    static $staffList;
    if($staff_id == 0){
        return ['fullname' => '-', 'avatar' => staff_avatar()];
    }
    if(!is_array($staffList) || !isset($staffList[$staff_id])){
        if(!$data = \Config\Services::staff()->getRow(['id' => $staff_id], 'fullname, avatar, email')){
            return $staffList[$staff_id] = ['fullname' => '-', 'avatar' => staff_avatar()];
        }else{
            return $staffList[$staff_id] = ['fullname' => $data->fullname, 'avatar' => staff_avatar($data->avatar), 'email' => $data->email];
        }
    }else{
        return $staffList[$staff_id];
    }
}

function uri_page()
{
    static $page;
    if(!$page){
        $uri = \Config\Services::uri();
        $page =$uri->getSegment(2);
    }
    return $page;
}
/*
 * Knowledge Base
 */
function kb_parents($parent_id)
{
    return \Config\Services::kb()->getParents($parent_id);
}
function kb_categories($parent=0, $public=true)
{
    return \Config\Services::kb()->getCategories($parent, $public);
}
function kb_count_articles($category_id, $public=true)
{
    return \Config\Services::kb()->countArticles($category_id, $public);
}

function kb_count_articles_category($category_id, $public=true)
{
    return \Config\Services::kb()->totalArticlesInCat($category_id, $public);
}

function kb_cat_move_link($category_id, $parent)
{
    return \Config\Services::kb()->moveUpOrDownLink($category_id, $parent);
}

function kb_articles_category($category_id, $public=true){
    return \Config\Services::kb()->articlesUnderCategory($category_id, $public);
}

function kb_articles($category_id, $public=true)
{
    return \Config\Services::kb()->getArticles($category_id, $public);
}

function kb_popular($public = 1)
{
    return \Config\Services::kb()->popularArticles($public);
}

function kb_newest($public = 1)
{
    return \Config\Services::kb()->newestArticles($public);
}

function resume_content($text, $chars, $clean_html = true)
{
    if($clean_html){
        $text = strip_tags($text);
    }
    if(strlen($text) > $chars){
        return substr($text, 0, ($chars-3)).'...';
    }else{
        return $text;
    }
}

#Language
function lang_replace($var, $field, $value=null)
{
    if(!is_array($field))
    {
        $field = array($field => $value);
    }
    return str_replace(array_keys($field), array_values($field), lang($var));
}
#Attachments
function article_files($article_id)
{
    return \Config\Services::attachments()->getList(['article_id' => $article_id]);
}

function ticket_files($ticket_id, $msg_id)
{
    return \Config\Services::attachments()->getList(['ticket_id' => $ticket_id, 'msg_id' => $msg_id]);
}

#Date
function dateFormat($data)
{
    return date(site_config('date_format'), $data);
}

#tickets
function count_tickets($status)
{
    return \Config\Services::staff()->countTicketsByStatus($status);
}

function sort_link($id, $string)
{
    $url = current_url(true);
    $url = parse_url($url);
    $query = (isset($url['query'])) ? $url['query'] : '';
    parse_str($query, $output);
    if(isset($output['sort']) && $output['sort'] == $id){
        if(isset($output['order']) && $output['order'] == 'DESC'){
            $output['order'] = 'ASC';
            $icon = '<i class="fa fa-caret-down"></i></a>';
        }else{
            $output['order'] = 'DESC';
            $icon = '<i class="fa fa-caret-up"></i></a>';
        }
    }else{
        $output['order'] = 'DESC';
        $icon = '<i class="fa fa-sort"></i></a>';
    }
    $output['sort'] = $id;

    $query = http_build_query($output);
    return '<span data-href="'.current_url().'?'.$query.'" class="pointer">'.$string.' '.$icon.'</span>';
}

function isOverdue($date,$status)
{
    return \Config\Services::tickets()->isOverdue($date, $status);
}

function time_ago($time, $staff=true)
{

    if(date("d-m-Y", $time) == date("d-m-Y")){
        $lang = ($staff ? 'Admin.form.todayAt' : 'Client.todayAt');
        return lang_replace($lang, ['%date%' => date('h:i a', $time)]);
    }elseif(date("d-m-Y", $time) == date('d-m-Y', strtotime("+1 day ago"))){
        $lang = ($staff ? 'Admin.form.yesterdayAt' : 'Client.yesterdayAt');
        return lang_replace($lang, ['%date%' => date('h:i a', $time)]);
    }else{
        return dateFormat($time);
    }
}

function count_status($status)
{
    return \Config\Services::tickets()->countStatus($status);
}

function max_file_size()
{
    static $file_size;
    if(!$file_size){
        $max_upload = (int)(ini_get('upload_max_filesize'));
        $max_post = (int)(ini_get('post_max_size'));
        $memory_limit = (int)(ini_get('memory_limit'));
        $file_size = min($max_upload, $max_post, $memory_limit);
        $file_size = $file_size*1024;
    }
    return $file_size;

}

function countDepartmentTickets($department_id)
{
    return \Config\Services::departments()->countTickets($department_id);
}

function countDepartmentAgents($department_id)
{
    return \Config\Services::departments()->countAgents($department_id);
}

function getDepartmentByID($department_id)
{
    return \Config\Services::departments()->getByID($department_id);
}

function getDepartments($onlyPublic=true)
{
    return ($onlyPublic === true)
        ? \Config\Services::departments()->getPublic()
        : \Config\Services::departments()->getAll();
}

#Implementacion ABO HELPDESK
function count_child_departments($id_dto_padre)
{
    return \Config\Services::departments()->getCountNumChildDpts($id_dto_padre);
}

function getDepartmentsChild($onlyPublic, $id_dto_padre)
{
    return \Config\Services::departments()->getDepartmentsChild($onlyPublic, $id_dto_padre);
}

function parseCustomFieldsForm($customField)
{
    $customFields = new \App\Libraries\CustomFields();
    return $customFields->parseForm($customField);
}

#Buscar datos staff por EMAIL
function findStaffByEmail($email)
{
    return \Config\Services::staff()->findStaffByEmail($email);
}

#Buscar datos cliente por EMAIL
function findUserByEmail($email)
{
    return \Config\Services::client()->findClientByEmail($email);
}

#Update password Staff
function updatePasswordStaff($id, $email, $password)
{
    return \Config\Services::staff()->updateOnePasswordStaff($id, $email, $password);
}

#Update password User
function updatePasswordUser($id, $email, $password)
{
    return \Config\Services::client()->updateOnePasswordClient($id, $email, $password);
}

#Obtener nombres de departamentos, lista en formulario Vista Agente
function getNamesDepAdjuntosById($idChild)
{
    return \Config\Services::tickets()->getNamesDepAdjuntosById($idChild);
}

#Obtener los datos de parametro del sistema, sugun el cparmetro que se envia a consultar
function getParam($cparam)
{
    return \Config\Services::params()->getParam($cparam);
}

#Se retorna el valor texto del parametro del sistema. Donde se recibe el nombre del parametro para consultar.
function getParamText($param)
{
    $param = \Config\Services::params()->getParam($param);
    $value = '';
    if($param != null){
        $value = $param->param_text;
    }
    return $value;
}

#Se retorna el valor texto en array del parametro del sistema. Donde se recibe el nombre del parametro para consultar.
function getParamTextArray($param)
{
    $param = \Config\Services::params()->getParam($param);
    $value = array();

    if($param != null){
        $valueAux = explode(',', $param->param_text);
        if(is_array($valueAux)){
            $value = $valueAux; 
        }
    }
    return $value;
}

#Se retorna el valor numerico del parametro del sistema. Donde se recibe el nombre del parametro para consultar.
function getParamNumber($param)
{
    $param = \Config\Services::params()->getParam($param);
    $value = 0;
    if($param != null){
        $value = $param->param_number;
    }
    return (int)$value;
}

/**
 * Servicio para obtener la cantidad de respuestas del ticket
 * Y validar que el ticket para cerrar al menos tiene que tener una respuesta.
 */
function getCountRepliesTicket($ticked_id)
{
    return \Config\Services::tickets()->getCountReplies($ticked_id);
}

#Obtiene el ticket por ID
function getTicket ($ticket_id)
{
    return \Config\Services::tickets()->getTicket(['id' => $ticket_id]);
}
 
#Obtiene el mesange del ticket
function getMessages ($ticket_id)
{
    return \Config\Services::tickets()->getMessageById($ticket_id);
}

#Obtiene listado de los tipo de solicitud
function getSolicitudes ()
{
    return \Config\Services::settingsAbo()->getSolicitude();
}

#Obtiene correo del cliente (Solicitud)
function getEmailSolicitude ($ticket_id)
{
    return \Config\Services::tickets()->getEmailSolicitude($ticket_id);
}

#Obtiene el estatus del ticket
function getStatusTicketSolicitude ($ticket_id)
{
    return \Config\Services::tickets()->getStatusTicketSolicitude($ticket_id);
}

#Para configurar tickets por Departamento
function department_config($var)
{
    return \Config\Services::settingsDep()->configDep($var);
}

#Retorna la parametrización del departamento.
function getConfigDepartmentById($department_id, $source_parameter)
{
    return \Config\Services::settingsDep()->getConfigDepartment($department_id, $source_parameter);
}

#Funcion para obtener el nombre del estatus del ticket por su id
function getStatusName($id)
{   
    return \Config\Services::traceProcess()->getNameStatus($id);
}


/*
 * -----------------------------------------
 * Encode/Decode data
 * -----------------------------------------
 */
function str_encode($str)
{
    if($str == ''){
        return '';
    }else{
        $ascii = strrev(base64_encode(strrev($str)));
        $hex = '';
        for ($i = 0; $i < strlen($ascii); $i++) {
            $byte = strtoupper(dechex(ord($ascii[$i])));
            $byte = str_repeat('0', 2 - strlen($byte)).$byte;
            $hex.=$byte;
        }
        return $hex;
    }
}

function str_decode($str)
{
    if($str == ''){
        return '';
    }else{
        $hex = $str;
        $ascii='';
        $hex=str_replace(" ", "", $hex);
        for($i=0; $i<strlen($hex); $i=$i+2) {
            $ascii.=chr(hexdec(substr($hex, $i, 2)));
        }
        return strrev(base64_decode(strrev($ascii)));

    }
}

#Obtiene datos del prestamo por codigo de ticket
function getLoanPaymentByTicketId ($ticket_id)
{
    return \Config\Services::loanPayment()->getLoanPaymentByTicketId($ticket_id);
}