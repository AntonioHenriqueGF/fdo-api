<?php

namespace App\Modules\Categories\Models;

use Illuminate\Database\Eloquent\Model;

class RulesModel extends Model
{
    protected $table = 'rules';

    protected $primaryKey = 'rul_id';

    public $timestamps = false;

    protected $fillable = [
        'rul_user_id',
        'rul_category_id',
        'rul_pattern',
        'rul_priority',
    ];

    /**
     * Removes every rule associated with the given category ID for the specified user.
     */
    public static function deleteByCategoryId(int $userId, int $categoryId): void
    {
        self::where('rul_user_id', $userId)
            ->where('rul_category_id', $categoryId)
            ->delete();
    }
}
