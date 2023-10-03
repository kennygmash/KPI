<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

        protected $fillable = [
            'project_name',
            'project_no',
            'department',
            'project_manager',
            'activity',
            'notes',
            'due_date',
            'time_frame',
            'user_id',

        ];

    public function getFullProjectAttribute()
          {
              return "{$this->project_name} {$this->project_no}";
          }
}
