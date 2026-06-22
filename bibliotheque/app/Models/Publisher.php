<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'country',
        'website',
    ];

    // Toutes les références publiées par cet éditeur
    public function references()
    {
        return $this->hasMany(Reference::class);
    }
}