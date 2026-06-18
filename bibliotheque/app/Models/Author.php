<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'biography',
        'nationality',
        'birth_date',
        'death_date',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'death_date' => 'date',
        ];
    }

    // Relations
    public function references()
    {
        return $this->belongsToMany(Reference::class, 'reference_author');
    }
}