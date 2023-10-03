<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreColumnsToWorkPlans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_plans', function (Blueprint $table) {
            $table->string('key_result')->after('id');
            $table->string('strategic_objective')->after('key_reslit');
            $table->string('other_objectives')->after('strategic_objective');
            $table->string('assumptions')->after('resources_required');
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
                'key_reslit', 'strategic_objective', 'other_objectives', 'assumptions'
            ]);
        });
    }
}
