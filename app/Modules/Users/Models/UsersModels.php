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

    public function verifyUserCredentials(string $email, string $password)
    {
        // Implement your logic to verify user credentials here
        // For example, you can use Laravel's built-in authentication features
        return $this->where('use_email', $email)->where('use_password', $password)->get()->toArray();
    }
}
