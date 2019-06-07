<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use MagmaticLabs\Obsidian\Domain\Eloquent\Model;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('repository_id')->index();
            $table->string('name');
            $table->string('source');
            $table->string('ref');
            $table->string('schedule');
            $table->dateTime(Model::CREATED_AT);
            $table->dateTime(Model::UPDATED_AT);

            $table->foreign('repository_id')->references('id')->on('repositories')->onDelete('cascade');
            $table->unique(['repository_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packages');
    }
}
