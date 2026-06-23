<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'description',
    ];

    public function references()
    {
        return $this->hasMany(Reference::class);
    }

    public function depositRequests()
    {
        return $this->hasMany(DepositRequest::class);
    }
}
