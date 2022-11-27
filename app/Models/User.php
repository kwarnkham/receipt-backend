<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $with = ['pictures', 'roles', 'payments', 'latestSubscription', 'setting', 'phones'];
    public function scopeFilter($query, $filters)
    {
        $query->when(
            $filters['role'] ?? false,
            fn ($q, $role) => $role == 'user' ? $q->doesntHave('roles') : $q->whereRelation('roles', 'name', $role)
        );

        $query->when(
            $filters['name'] ?? false,
            fn ($q, $name) => $q->where('name', 'like', '%' . $name . '%')
        );

        $query->when(
            $filters['mobile'] ?? false,
            fn ($q, $mobile) => $q->where('mobile', 'like', '%' . $mobile . '%')
        );
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function phones()
    {
        return $this->hasMany(Phone::class);
    }

    public function setting()
    {
        return $this->hasOne(Setting::class);
    }

    public function latestSubscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role')->withTimestamps();
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'user_payment')->withPivot(['account_name', 'number', 'id'])->withTimestamps();
    }

    public function pictures()
    {
        return $this->hasMany(Picture::class);
    }

    public function isAdmin()
    {
        return $this->roles->contains(function ($role) {
            return $role->name == 'admin';
        });
    }

    public static function revokeAccessTokensOfExpiredSubscriptions()
    {
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $subscription = DB::table('subscriptions')->where('user_id', $user->id)->orderByDesc('id')->first();
            if ($subscription) {
                $start = new Carbon($subscription->created_at);
                if (today()->diffInDays($start->startOfDay()) > $subscription->duration) {
                    DB::table('personal_access_tokens')->where('tokenable_id', $subscription->user_id)->delete();
                }
            }
        }
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];
}
