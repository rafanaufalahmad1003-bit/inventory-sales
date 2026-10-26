<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Sesuai ketentuan: Owner dan Admin memiliki wewenang sama (Full Access)
     */
    public function isAdminOrOwner(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isPelanggan(): bool
    {
        return $this->role === 'pelanggan';
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }
}
