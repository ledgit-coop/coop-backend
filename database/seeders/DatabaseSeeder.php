<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Constants\AccountType;
use App\Constants\LoanFeeMethod;
use App\Constants\LoanFeeType;
use App\Models\Account;
use App\Models\LoanFeeTemplate;
use App\Models\LoanGuarantor;
use App\Models\LoanProduct;
use App\Models\User;
use App\Models\WorkIndustry;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        LoanProduct::create(['name'=> 'Salary Loan', 'locked' => true]);
        LoanProduct::create(['name'=> 'Negosyo Loan', 'locked' => true]);
        LoanProduct::create(['name'=> 'Student Loan', 'locked' => true]);
        LoanProduct::create(['name'=> 'Other Loan', 'locked' => true]);

        $industries = [
            ['name' => 'Agriculture', 'key' => 'agriculture'],
            ['name' => 'Automotive', 'key' => 'automotive'],
            ['name' => 'Construction', 'key' => 'construction'],
            ['name' => 'Education', 'key' => 'education'],
            ['name' => 'Energy', 'key' => 'energy'],
            ['name' => 'Entertainment', 'key' => 'entertainment'],
            ['name' => 'Finance', 'key' => 'finance'],
            ['name' => 'Food and Beverage', 'key' => 'food-and-beverage'],
            ['name' => 'Healthcare', 'key' => 'healthcare'],
            ['name' => 'Hospitality and Tourism', 'key' => 'hospitality-and-tourism'],
            ['name' => 'Information Technology (IT)', 'key' => 'information-technology'],
            ['name' => 'Manufacturing', 'key' => 'manufacturing'],
            ['name' => 'Real Estate', 'key' => 'real-estate'],
            ['name' => 'Retail', 'key' => 'retail'],
            ['name' => 'Transportation and Logistics', 'key' => 'transportation-and-logistics'],
            ['name' => 'Wholesale', 'key' => 'wholesale'],
            ['name' => 'Non-Profit', 'key' => 'non-profit'],
            ['name' => 'Government', 'key' => 'government'],
            ['name' => 'Professional Services', 'key' => 'professional-services'],
            ['name' => 'Other', 'key' => 'other'],
        ];
        
        LoanFeeTemplate::create([
            'name' => 'Processing Fee',
            'fee' => 1,
            'enabled' => true,
            'fee_type' => LoanFeeType::DEDUCT_PRINCIPAL,
            'fee_method' => LoanFeeMethod::PERCENTAGE,
        ]);

        LoanFeeTemplate::create([
            'name' => 'Share Capital',
            'fee' => 300,
            'enabled' => true,
            'fee_type' => LoanFeeType::DEDUCT_PRINCIPAL,
            'fee_method' => LoanFeeMethod::FIX_AMOUNT,
        ]);

        WorkIndustry::insert($industries);

        Account::create(['name'=> 'Share Capital', 'type' => AccountType::SHARE_CAPITAL, 'key' => 'share-capital', 'earn_interest_per_anum' => 1, 'maintaining_balance' => 1000]);
        Account::create(['name'=> 'Regular Savings', 'type' => AccountType::SAVINGS, 'key' => 'regular-savings', 'earn_interest_per_anum' => 1, 'maintaining_balance' => 1000]);
        Account::create(['name'=> 'Kiddie Savings', 'type' => AccountType::SAVINGS, 'key' => 'kiddie-savings', 'earn_interest_per_anum' => 1, 'maintaining_balance' => 1000]);

        Account::create(['name'=> 'Negosyo Loan', 'type' => AccountType::REGULAR, 'key' => 'negosyo-loan']);
        Account::create(['name'=> 'Salary Loan', 'type' => AccountType::REGULAR, 'key' => 'salary-loan']);
        Account::create(['name'=> 'Other Loan', 'type' => AccountType::REGULAR, 'key' => 'other-loan']);

        LoanGuarantor::create([
            'first_name' => 'Kevin Mokie',
            'last_name' => 'Koree',
        ]);

        LoanGuarantor::create([
            'first_name' => 'Kolen',
            'last_name' => 'Koree',
        ]);

        LoanGuarantor::create([
            'first_name' => 'Loque',
            'last_name' => 'Coins',
        ]);
    }
}
