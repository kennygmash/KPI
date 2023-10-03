<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'payroll_number',
        'job_group_id',
        'designation_id',
        'department_id',
        'campus_id',
        'is_supervisor',
        'supervisor_id'
    ];

    /**
     * The attributes that sholid be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that sholid be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the user job group
     * @return BelongsTo
     */
    public function jobGroup()
    {
        return $this->belongsTo(JobGroup::class, 'job_group_id');
    }

    /**
     * Get the user designation
     * @return BelongsTo
     */
    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');

    }

    /**
     * Get the user department
     * @return BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Get the user campus
     * @return BelongsTo
     */
    public function campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }

    /**
     * Get the user supervisor
     * @return BelongsTo
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Get the user work plans
     * @return HasMany
     */
    public function workPlans()
    {
        return $this->hasMany(WorkPlan::class);
    }
    public function assignments()
    {
        return $this->hasMany(Assignments::class);
    }

    /**
     * Get the staff the user supervises
     * @return HasMany
     */
    public function supervisees()
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    /**
     * Get the user file uploads
     * @return HasMany
     */
    public function targets()
    {
        return $this->hasMany(Target::class);
    }

    /**
     * Get the user file uploads
     * @return MorphMany
     */
    public function fileUploads()
    {
        return $this->morphMany(FileUpload::class, 'uploadable');
    }
}
