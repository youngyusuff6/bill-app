<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'type', 'amount', 'description','status', 'metadata', 'transaction_id'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    //SPECIFY RELATIONSHIP
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
