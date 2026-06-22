<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'assigned_manager_id',
        'title',
        'description',
        'proposed_file',
        'status',
    ];

    // Utilisateur ayant soumis la demande
    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    // Manager RH assigné au traitement de la demande
    public function assignedManager()
    {
        return $this->belongsTo(User::class, 'assigned_manager_id');
    }

    // Tous les avis (review) liés à cette demande
    public function reviews()
    {
        return $this->hasMany(DepositRequestReview::class);
    }
}