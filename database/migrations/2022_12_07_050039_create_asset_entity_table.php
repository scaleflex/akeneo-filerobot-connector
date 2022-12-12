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
        Schema::create('asset_entity', function (Blueprint $table) {
            $table->uuid();
            $table->string('connector_uuid');
            $table->string('filerobot_uuid');
            $table->string('url_cdn');
            $table->string('url_public');
            $table->text('filename');
            $table->string('entity')->nullable();
            $table->string('entity_code')->nullable();
            $table->text('entity_label')->nullable();
            $table->string('entity_attribute')->nullable();
            $table->text('scope')->nullable();
            $table->text('locale')->nullable();
            $table->string('status')->default('not_sync');
            $table->text('message')->nullable();
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->text('filename')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_entity');
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('filename');
        });
    }
};
