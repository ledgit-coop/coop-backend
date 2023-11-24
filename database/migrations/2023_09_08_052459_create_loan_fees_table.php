<?php

use App\Models\Loan;
use App\Models\LoanFeeTemplate;
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
        Schema::create('loan_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Loan::class, 'loan_id')->constrained()->onDelete('cascade');
            $table->foreignIdFor(LoanFeeTemplate::class, 'loan_fee_template_id')->constrained()->onDelete('restrict');
            $table->double('fee')->comment('Actual value from template');
            $table->double('amount')->default(0)->comment('Computed amount based on the fee value');
            $table->boolean('posted')->default(false);
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
        Schema::dropIfExists('loan_fees');
    }
};
