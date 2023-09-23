<?php

use App\Constants\AccountMaintainingBalanceCycle;
use App\Constants\AccountMaintainingBalanceMethod;
use App\Constants\AccountType;
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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->enum('type', AccountType::LIST);
            
            $table->double('earn_interest_per_anum')->nullable();
            $table->double('maintaining_balance')->nullable();
            $table->enum('penalty_below_maintaining_method', AccountMaintainingBalanceMethod::LIST)->nullable();
            $table->double('penalty_below_maintaining')->nullable();
            
            $table->enum('penalty_below_maintaining_cycle', AccountMaintainingBalanceCycle::LIST)->nullable();
            $table->double('penalty_below_maintaining_duration')->nullable();
            
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
        Schema::dropIfExists('accounts');
    }
};
