<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkPlanUpdate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'evidence',
        'supervisor_comments',
        'work_plan_id',
        'user_id',
    ];
}
