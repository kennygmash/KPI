<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TargetUpdate extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'evidence',
        'supervisor_comments',
        'mid_year_remarks',
        'target_id',
        'user_id',

    ];

    /**
     * Get the target that owns the update
     */
    public function target(){
        return $this->belongsTo(Target::class, 'target_id');
    }
}
