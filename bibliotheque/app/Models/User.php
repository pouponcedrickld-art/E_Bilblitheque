<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relations
    public function references()
    {
        return $this->hasMany(Reference::class, 'uploaded_by');
    }

    public function depositRequests()
    {
        return $this->hasMany(DepositRequest::class, 'applicant_id');
    }

    public function assignedDepositRequests()
    {
        return $this->hasMany(DepositRequest::class, 'assigned_manager_id');
    }

    public function depositRequestReviews()
    {
        return $this->hasMany(DepositRequestReview::class, 'reviewer_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    public function views()
    {
        return $this->hasMany(View::class);
    }
}