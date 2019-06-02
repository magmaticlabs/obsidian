<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use MagmaticLabs\Obsidian\Domain\Eloquent\Model;

class CreateOrganizationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description');
            $table->dateTime(Model::CREATED_AT);
            $table->dateTime(Model::UPDATED_AT);
        });

        Schema::create('organization_memberships', function (Blueprint $table) {
            $table->string('organization_id')->index();
            $table->string('user_id')->index();
            $table->boolean('owner')->default(false);

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['organization_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organization_memberships');
        Schema::dropIfExists('organizations');
    }
}
