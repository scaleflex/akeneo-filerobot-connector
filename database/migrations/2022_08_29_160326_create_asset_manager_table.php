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
        Schema::create('asset_manager', function (Blueprint $table) {
            $table->uuid();
            $table->string('connector_uuid');
            $table->string('filerobot_uuid');
            $table->text('tags');
            $table->string('url_cdn');
            $table->string('url_public');
            $table->string('asset_family')->nullable();
            $table->string('asset_attribute')->nullable();
            $table->string('scope')->nullable();
            $table->string('locale')->nullable();
            $table->string('status')->default('not_sync');
            $table->text('message');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_manager');
    }
};
