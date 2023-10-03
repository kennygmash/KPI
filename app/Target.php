<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Target extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'target',
        'performance_indicator',
        'self_appraisal',
        'actual_appraisal',
        'evidence',
        'projects',
        'activity',
        'department',
        'user_id'
    ];

    /**
     * Get the target file uploads
     * @return MorphMany
     */
    public function fileUploads()
    {
        return $this->morphMany(FileUpload::class, 'uploadable');
    }

    /**
     * Get the user who created the target
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the target updates
     */
    public function updates()
    {
      return $this->hasMany(TargetUpdate::class);
    }

    public function getFullActivityAttribute()
    {
       return "{$this->activity_name} {$this->activity_no}";
    }
}
