<?php

use App\Models\Loan;
use App\Models\Transaction;
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
        Schema::create('loan_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Loan::class, 'loan_id')->constrained()->onDelete('cascade');
            $table->date('due_date');
            $table->double('principal_amount');
            $table->double('interest_amount');
            $table->double('fee_amount');
            $table->double('penalty_amount');
            $table->double('due_amount');
            $table->double('principal_balance');
            $table->boolean('is_maturity')->default(false);
            $table->boolean('paid')->default(false);
            $table->boolean('overdue')->default(false);
            $table->double('amount_paid')->nullable()->comment('Must equal to (penalty_amount + due_amount) to flag paid');
            $table->foreignIdFor(Transaction::class, 'transaction_id')->nullable()->constrained()->onDelete('restrict');

            $table->boolean('overdue')->default(false);
            $table->string('payment_channel')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('payment_remarks')->nullable();

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
        Schema::dropIfExists('loan_schedules');
    }
};
