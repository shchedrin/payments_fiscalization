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
            $table->string('account_id', 191);
            $table->decimal('amount', 12,2);
            $table->string('tender_source',191);
            $table->string('file_name',191);
            $table->date('pay_date_oracle');
            $table->date('create_date_oracle');
            $table->boolean('fiscal_flag')->default(false);
            $table->string('fiscal_status', 191)->nullable();
            $table->timestamp('created_at', $precision = 0)->useCurrent();
            $table->timestamp('updated_at', $precision = 0)->nullable()->useCurrentOnUpdate();
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
