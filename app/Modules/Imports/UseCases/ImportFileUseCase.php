<?php

namespace App\Modules\Imports\UseCases;

use App\Jobs\FileImportJob;
use Illuminate\Support\Facades\Auth;

class ImportFileUseCase
{
    public function __construct() {}

    /**
     * Execute the use case to process the imported file data.
     *
     * @param  array  $data  The validated data from the import file request.
     * @param  string  $data['fileName']  The name of the imported file.
     * @param  string  $data['fileHash']  The hash of the imported file.
     * @param  array  $data['normalized']  An array of normalized data, where each item contains 'amount', 'credit_only', 'debit_only', 'closing_balance', 'date_yyyymmdd', 'description', and 'date_ddmmyyyy'.
     * @return string A message indicating the result of the import process.
     */
    public function execute(array $data): string
    {
        $user = Auth::user();
        FileImportJob::dispatch($user, $data);

        return 'File import job dispatched successfully!';
    }
}
