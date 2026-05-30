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

            // Process the imported data using the use case
            $result = $importFileUseCase->execute($data);

            return $this->successResponse('File imported successfully!', $result);
        } catch (\InvalidArgumentException $th) {
            return $this->errorResponse('Invalid argument: ' . $th->getMessage(), 400);
        } catch (\Exception $th) {
            // Handle any other exceptions that may occur
            return $this->errorResponse('An error occurred while importing the file.', 500);
        }
    }
}
