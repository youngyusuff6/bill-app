<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id', 'balance'
    ];

    protected $hidden = ['id', 'created_at'];


    //SPECIFY RELATIONSHIPS
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
