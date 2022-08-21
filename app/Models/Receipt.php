<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $with = ['items'];
    protected $appends = ['code'];

    public function items()
    {
        return $this->belongsToMany(Item::class, 'receipt_item')->withTimestamps()->withPivot(['quantity', 'price']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function code(): Attribute
    {
        return Attribute::make(
            get: fn () => "#LBO066X" . $this->id . "X18" . $this->id * 49,
        );
    }

    public static function codeToId($code)
    {
        return explode("X", $code)[1] ?? false;
    }

    public function scopeOf($query, User $user)
    {
        if (!$user->isAdmin()) $query->where('user_id', $user->id);
    }
}
