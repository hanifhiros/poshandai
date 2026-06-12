<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketingMetricsTable extends Migration
{
    public function up()
    {
        Schema::create('marketing_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->index();
            $table->date('date');
            $table->decimal('revenue', 15, 2)->default(0);
            $table->integer('orders')->default(0);
            $table->integer('customers')->default(0);
            $table->decimal('aov', 10, 2)->default(0);
            $table->decimal('churn_rate', 5, 2)->default(0);
            $table->decimal('retention_rate', 5, 2)->default(0);
            $table->timestamps();
            $table->unique(['store_id','date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('marketing_metrics');
    }
}
