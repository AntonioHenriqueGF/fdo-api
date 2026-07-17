<?php

namespace App\Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

// Model will serve as the eloquent class to interact with the database

class ImportsModel extends Model
{
  // Define your model properties and methods here
  protected $table = 'imports';

  protected $primaryKey = 'imp_id';

  protected $fillable = [
    'imp_user_id',
    'imp_file_name',
    'imp_file_hash',
    'imp_imported_at',
  ];

  protected $hidden = [
    'imp_file_hash',
  ];

  public $timestamps = false;

  /**
   * Verify if the combination of user ID and file hash is unique
   *
   * @param  int  $userId
   * @param  string  $binaryHash
   * @return bool
   */
  public function verifyUniqueImport($userId, $binaryHash)
  {
    // Implement logic to check if the combination of user ID and file hash is unique
    // For example, you can query the database to see if a record with the same user ID and file hash already exists
    return ! self::where('imp_user_id', $userId)->where('imp_file_hash', $binaryHash)->exists();
  }

  /**
   * Save the imported file information to the database
   *
   * @param  int  $userId
   * @param  string  $fileName
   * @param  string  $binaryHash
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

  /**
   * List all imports for a specific user along with their details
   *
   * @param  int  $userId
   * @return array An array of imports for the specified user, including their details.
   */
  public function listImportsForUser($userId)
  {
    // Implement logic to retrieve all imports for a specific user along with their details
    // For example, you can query the database to get all records from the imports table for the specified user ID
    return self::where('imp_user_id', $userId)->get()->toArray();
  }

  /**
   * Find an import by its ID and user ID, or throw a ModelNotFoundException if it doesn't exist.
   *
   * @param  int  $importId
   * @param  int  $userId
   * @return Model The import model instance.
   *
   * @throws ModelNotFoundException If the import with the specified ID and user ID does not exist.
   */
  public function firstOrFailByUser($importId, $userId)
  {
    return self::where('imp_user_id', $userId)->where('imp_id', $importId)->firstOrFail();
  }
}
