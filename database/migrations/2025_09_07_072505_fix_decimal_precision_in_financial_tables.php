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
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('cash', 15, 2)->change();
            $table->decimal('change', 15, 2)->change(); // Laravel handle escaping otomatis
            $table->decimal('discount', 15, 2)->change();
            $table->decimal('grand_total', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->bigInteger('cash')->change();
            $table->bigInteger('change')->change();
            $table->bigInteger('discount')->change();
            $table->bigInteger('grand_total')->change();
        });
    }
};
