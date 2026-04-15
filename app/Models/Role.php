<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Get all users with this role
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if role is Admin
     */
    public function isAdmin()
    {
        return $this->slug === 'admin';
    }

    /**
     * Check if role is Rider
     */
    public function isRider()
    {
        return $this->slug === 'rider';
    }
}
