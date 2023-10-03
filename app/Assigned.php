<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assigned extends Model
{
    use SoftDeletes;

    protected $table = 'assigneds';

    protected $fillable = [
        'assignment',
        'description',
        'supervisee_id',
        'user_id',
        'from',
        'to',
        'status',
        'evidence',
        'supervisor_comments'
    ];

    public function fileUploads()
    {
        return $this->morphMany(FileUpload::class, 'uploadable');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supervisee()
    {
        return $this->belongsTo(User::class, 'supervisee_id');
    }
}
