<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class WorkPlan extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'activity',
        'expected_output',
        'performance_indicator',
        'resources_required',
        'due_date',
        'time_frame',
        'user_id',
        'supervisee_id',
        'progress',
        'supervisor_comments',
        'key_result',
        'strategic_objective',
        'other_objectives',
        'assumptions',
        'activities',
        'projects',
        'department',
    ];

    /**
     * Get the user who owns the work plan
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the updates done on a workplan
     */
    public function updates()
    {
        return $this->hasMany(WorkPlanUpdate::class);
    }

    /**
     * Get the most recent update performed on a file
     */
    public function latestUpdate()
    {
        return $this->hasMany(WorkPlanUpdate::class)->take(1);
    }

    /**
     * Get the workplan file uploads
     * @return MorphMany
     */
    public function fileUploads()
    {
        return $this->morphMany(FileUpload::class, 'uploadable');
    }

    public function supervisee()
    {
        return $this->belongsTo(User::class, 'supervisee_id');
    }
}
