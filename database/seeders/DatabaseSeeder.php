<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Constants\AccountType;
use App\Models\Account;
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

        LoanProduct::factory()->count(1)->create();
        LoanProduct::create(['name'=> 'Student Loan']);
        LoanProduct::create(['name'=> 'Motorcycle Loan']);

        WorkIndustry::create(['name'=> 'IT Software', 'key' => 'it-software']);
        WorkIndustry::create(['name'=> 'Manufacturing', 'key' => 'manufacturing']);

        Account::create(['name'=> 'Share Capital', 'type' => AccountType::SHARE_CAPITAL, 'key' => 'share-capital']);
        Account::create(['name'=> 'Regular Savings', 'type' => AccountType::SAVINGS, 'key' => 'regular-savings']);
        Account::create(['name'=> 'Kiddie Savings', 'type' => AccountType::SAVINGS, 'key' => 'kiddie-savings']);
    }
}
