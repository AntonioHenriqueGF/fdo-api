<?php

namespace App\Modules\Categories\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriesModel extends Model
{
    protected $table = 'categories';

    protected $primaryKey = 'cat_id';

    public $timestamps = false;

    protected $fillable = [
        'cat_user_id',
        'cat_description',
    ];
}
