<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Vérifications de rôle
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isResponsableRH(): bool
    {
        return in_array($this->role, ['admin', 'responsable_rh']);
    }

    public function isResponsableDemande(): bool
    {
        return in_array($this->role, ['admin', 'responsable_demande']);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // Relations
    public function references() { return $this->hasMany(Reference::class, 'uploaded_by'); }
    public function depositRequests() { return $this->hasMany(DepositRequest::class, 'applicant_id'); }
    public function assignedDepositRequests() { return $this->hasMany(DepositRequest::class, 'assigned_manager_id'); }
    public function notifications() { return $this->hasMany(Notification::class); }
    public function activityLogs() { return $this->hasMany(ActivityLog::class); }
    public function downloads() { return $this->hasMany(Download::class); }
    public function views() { return $this->hasMany(View::class); }
}
