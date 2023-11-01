<?php

use App\Models\TransactionSubType;
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
        Schema::table('loan_products', function(Blueprint $table) {

            $table->unsignedBigInteger('disbursement_transaction_sub_type_id')->nullable();
            $table->unsignedBigInteger('principal_transaction_sub_type_id')->nullable();
            $table->unsignedBigInteger('interest_transaction_sub_type_id')->nullable();
            $table->unsignedBigInteger('penalty_transaction_sub_type_id')->nullable();

            $table->foreign('disbursement_transaction_sub_type_id')->references('id')
                ->on('transaction_sub_types')->constrained()->onDelete('restrict');
            $table->foreign('principal_transaction_sub_type_id')->references('id')
                ->on('transaction_sub_types')->constrained()->onDelete('restrict');
            $table->foreign('interest_transaction_sub_type_id')->references('id')
                ->on('transaction_sub_types')->constrained()->onDelete('restrict');
            $table->foreign('penalty_transaction_sub_type_id')->references('id')
                ->on('transaction_sub_types')->constrained()->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_fee_templates', function(Blueprint $table) {
            $table->dropColumn(
                'disbursement_transaction_sub_type_id',
                'principal_transaction_sub_type_id',
                'interest_transaction_sub_type_id',
                'penalty_transaction_sub_type_id',
            );
        });
    }
};
