<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTargetUpdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('target_updates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('evidence')->nullable();
            $table->longText('supervisor_comments')->nullable();
            $table->unsignedBigInteger('target_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('target_id')->references('id')->on('targets')
                ->onDelete('cascade');

            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('target_updates');
    }
}
