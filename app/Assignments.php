<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignments extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'assignment',
        'description',
        'type',
        'assigned_by',
        'from',
        'to',
        'progress',
        'evidence',
        'supervisor_comments',
        'user_id',

    ];

    /**
     * Get the workplan file uploads
     * @return MorphMany
     */
    public function fileUploads()
    {
        return $this->morphMany(FileUpload::class, 'uploadable');
    }
}
