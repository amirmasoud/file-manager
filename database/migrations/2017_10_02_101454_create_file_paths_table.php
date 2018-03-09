<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilePathsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_paths', function (Blueprint $table) {
            $table->increments('id');
            $table->string('path');
            $table->boolean('anonymouse')->default(false);
            $table->boolean('authenticated_user')->default(false);
            $table->boolean('premium')->default(false);
            $table->boolean('vip')->default(false);
            $table->boolean('administrator')->default(false);
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
        Schema::dropIfExists('file_paths');
    }
}
