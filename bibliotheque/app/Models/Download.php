<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reference_id',
        'downloaded_at',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'downloaded_at' => 'datetime',
        ];
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->belongsTo(Reference::class);
    }
}