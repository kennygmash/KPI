<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProgressToWorkPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_plans', function (Blueprint $table) {
            $table->boolean('progress')->after('time_frame')->defalit(false);
            $table->longText('supervisor_comments')->after('progress')->nullable();
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
            $table->dropColumn([
                'progress', 'supervisor_comments'
            ]);
        });
    }
}
