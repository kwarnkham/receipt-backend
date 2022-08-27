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
            fn () => $this->remainingDuration() < 0
        );
    }

    public function active(): Attribute
    {
        return Attribute::make(
            fn () => $this->remainingDuration() >= 0
        );
    }

    public function remainingDuration()
    {
        return $this->duration - $this->created_at->startOfDay()->diffInDays(today());
    }
}
