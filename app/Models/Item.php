<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function receipts()
    {
        return $this->belongsToMany(Receipt::class, 'receipt_item')->withTimestamps()->withPivot(['quantity', 'price']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
