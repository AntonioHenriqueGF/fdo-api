<?php

namespace App\Modules\Users\Models;

use Illuminate\Database\Eloquent\Model;

class UsersModels extends Model
{
    // Define your user model properties and methods here
    protected $table = 'users'; // Specify the table name if it's not the plural of the model name

    protected $fillable = [
        'use_name',
        'use_email',
        'use_password',
        'use_remember_token',
        'use_created_at',
        'use_updated_at',
    ]; // Specify the fillable

    protected $hidden = [
        'use_password',
        'use_remember_token',
    ]; // Specify the hidden attributes

    public function findByEmail(string $email): ?self
    {
        return $this->where('use_email', $email)->first();
    }
}
