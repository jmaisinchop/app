<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */

namespace App\Controllers\Staff;


use App\Controllers\BaseController;
use App\Libraries\Emails;
use Config\Services;

class TraceProcess extends BaseController
{
    public function emails()
    {
        $tickets = new \App\Libraries\TraceProcess();

        $pager = $tickets->getListEmailsClient();
        return view('staff/emails_client',[
            'emails_client' => $pager['result'],
            'pager' => $pager['pager'],
            'error_msg' => isset($error_msg) ? $error_msg : null
        ]);
    }

    public function allProcess()
    {
        $tickets = new \App\Libraries\TraceProcess();

        $pager = $tickets->getListValijas();
        return view('staff/trace_allprocess',[
            'valijas' => $pager['result'],
            'pager' => $pager['pager'],
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'list_process' => $tickets->getDepartmentProcess()
        ]);
    }

}



