<?php

namespace App\Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

// Model will serve as the eloquent class to interact with the database

class ImportsModel extends Model
{
    // Define your model properties and methods here
    protected $table = 'imports';

    /**
     * Verify if the combination of user ID and file hash is unique
     * @param int $userId
     * @param string $binaryHash
     * @return bool
     */
    public function verifyUniqueImport($userId, $binaryHash)
    {
        // Implement logic to check if the combination of user ID and file hash is unique
        // For example, you can query the database to see if a record with the same user ID and file hash already exists
        return !self::where('imp_user_id', $userId)->where('imp_file_hash', $binaryHash)->exists();
    }

    /**
     * Save the imported file information to the database
     * @param int $userId
     * @param string $fileName
     * @param string $binaryHash
     * @return int The ID of the newly created import record.
     */
    public function saveImport($userId, $fileName, $binaryHash)
    {
        // Implement logic to save the imported file information to the database
        // For example, you can create a new record in the imports table with the provided user ID, file name, and file hash
        return DB::table('imports')->insertGetId([
            'imp_user_id' => $userId,
            'imp_file_name' => $fileName,
            'imp_file_hash' => $binaryHash,
        ]);
    }
}
