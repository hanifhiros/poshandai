<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('payment', function (Blueprint $table) {
            $table->id('payment_id');
            $table->string('payment_method_name', 25)->nullable();
            $table->string('provider_name', 25)->nullable();
            $table->float('transaction_fee')->nullable();
            $table->string('account_number', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment');
    }
};
