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

    // Relations
    public function reference()
    {
        return $this->belongsTo(Reference::class);
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}