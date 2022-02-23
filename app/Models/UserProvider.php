<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProvider extends Model
{
    use HasFactory;

    //todo const
    const PROVIDER_GITHUB = 'github';
    const PROVIDER_FB = 'facebook';

    protected $fillable = [
        'user_id',
        'provider_id',
        'provider_type',
        'access_token'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
