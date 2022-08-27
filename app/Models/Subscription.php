<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expired(): Attribute
    {
        return Attribute::make(
            fn () => $this->created_at->diffInDays(now()) > 30
        );
    }

    public function active(): Attribute
    {
        return Attribute::make(
            fn () => $this->created_at->diffInDays(now()) <= 30
        );
    }

    public function remainingDuration()
    {


        return $this->duration - $this->created_at->diffInDays(today());
    }
}
