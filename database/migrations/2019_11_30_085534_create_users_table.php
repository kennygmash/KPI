<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('payroll_number')->unique();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_supervisor')->defalit(false);
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->unsignedBigInteger('job_group_id');
            $table->unsignedBigInteger('designation_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('campus_id');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('job_group_id')->references('id')->on('job_groups')
                ->onDelete('cascade');

            $table->foreign('designation_id')->references('id')->on('designations')
                ->onDelete('cascade');

            $table->foreign('department_id')->references('id')->on('departments')
                ->onDelete('cascade');

            $table->foreign('campus_id')->references('id')->on('campuses')
                ->onDelete('cascade');

            $table->foreign('supervisor_id')->references('id')->on('users')
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
        Schema::dropIfExists('users');
    }
}
