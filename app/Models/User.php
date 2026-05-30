<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'use_id';

    public $timestamps = true;

    const CREATED_AT = 'use_created_at';
    const UPDATED_AT = 'use_updated_at';

    protected $fillable = [
        'use_name',
        'use_email',
        'use_password',
    ];

    protected $hidden = [
        'use_password',
        'use_remember_token',
    ];

    /**
     * Campo usado para senha pelo sistema de autenticação
     */
    public function getAuthPassword()
    {
        return $this->use_password;
    }

    /**
     * Campo usado para "remember me"
     */
    public function getRememberTokenName()
    {
        return 'use_remember_token';
    }
}
