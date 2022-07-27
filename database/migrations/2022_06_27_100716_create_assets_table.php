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
        Schema::create('assets', function (Blueprint $table) {
            $table->uuid()->unique();
            $table->uuid('product_uuid');
            $table->uuid('connector_uuid');
            $table->string('product_code');
            $table->string('filerobot_position')->nullable();
            $table->string('filerobot_position_old')->nullable();
            $table->string('filerobot_url_cdn');
            $table->string('filerobot_url_cdn_old')->nullable();
            $table->string('filerobot_url_public');
            $table->string('filerobot_url_public_old')->nullable();
            $table->integer('version')->default(0);
            $table->integer('akeneo_latest_version')->nullable();
            $table->string('akeneo_latest_attribute')->nullable();
            $table->string('akeneo_sync_status')->nullable();
            $table->boolean('have_mapping')->nullable();
            $table->string('new_version_action')->nullable();
            $table->text('last_sync_error')->nullable();
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
        Schema::dropIfExists('assets');
    }
};
