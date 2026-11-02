<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * Database_final.sql hiện tại chỉ dùng created_at hoặc để DB tự xử lý thời gian.
     * Nếu bảng users của bạn có updated_at thì có thể đổi lại true.
     */
    public $timestamps = false;

    protected $fillable = [
        'user_name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function getNameAttribute(): ?string
    {
        return $this->user_name;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['user_name'] = $value;
    }

    public function priceAlerts()
    {
        return $this->hasMany(PriceAlert::class, 'user_id');
    }
}
