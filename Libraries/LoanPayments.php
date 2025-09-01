<?php
/**
 * @package AboHelpdesk
 * @author: Adrian Carchipulla
 * @Copyright (c) 2025
 * @link https://www.linkedin.com/in/adrian-carchipulla/
 */

namespace App\Libraries;

use App\Models\LoanPaymentModel;

class LoanPayments {

    protected $loanPaymentModel;

	public function __construct()
    {
        $this->loanPaymentModel = new LoanPaymentModel();
    }

    public function getLoanPaymentByTicketId ($ticket_id)
    {
        $q = $this->loanPaymentModel->select('*')
            ->where('ticket_id', $ticket_id)
            ->get(1);
        if($q->resultID->num_rows == 0){
            return null;
        }
        return $q->getRow();
    }
}