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
            $table->text('metadata')->nullable();
        });

        Schema::table('connectors', function (Blueprint $table) {
            // Will contains attributes of families
            $table->text('families')->nullable();
        });

        Schema::create('meta_mapping', function (Blueprint $table) {
            $table->uuid();
            $table->uuid('connector_uuid');
            $table->string('metadata');
            $table->string('akeneo_family');
            $table->string('akeneo_attribute');
            $table->boolean('is_locale')->default(false);
            $table->string('scope')->nullable();
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
        Schema::dropIfExists('meta_mapping');

        Schema::table('connectors', function (Blueprint $table) {
            $table->dropColumn('families');
        });
    }
};
