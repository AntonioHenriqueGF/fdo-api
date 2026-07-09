<?php

namespace App\Modules\Imports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Imports\Http\Requests\ImportFileRequest;
use App\Modules\Imports\UseCases\ImportFileUseCase;

class ImportsController extends Controller
{
    public function import(ImportFileRequest $request, ImportFileUseCase $importFileUseCase)
    {
        try {
            $data = $request->validated();
            $result = $importFileUseCase->execute($data);

            return $this->successResponse('File import job dispatched successfully!', $result);
        } catch (\InvalidArgumentException $th) {
            return $this->errorResponse('Invalid argument: ' . $th->getMessage(), 400);
        } catch (\Exception $th) {
            return $this->errorResponse('An error occurred while dispatching file import.', $th->getMessage(), 500);
        }
    }
}
