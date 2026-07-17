<?php

namespace App\Modules\Imports\UseCases;

use App\Modules\Imports\Models\ImportsModel;
use App\Modules\Transactions\Models\DailyBalancesModel;
use App\Modules\Transactions\Models\TransactionsModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteImportUseCase
{
  public function __construct(
    private ImportsModel $importsModel,
    private DailyBalancesModel $dailyBalancesModel,
    private TransactionsModel $transactionsModel
  ) {}

  /**
   * Execute the use case to delete an import by its ID.
   *
   * @param  int  $importId  The ID of the import to be deleted.
   *
   * @throws ModelNotFoundException If the import with the specified ID does not exist.
   */
  public function execute(int $importId): void
  {
    $user = Auth::user();
    // Find the import by its ID and user ID or throw a ModelNotFoundException if it doesn't exist
    try {
      DB::transaction(function () use ($importId, $user): void {
        $import = $this->importsModel->firstOrFailByUser($importId, $user->use_id);

        // Delete child records first to satisfy FK constraints.
        $this->transactionsModel->deleteByImportId($importId, $user->use_id);
        $this->dailyBalancesModel->deleteBalancesByImportId($importId, $user->use_id);

        // Delete the import from the database.
        $import->delete();
      });
    } catch (ModelNotFoundException $e) {
      throw new ModelNotFoundException("Import with ID {$importId} not found for the authenticated user.");
    } catch (\Exception $e) {
      throw new \Exception("An error occurred while deleting the import with ID {$importId}: " . $e->getMessage());
    }
  }
}
