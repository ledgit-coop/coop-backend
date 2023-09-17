<?php

use App\Models\LoanFeeTemplate;
use App\Models\LoanProduct;
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
        Schema::create('loan_product_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(LoanProduct::class, 'loan_product_id')->constrained()->onDelete('cascade');
            $table->foreignIdFor(LoanFeeTemplate::class, 'loan_fee_template_id')->constrained()->onDelete('restrict');
            $table->double('fee');
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
        Schema::dropIfExists('loan_product_fees');
    }
};
