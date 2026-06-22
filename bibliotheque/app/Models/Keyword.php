<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Keyword extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static function booted(): void
    {
        static::creating(function (Keyword $keyword) {
            if (!$keyword->slug) {
                $keyword->slug = Str::slug($keyword->name);
            }
        });
    }

    public function references()
    {
        return $this->belongsToMany(Reference::class, 'keyword_reference');
    }
}
