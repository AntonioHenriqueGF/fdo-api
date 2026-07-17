<?php

namespace App\Modules\Imports\UseCases;

use App\Modules\Imports\Models\ImportsModel;
use Illuminate\Support\Facades\Auth;

class ListImportsUseCase
{
    public function __construct(
        private ImportsModel $importsModel
    ) {}

    /**
     * Execute the use case to list all imports for the authenticated user.
     *
     * @return array An array of imports for the authenticated user.
     */
    public function execute(): array
    {
        $user = Auth::user();
        $imports = $this->importsModel->listImportsForUser($user->use_id);

        return $imports;
    }
}
