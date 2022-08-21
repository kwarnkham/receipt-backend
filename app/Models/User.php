<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public function scopeFilter($query, $filters)
    {
        $query->when(
            $filters['role'] ?? false,
            fn ($q, $role) => $role == 'user' ? $q->doesntHave('roles') : $q->whereRelation('roles', 'name', $role)
        );
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
        return $this->belongsToMany(Payment::class, 'user_payment')->withPivot(['account_name', 'number'])->withTimestamps();
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
