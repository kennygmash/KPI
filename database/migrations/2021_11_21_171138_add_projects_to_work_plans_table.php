<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjectsToWorkPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_plans', function (Blueprint $table) {
            $table->text('projects')->after('key_result');
            $table->text('activities')->after('projects');
            $table->text('department')->after('activities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_plans', function (Blueprint $table) {
            //
        });
    }
}
