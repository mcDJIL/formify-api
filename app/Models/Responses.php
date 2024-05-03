<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Responses extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id', 'user_id', 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
