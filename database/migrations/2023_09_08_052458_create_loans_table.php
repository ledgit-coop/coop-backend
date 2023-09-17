<?php

use App\Constants\LoanDisbursementChannel;
use App\Constants\LoanDurationPeriod;
use App\Constants\LoanInterestMethod;
use App\Constants\LoanInterestPeriod;
use App\Constants\LoanInterestType;
use App\Constants\LoanRepaymentCycle;
use App\Constants\MemberLoanStatus;
use App\Models\LoanGuarantor;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\MemberAccount;
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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Member::class, 'member_id')->constrained()->onDelete('cascade');
            $table->foreignIdFor(LoanProduct::class, 'loan_product_id')->constrained()->onDelete('cascade');
            $table->foreignIdFor(MemberAccount::class, 'member_account_id')->constrained()->onDelete('cascade');
            $table->foreignIdFor(LoanGuarantor::class, 'guarantor_first_id');
            $table->foreignIdFor(LoanGuarantor::class, 'guarantor_second_id');

            $table->string('loan_number')->unique();
            $table->string('email')->nullable();
            $table->enum('status', MemberLoanStatus::LIST)->default(MemberLoanStatus::PENDING);
            $table->string('contact_number')->nullable();
            $table->integer('age')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('present_address')->nullable();
            $table->string('home_address')->nullable();
            $table->string('valid_id')->nullable();
            $table->string('tin_number')->nullable();
            $table->integer('number_of_children')->nullable();
            $table->string('application_type')->nullable();
            $table->string('employer_name')->nullable();
            $table->string('occupation')->nullable();
            $table->string('work_address')->nullable();
            $table->string('work_industry')->nullable();
            $table->string('loan_purpose')->nullable();
            $table->string('salary_range')->nullable();
            
            $table->decimal('applied_amount', 10, 2)->comment('Orignal loan applied')->nullable();
 
            $table->decimal('principal_amount', 10, 2)->comment('Loan approved')->nullable();
            $table->enum('disbursed_channel', LoanDisbursementChannel::LIST)->nullable();
            $table->enum('interest_method',LoanInterestMethod::LIST)->nullable();
            $table->enum('interest_type', LoanInterestType::LIST)->nullable();
            $table->decimal('loan_interest', 5, 2)->nullable();
            $table->enum('loan_interest_period', LoanInterestPeriod::LIST)->nullable();
            $table->integer('loan_duration')->nullable();
            $table->enum('loan_duration_type', LoanDurationPeriod::LIST)->nullable();
            $table->enum('repayment_cycle', LoanRepaymentCycle::LIST)->nullable();
            $table->integer('number_of_repayments')->nullable();
            $table->string('repayment_mode')->nullable();

            $table->date('applied_date')->nullable();
            $table->date('approved_date')->nullable();
            $table->date('released_date')->nullable()->comment('Needed to have a value for loan computation');
            $table->boolean('released')->default(false)->comment('Will be updated after actual released to the loaner');

            $table->decimal('released_amount', 10, 2)->comment('Net principal deducted with fees')->nullable();
            $table->decimal('interest_amount', 10, 2)->comment('Total interest computed in amortization')->nullable();
            $table->decimal('due_amount', 10, 2)->comment('Principal plus interest')->nullable();

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
        Schema::dropIfExists('loans');
    }
};
