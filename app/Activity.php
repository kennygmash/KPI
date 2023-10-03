<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use SoftDeletes;

        protected $fillable = [
            'activity_name',
            'activity_no',
            'projects',
            'activity',
            'notes',
            'start_date',
            'end_date',
            'user_id',

        ];

    
}