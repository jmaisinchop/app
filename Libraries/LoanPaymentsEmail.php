<?php
/**
 * @package AboHelpdesk
 * @author: Adrian Carchipulla
 * @Copyright (c) 2025
 * @link https://www.linkedin.com/in/adrian-carchipulla/
 */

namespace App\Libraries;

use App\Models\LoanPaymentEmailModel;

class LoanPaymentsEmail {

    protected $loanPaymentModel;

	public function __construct()
    {
        $this->loanPaymentModel = new LoanPaymentEmailModel();
    }

    public function createLoanPaymentEmail ($ticket_id, $name, $email)
    {
        $this->loanPaymentModel->protect(false);
        $this->loanPaymentModel->insert([
            'ticket_id' => $ticket_id,
            'name' => $name,
            'email' => $email,
            'date' => time()
        ]);
        $this->loanPaymentModel->protect(true);
    }
}