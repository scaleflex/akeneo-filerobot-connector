<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new  class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('connectors', function (Blueprint $table) {
            $table->uuid()->unique();
            $table->boolean('activation')->default(false);
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('filerobot_token')->nullable();
            $table->string('filerobot_key')->nullable();
            $table->string('akeneo_version')->nullable();
            $table->string('akeneo_server_url')->nullable();
            $table->string('akeneo_client_id')->nullable();
            $table->string('akeneo_secret')->nullable();
            $table->string('akeneo_username')->nullable();
            $table->string('akeneo_password')->nullable();
            // User relationship
            $table->integer('user_id')->unsigned();

            $table->string('email')->nullable();

            $table->string('akeneo_sync_status')->nullable();
            $table->string('akeneo_sync_last_message')->nullable();

            $table->string('filerobot_sync_status')->nullable();
            $table->string('filerobot_sync_last_message')->nullable();

            $table->string('sync_status')->nullable();
            $table->string('sync_last_message')->nullable();

            $table->string('setup_status')->nullable();
            $table->string('setup_message')->nullable();

            $table->boolean('lock_status')->nullable();

            $table->integer('products_count')->default(0);
            $table->integer('total_product')->default(0);
            $table->timestamps();
        });

        Schema::create('akeneo_families', function (Blueprint $table) {
            $table->uuid()->unique();
            $table->string('code');
            $table->text('label')->nullable();
            $table->uuid('connector_uuid');
            //Akeneo
            $table->text('attributes')->nullable();
            $table->string('attribute_as_label')->nullable();
            $table->string('attribute_as_image')->nullable();
            $table->text('attribute_requirements')->nullable();
            $table->timestamps();
        });

        Schema::create('mappings', function (Blueprint $table) {
            $table->uuid()->unique();
            $table->string('filerobot_position')->nullable();
            $table->string('akeneo_attribute')->nullable();
            $table->string('mapping_type');
            $table->string('akeneo_family');
            $table->string('update_default_behavior')->nullable();
            //Relationship
            $table->uuid('akeneo_family_uuid')->unsigned();
            $table->uuid('connector_uuid')->unsigned()->nullable();
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
        Schema::dropIfExists('mappings');
        Schema::dropIfExists('akeneo_families');
        Schema::dropIfExists('connectors');
    }
};
