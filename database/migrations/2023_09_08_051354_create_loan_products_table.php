<?php

use App\Constants\LoanDisbursementChannel;
use App\Constants\LoanDurationPeriod;
use App\Constants\LoanInterestMethod;
use App\Constants\LoanInterestPeriod;
use App\Constants\LoanInterestType;
use App\Constants\LoanRepaymentCycle;
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
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            
            $table->decimal('default_principal_amount', 10, 2)->nullable();
            $table->decimal('min_principal_amount', 10, 2)->nullable();
            $table->decimal('max_principal_amount', 10, 2)->nullable();
            
            $table->enum('disbursed_channel', LoanDisbursementChannel::LIST)->nullable();
            
            $table->enum('interest_method',LoanInterestMethod::LIST)->nullable();
            $table->enum('interest_type', LoanInterestType::LIST)->nullable();

            $table->enum('loan_interest_period', LoanInterestPeriod::LIST)->nullable();
            $table->decimal('default_loan_interest', 5, 2)->nullable();

            $table->enum('loan_duration_type', LoanDurationPeriod::LIST)->nullable();
            $table->decimal('default_loan_duration', 10, 2)->nullable();

            $table->enum('repayment_cycle', LoanRepaymentCycle::LIST)->nullable();
            $table->integer('default_number_of_repayments')->nullable();
            $table->string('repayment_mode')->nullable();

            $table->json('fees')->nullable();

            $table->boolean('locked')->default(false)->comment('Locked if in used');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_products');
    }
};
