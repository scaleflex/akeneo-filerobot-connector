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
        Schema::table('asset_manager', function (Blueprint $table) {
            $table->text('scope')->change();
            $table->text('locale')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asset_manager', function (Blueprint $table) {
            $table->string('scope')->change();
            $table->string('locale')->change();
        });
    }
};
