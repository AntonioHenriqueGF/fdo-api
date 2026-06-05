<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

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

    public function getAuthPassword()
    {
        return $this->use_password;
    }

    public function getRememberTokenName()
    {
        return 'use_remember_token';
    }
}
