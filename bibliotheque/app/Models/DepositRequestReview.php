<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositRequestReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_request_id',
        'reviewer_id',
        'reviewer_role',
        'decision',
        'justification',
    ];

    // Demande de dépôt liée à cet avis
    public function depositRequest()
    {
        return $this->belongsTo(DepositRequest::class);
    }

    // Utilisateur (manager/admin) ayant donné son avis
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}