<?php

use App\Constants\LoanPenaltyFrequency;
use App\Constants\LoanPenaltyMethod;
use App\Models\LoanSchedule;
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
        Schema::create('loan_schedule_penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(LoanSchedule::class, 'loan_schedule_id')->constrained()->onDelete('cascade');
            $table->double('penalty');
            $table->date('penalty_date');
            $table->enum('frequency', LoanPenaltyFrequency::LIST);
            $table->enum('method', LoanPenaltyMethod::LIST);
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
        Schema::dropIfExists('loan_schedule_penalties');
    }
};
