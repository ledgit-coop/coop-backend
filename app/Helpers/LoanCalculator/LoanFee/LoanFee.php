<?php

namespace App\Helpers\LoanCalculator\LoanFee;

use App\Constants\LoanFeeType;
use Exception;

class LoanFee {
    
    public function __construct(
        public int $id,
        public string $name,
        public float $fee,
        public string $type,
        public string $method,
        public float $amount,
    )
    { }

    public function fee() {
        if($this->type == LoanFeeType::DEDUCT_PRINCIPAL)
            return (new ComputePrincipal(
                $this->fee,
                $this->method,
                $this->amount,
            ))->compute();

        throw new Exception("Type not supported.", 1);
    }
}