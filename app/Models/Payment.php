<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_payment')->withPivot(['account_name', 'number'])->withTimestamps();
    }
}
