<?php

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
            $table->foreignIdFor(Member::class, 'member_id');
            $table->foreignIdFor(Account::class, 'account_id');
            $table->enum('status', ['active', 'dormant']);
            $table->integer('passbook_count')->unsigned()->default(1);
            $table->double('balance')->default(0);
            $table->double('interest_per_anum')->default(1);
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
