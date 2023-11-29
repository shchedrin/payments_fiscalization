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
        Schema::table('payments', function ($table) {
            $table->string('fiscal_number')->nullable();
            $table->string('shift_fiscal_number')->nullable();
            $table->datetime('receipt_date')->nullable();
            $table->datetime('fn_number')->nullable();
            $table->string('kkt_registration_number')->nullable();
            $table->string('fiscal_attribute')->nullable();
            $table->string('fiscal_doc_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function ($table) {
            $table->dropColumn('fiscal_number');
            $table->dropColumn('shift_fiscal_number');
            $table->dropColumn('receipt_date');
            $table->dropColumn('fn_number');
            $table->dropColumn('kkt_registration_number');
            $table->dropColumn('fiscal_attribute');
            $table->dropColumn('fiscal_doc_number');
        });
    }
};
