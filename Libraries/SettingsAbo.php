<?php
/**
 * @package AboHelpdesk
 * @author: Adrian Carchipulla
 * @Copyright (c) 2022
 * @link https://www.linkedin.com/in/adrian-carchipulla/
 */

namespace App\Libraries;

use App\Models\ClientSolicitudeModel;
use App\Models\SystemParamsModel;
use App\Models\TypeSolicitude;
use Config\Database;
use Config\Services;

class SettingsAbo 
{
    protected $solicitudeModel;

	public function __construct()
    {
        $this->solicitudeModel = new TypeSolicitude();
    }

    /*
     * -------------------------
     * Type Solicitudes
     * -------------------------
     */
	public function typeList()
    {
        return $type_solicitude = array(
            'label' => 'Label',
            'text' => 'Text field',
            'checkbox' => 'Checkbox',
            'select' => 'Drop-down select',
            'textarea' => 'Text area'
        );
    }

    public function newSolicitude ($data=array())
    {
        //$next_position = $solicitudeModel->countAll()+1;
        $this->solicitudeModel->protect(false)
            ->insert([
                'description' => strtoupper($data['description']),
                'solicitude_order' => $data['solicitude_order'],
                'type' => $data['type'],
                'color' => $this->getColorSolicitude($data['type']),
                'value' => $data['value'],
                'multiple_select' => $data['multiple_select'],
                'enabled' => $data['enabled']
            ]);
        $this->solicitudeModel->protect(true);
    }

    public function updateSolicitude ($data=array(), $id)
    {
        $this->solicitudeModel->protect(false)
        ->update($id, $data);
        $this->solicitudeModel->protect(true);
    }

    public function getSolicitudeAll()
    {
        $q = $this->solicitudeModel->orderBy('solicitude_order','asc')
            ->get();
        $r = $q->getResult();
        $q->freeResult();;
        return $r;
    }

    //Obtiene el listado de las solicitudes con estatus activo
    public function getSolicitude()
    {
        $q = $this->solicitudeModel->where('enabled', 1)
            ->orderBy('solicitude_order','asc')
            ->get();
        $r = $q->getResult();
        $q->freeResult();;
        return $r;
    }

    public function getSolicitudeById ($id)
    {
        $q = $this->solicitudeModel->select('*')
            ->where('id', $id)
            ->get(1);
        if($q->resultID->num_rows == 0){
            return null;
        }
        return $q->getRow();
    }

    private function getColorSolicitude($type)
    {
        $color = '';
        switch ($type){
            case 'label':
                return $color .= "#FFFFFF";
                break;
            case 'text':
                return $color .= "#007B9D";
                break;
            case 'checkbox':
                return $color .= "#868E96";
                break;
            case 'select':
                return $color .= "#868E96";
                break;
        }
    }
}
