<?php 
/**
 * @author: Adrian Carchipulla
 * @Copyright (c) 2021, ABOHELPDESK
 * @link https://www.linkedin.com/in/adrian-carchipulla/
 */
namespace App\Libraries;

use App\Models\SettingsDepartmentModel;
use Config\Database;
use Config\Services;

class SettingsDepartment 
{
	protected $vars;

    protected $settingsdepartmentModel;

    function __construct() {

        $this->settingsdepartmentModel = new SettingsDepartmentModel ();

    }

	public function configDep ($var) 
	{
		if(!$this->vars)
        {
            $db = Database::connect();
            $builder = $db->table('config_department');
            $this->vars = $builder->get()->getRow();
        }
        return (isset($this->vars->$var) ? $this->vars->$var : '');
	}


    #Retorna las parametrizaciones por departamento.
    public function getConfigDepartment($department_id, $source_parameter)
    {
        $q = $this->settingsdepartmentModel->where('department_id', $department_id)
        ->where('source_parameter', $source_parameter)
        ->get(1);
        if($q->resultID->num_rows == 0){
            return null;
        }
        return $q->getRow();
    }

    public function getList()
    {
        $q = $this->settingsdepartmentModel->orderBy('id','asc')
            ->get();
        $r = $q->getResult();
        $q->freeResult();;
        return $r;
    }

    public function getConfigById($id)
    {
        if($param = $this->settingsdepartmentModel->find($id)){
            return $param;
        }
        return null;
    }

    public function updateConfigDepTicket($data=array(), $id)
    {
        $this->settingsdepartmentModel->protect(false);
        $this->settingsdepartmentModel->update($id, $data);
        $this->settingsdepartmentModel->protect(true);
    }

    public function createConfigDepTicket ($enabled, $param, $department_id, $number_attachment, $size_attachment, $type_attachment)
    {
        $this->settingsdepartmentModel->protect(false);
        $this->settingsdepartmentModel->insert([
            'ticket_attachment' => $enabled,
            'source_parameter' => $param,
            'department_id' => $department_id,
            'ticket_attachment_number' => $number_attachment,
            'ticket_file_size' => $size_attachment,
            'ticket_file_type' => $type_attachment
        ]);
        $this->settingsdepartmentModel->protect(true);
    }

    public function deleteConfigDepTicket ($id)
    {
        $this->settingsdepartmentModel->protect(false)
            ->delete($id);
        $this->settingsdepartmentModel->protect(true);
    }
}


