<?php

namespace App\Helpers\LoanCalculator;

use Illuminate\Support\Carbon;

class LoanSchedule {

    public function __construct(
        public int $month,
        public Carbon $date,
        public float $principal,
        public float $interest,
        public float $total_payment,
        public float $loan_balance,
        public string $description,
    )
    { }

    public function toArray() {
        return [
            'month' => $this->month, 
            'date' => $this->date->format("Y-m-d"),
            'principal' => $this->principal,
            'interest' => $this->interest,
            'total_payment' => $this->total_payment,
            'loan_balance' => $this->loan_balance,
            'description' => $this->description,
        ];
    }
}