<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Picture extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function url()
    {
        return config('filesystems')['disks']['s3']['url'] . "/" . config('app')['name'] . "/" . $this->name;
    }

    public function deleteFromCloud()
    {
        return Storage::disk('s3')->delete(config('app')['name'] . "/" . $this->name);
    }
}
