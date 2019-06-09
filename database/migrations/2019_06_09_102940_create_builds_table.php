<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use MagmaticLabs\Obsidian\Domain\Eloquent\Model;

class CreateBuildsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('builds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('package_id')->index();
            $table->string('ref', 40);
            $table->string('commit', 40)->nullable();
            $table->enum('status', ['pending', 'running', 'success', 'failure'])->default('pending');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('completion_time')->nullable();
            $table->dateTime(Model::CREATED_AT);
            $table->dateTime(Model::UPDATED_AT);

            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('builds');
    }
}
