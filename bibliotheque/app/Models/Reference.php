<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'abstract',
        'isbn',
        'publication_year',
        'language',
        'document_type',
        'category_id',
        'publisher_id',
        'uploaded_by',
        'cover_image',
        'file_path',
        'pages',
        'download_count',
        'view_count',
        'status',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'publication_year' => 'integer',
            'pages' => 'integer',
            'download_count' => 'integer',
            'view_count' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    // Catégorie de la référence
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Éditeur de la référence
    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    // Utilisateur ayant uploadé la référence
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Auteurs de la référence
    public function authors()
    {
        return $this->belongsToMany(Author::class, 'reference_author');
    }

    // Mots-clés associés
    public function keywords()
    {
        return $this->belongsToMany(Keyword::class, 'keyword_reference');
    }

    // Historique des téléchargements
    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    // Historique des vues
    public function views()
    {
        return $this->hasMany(View::class);
    }
}