<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'phone',
        'address',
        'profile_image',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'deleted_at' => 'datetime',     // This allows for soft deletes if implemented
        ];
    }

     // Add this method for password reset
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    public function sendPasswordResetNotification($token)
    {
        // Pass both token and email to the view
        Mail::send('emails.password-reset', [
            'token' => $token,
            'email' => $this->email,  // ← This is key - passing email variable
            'name' => $this->name      // Optional
        ], function ($message) {
            $message->to($this->email)
                    ->subject('Reset Your Password');
        });
    }

    //relationship

     /**
     * Get the role of the user
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the rider profile (if user is a rider)
     */
    public function rider()
    {
        return $this->hasOne(Rider::class);
    }

    /**
     * Get parcels created by this user (for admin)
     */
    public function createdParcels()
    {
        return $this->hasMany(Parcel::class, 'created_by');
    }

    /**
     * Get status updates made by this user
     */
    public function statusHistories()
    {
        return $this->hasMany(ParcelStatusHistory::class, 'updated_by');
    }

    /**
     * Get notifications for this user
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role && $this->role->slug === 'admin';
    }

    /**
     * Check if user is rider
     */
    public function isRider()
    {
        return $this->role && $this->role->slug === 'rider';
    }

    /**
     * Check if user is active
     */
    public function isActive()
    {
        return $this->is_active;
    }


}
