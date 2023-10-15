<?php

use App\Constants\LoanFeeMethod;
use App\Constants\LoanFeeType;
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
        Schema::create('loan_fee_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->double('fee');
            $table->enum('fee_type', LoanFeeType::LIST); // For enhancement: on-top-amotization, against-principal-installment
            $table->enum('fee_method', LoanFeeMethod::LIST);
            $table->boolean('enabled')->default(false);

            $table->boolean('credit_share_capital')->default(false);
            $table->boolean('credit_regular_savings')->default(false);
            $table->boolean('credit_revenue')->default(false);
            $table->boolean('show_to_report')->default(false);
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
        Schema::dropIfExists('loan_fee_templates');
    }
};
