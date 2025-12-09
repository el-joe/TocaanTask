<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory,SoftDeletes;

    // we need to add stocks table but its not into requirements now
    protected $fillable = [
        'name',
        'description',
        'price',
        'active'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
