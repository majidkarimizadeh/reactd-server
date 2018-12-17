<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchemaActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schema_actions', function (Blueprint $table) 
        {
            $table->increments('id');
            $table->integer('schema_id');
            $table->integer('perm');
            $table->integer('role_id')->unsigned();
            $table->text('condition')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('schema_actions');
    }
}
