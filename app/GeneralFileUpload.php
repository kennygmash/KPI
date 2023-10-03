<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class GeneralFileUpload extends Model
{
    protected $fillable = [
        'user_id'
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
