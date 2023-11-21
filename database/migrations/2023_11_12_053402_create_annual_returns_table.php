<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('annual_returns', function (Blueprint $table) {
            $table->id();
            $table->date('to_date');
            $table->date('from_date');

            $table->double('interest_income_on_loan');
            $table->double('service_fees');
            $table->double('membership_fees');
            $table->double('gross_surplus');
            $table->double('operating_expenses');
            $table->double('net_suprplus_allocation_distribution');

            $table->double('reserve_fund_percent');
            $table->double('reserve_fund');
            $table->double('educational_training_fund_percent');
            $table->double('educational_training_fund');
            $table->double('educational_training_fund_due_cetf');
            $table->double('educational_training_fund_due_etf');
            $table->double('optional_fund_percent');
            $table->double('optional_fund');

            $table->double('interest_on_share_capital');
            $table->double('patronage_refund');
            $table->double('net_surplus_allocated_distributed');

            $table->double('interest_on_share_capital_allocation_percent');
            $table->double('patronage_refund_allocation_percent');

            $table->double('interest_on_share_capital_rate_interest');
            $table->double('patronage_refund_rate_interest');

            $table->foreignIdFor(User::class, 'created_by');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('annual_returns');
    }
};
