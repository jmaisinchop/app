<?php 

namespace App\Libraries;

use App\Models\SystemParamsModel;
use Config\Database;
use Config\Services;

class SystemParams {

	protected $systemparamsModel;

	function __construct() {

		$this->systemparamsModel = new SystemParamsModel ();

	}

	#Consulta el parametro del sistema para validar
	#@cparam: Codigo del parametro	
	public function getParam($cparam){
        $q = $this->systemparamsModel->where('cparam', $cparam)
        ->get(1);
        if($q->resultID->num_rows == 0){
            return null;
        }
        return $q->getRow();
	}

	public function getAllParams()
    {
        $q = $this->systemparamsModel->orderBy('cparam','asc')
            ->get();
        if($q->resultID->num_rows == 0){
            return null;
        }
        $r = $q->getResult();
        $q->freeResult();
        return $r;
    }


    public function getParamById($id)
    {
        if($param = $this->systemparamsModel->find($id)){
            return $param;
        }
        return null;
    }

    public function updateParam($data=array(), $id)
    {
        $this->systemparamsModel->protect(false);
        $this->systemparamsModel->update($id, $data);
        $this->systemparamsModel->protect(true);
    }

    public function createParam ($cparam, $type, $param_text=null, $param_number, $description)
    {
        $this->systemparamsModel->protect(false);
        $this->systemparamsModel->insert([
            'cparam' => $cparam,
            'type_param' => $type,
            'param_text' => $param_text,
            'param_number' => $param_number,
            'param_description' => $description
        ]);
        $this->systemparamsModel->protect(true);
    }

    public function deleteParam ($id)
    {
        $this->systemparamsModel->protect(false)
            ->delete($id);
        $this->systemparamsModel->protect(true);
    }

}

 ?>