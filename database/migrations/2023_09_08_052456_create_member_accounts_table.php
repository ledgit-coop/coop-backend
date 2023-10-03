<?php

use App\Constants\AccountStatus;
use App\Models\Account;
use App\Models\Member;
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
        Schema::create('member_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->unique();
            $table->string('account_holder');
            $table->foreignIdFor(Member::class, 'member_id')->constrained()->onDelete('cascade');
            $table->foreignIdFor(Account::class, 'account_id')->constrained()->onDelete('restrict');
            $table->enum('status', AccountStatus::LIST);
            $table->integer('passbook_count')->unsigned()->default(1);
            $table->double('balance')->default(0);
            
            $table->double('earn_interest_per_anum')->nullable();
            $table->double('maintaining_balance')->nullable();
            $table->enum('penalty_below_maintaining_method', ['fixed', 'percentage'])->nullable();
            $table->double('penalty_below_maintaining')->nullable();

            $table->enum('penalty_below_maintaining_cycle', ['day', 'month', 'quarter', 'year'])->nullable();
            $table->double('penalty_below_maintaining_duration')->nullable();

            $table->boolean('below_maintaining_balance')->default(false);
            
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
        Schema::dropIfExists('member_accounts');
    }
};
