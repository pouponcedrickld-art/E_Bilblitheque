<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferenceKeyword extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_id',
        'keyword',
    ];

    // Référence associée à ce mot-clé
    public function reference()
    {
        return $this->belongsTo(Reference::class);
    }
}