<?php

use App\Constants\MemberStatus;
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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_number')->unique();
            $table->text('profile_picture_url')->nullable();
            $table->string('surname');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('name_extension')->nullable();
            $table->enum('status', MemberStatus::LIST)->default('active');
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('date_hired')->nullable();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('employee_no')->nullable();
            $table->string('tin_no')->nullable();
            $table->string('email_address')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('telephone_number')->nullable();
            $table->boolean('oriented')->default(false);
            $table->date('member_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('members');
    }
};
