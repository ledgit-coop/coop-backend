<?php

namespace App\Helpers\LoanCalculator\LoanFee;

use App\Constants\LoanFeeMethod;
use App\Constants\LoanFeeType;

class ComputePrincipal {
    
    public function __construct(
        protected float $fee,
        protected string $method,
        protected float $principal_amount,
    )
    { }

    public function compute() {

        $fee = 0;

        switch ($this->method) {
            case LoanFeeMethod::FIX_AMOUNT:
                $fee = $this->fee;
                break;
            case LoanFeeMethod::PERCENTAGE:
                $fee = ($this->principal_amount * ($this->fee / 100));
                break;
        }

        return $fee;
    }
}