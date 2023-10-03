<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FileUpload extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'filename',
        'path',
        'public_url',
        'user_id',
        'workplan_id',
    ];

    /**
     * Get the user who owns the file uploaded.
     */
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     *  Get the owning uploadable model.
     * @return MorphTo
     */
    public function uploadable()
    {
        return $this->morphTo();
    }
}
