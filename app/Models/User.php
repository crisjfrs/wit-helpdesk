<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'email_verification_code',
        'email_verification_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_code',
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
            'email_verification_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get tickets created by this user
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    /**
     * Get tickets assigned to this user (for teknisi)
     */
    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    /**
     * Get messages sent by this user
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get notifications for this user
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get ticket activities by this user
     */
    public function ticketActivities()
    {
        return $this->hasMany(TicketActivity::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return strtolower(trim($this->role ?? '')) === 'admin';
    }

    /**
     * Check if user is teknisi
     */
    public function isTeknisi(): bool
    {
        return strtolower(trim($this->role ?? '')) === 'teknisi';
    }

    /**
     * Check if user is regular user
     */
    public function isUser(): bool
    {
        return strtolower(trim($this->role ?? '')) === 'user';
    }
}
