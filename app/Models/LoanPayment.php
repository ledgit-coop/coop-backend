<?php

namespace App\Models;

class LoanPayment extends Loan
{
    protected $table = 'loans';
    
    protected $appends = ['outstanding'];
}
