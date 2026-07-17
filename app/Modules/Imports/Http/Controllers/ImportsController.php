<?php

namespace App\Modules\Imports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Imports\Http\Requests\ImportFileRequest;
use App\Modules\Imports\UseCases\DeleteImportUseCase;
use App\Modules\Imports\UseCases\ImportFileUseCase;
use App\Modules\Imports\UseCases\ListImportsUseCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ImportsController extends Controller
{
    public function import(ImportFileRequest $request, ImportFileUseCase $importFileUseCase)
    {
        try {
            $data = $request->validated();
            $result = $importFileUseCase->execute($data);

            return $this->successResponse('File import job dispatched successfully!', $result);
        } catch (\InvalidArgumentException $th) {
            return $this->errorResponse('Invalid argument: '.$th->getMessage(), 400);
        } catch (\Exception $th) {
            return $this->errorResponse('An error occurred while dispatching file import.', $th->getMessage(), 500);
        }
    }

    public function listImports(ListImportsUseCase $listImportsUseCase)
    {
        try {
            $result = $listImportsUseCase->execute();

            return $this->successResponse('List of imports retrieved successfully!', $result);
        } catch (\Exception $th) {
            return $this->errorResponse('An error occurred while retrieving the list of imports.', $th->getMessage(), 500);
        }
    }

    public function deleteImport(int $importId, DeleteImportUseCase $deleteImportUseCase)
    {
        try {
            $deleteImportUseCase->execute($importId);

            return $this->successResponse('Import deleted successfully!', ['import_id' => $importId]);
        } catch (ModelNotFoundException $th) {
            return $this->errorResponse('Import not found.', 404);
        } catch (\Exception $th) {
            return $this->errorResponse('An error occurred while deleting the import.', $th->getMessage(), 500);
        }
    }
}
