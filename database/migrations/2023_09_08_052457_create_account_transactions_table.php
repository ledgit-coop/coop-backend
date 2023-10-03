<?php

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
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(MemberAccount::class, 'member_account_id')->constrained()->onDelete('cascade');
            $table->string('transaction_number')->unique();
            $table->string('particular');
            $table->double('amount');
            $table->date('transaction_date');
            $table->double('remaining_balance')->default(0);
            $table->softDeletes();
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
        Schema::dropIfExists('account_transactions');
    }
};
