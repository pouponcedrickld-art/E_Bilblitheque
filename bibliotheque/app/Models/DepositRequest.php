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
        'subtitle',
        'abstract',
        'description',
        'isbn',
        'publication_year',
        'language',
        'document_type_id',
        'category_id',
        'publisher_id',
        'pages',
        'proposed_file',
        'cover_image',
        'status',
        'allow_download',
    ];

    protected function casts(): array
    {
        return [
            'allow_download' => 'boolean',
        ];
    }

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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }
}