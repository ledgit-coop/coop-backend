<?php

namespace Database\Factories;

use App\Constants\LoanDisbursementChannel;
use App\Constants\LoanDurationPeriod;
use App\Constants\LoanInterestMethod;
use App\Constants\LoanInterestPeriod;
use App\Constants\LoanInterestType;
use App\Constants\LoanRepaymentCycle;
use App\Models\LoanProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\=LoanProduct>
 */
class LoanProductFactory extends Factory
{
    protected $model = LoanProduct::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->randomElement(['Emergency Loan', 'Personal Loan', 'Salary Loan']),
            'default_principal_amount' => fake()->randomFloat(2, 1000, 10000),
            'min_principal_amount' => fake()->randomFloat(2, 100, 5000),
            'max_principal_amount' => fake()->randomFloat(2, 5000, 20000),
            'disbursed_channel' => fake()->randomElement(LoanDisbursementChannel::LIST),
            'interest_method' => fake()->randomElement(LoanInterestMethod::LIST),
            'interest_type' => fake()->randomElement(LoanInterestType::LIST),
            'loan_interest_period' => fake()->randomElement(LoanInterestPeriod::LIST),
            'default_loan_interest' => fake()->randomFloat(2, 1, 10),
            'loan_duration_period' => fake()->randomElement(LoanDurationPeriod::LIST),
            'default_duration_period' => fake()->randomFloat(2, 1, 36),
            'repayment_cycle' => fake()->randomElement(LoanRepaymentCycle::LIST),
            'default_number_of_repayments' => fake()->numberBetween(1, 12),
        ];
    }
}
