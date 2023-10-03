<?php

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
        Schema::create('member_beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Member::class, 'member_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('relationship')->nullable();
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
        Schema::dropIfExists('member_beneficiaries');
    }
};
