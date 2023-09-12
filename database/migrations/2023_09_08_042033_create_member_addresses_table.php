<?php

use App\Constants\AddressResidencyStatus;
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
        Schema::create('member_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Member::class, 'member_id');
            $table->string('house_block_lot')->nullable();
            $table->string('street')->nullable();
            $table->string('subdivision_village')->nullable();
            $table->string('barangay')->nullable();
            $table->string('city_municipality')->nullable();
            $table->string('province')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('residency_status')->nullable();
            $table->enum('type', AddressResidencyStatus::LIST);
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
        Schema::dropIfExists('member_addresses');
    }
};
