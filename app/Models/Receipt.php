<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    public function scopeFilter($query, array $filters)
    {
        $query->when(
            $filters['order_in'] ?? false,
            function ($q, $order_in) {
                $q->orderBy('id', $order_in);
            }
        );

        $query->when(
            $filters['customer_phone'] ?? false,
            function ($q, $customer_phone) {
                $q->where('customer_phone', 'like', '%' . $customer_phone . '%');
            }
        );

        $query->when(
            $filters['customer_name'] ?? false,
            function ($q, $customer_name) {
                $q->where('customer_name', 'like', '%' . $customer_name . '%');
            }
        );

        $query->when(
            $filters['date'] ?? false,
            function ($q, $date) {
                $q->whereDate('date', $date);
            }
        );

        $query->when($filters['code'] ?? false, function ($q, $code) {
            $id = static::codeToId($code);
            $q->where('id', $id);
        });
    }
}
