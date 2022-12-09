<?php

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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('pay_event_id', 191);
            $table->string('uid', 191);
            $table->string('account', 191);
            $table->decimal('amount', 12,2);
            $table->string('tender_source',191);
            $table->string('tender_source_descr',191);
            $table->string('filen_name',191);
            $table->date('pay_date');
            $table->boolean('fiscal_flag');
            $table->string('fiscal_status', 191);
            $table->timestamp('created_at', $precision = 0);
            $table->timestamp('updated_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
