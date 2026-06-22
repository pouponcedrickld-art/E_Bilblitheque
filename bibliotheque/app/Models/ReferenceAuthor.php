<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferenceAuthor extends Model
{
    use HasFactory;

    protected $table = 'reference_author';

    protected $fillable = [
        'reference_id',
        'author_id',
    ];

    public $timestamps = false;

    // Référence liée
    public function reference()
    {
        return $this->belongsTo(Reference::class);
    }

    // Auteur lié
    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}