<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupervisorCommentsToTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->longtext('supervisor_comments')->after('evidence');
            $table->longtext('supervisee_comments')->after('supervisor_comments');
            $table->longtext('hr_comments')->after('supervisee_comments');
            $table->longtext('mid_remarks')->after('hr_comments');
            $table->longtext('end_remarks')->after('mid_remarks');

            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('targets', function (Blueprint $table) {
            //
        });
    }
}
