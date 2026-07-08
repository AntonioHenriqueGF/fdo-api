<?php

namespace App\Modules\Categories\Services;

use App\Models\User;
use App\Modules\Categories\Models\RulesModel;
use App\Modules\Transactions\Models\TransactionsModel;

class ReprocessRulesService
{
    public function reprocessRulesForUser(string $userId)
    {
        // Logic to reprocess rules for the given user ID
        // Will be executed by the job dispatched in the usecase
        // This class will take a hybrid aproach, where the rule matching will be done using both queries and php logic, to ensure that the rules are applied correctly and efficiently.
        $rulesModel = RulesModel::where('rul_user_id', $userId)->orderByDesc('rul_priority')->get();

        TransactionsModel::where('tra_user_id', $userId)->chunkById(1000, function ($transactions) use ($rulesModel) {
            foreach ($transactions as $transaction) {
                foreach ($rulesModel as $rule) {
                    if (preg_match($rule->rul_pattern, $transaction->tra_description)) {
                        // Apply the rule to the transaction
                        // For example, you might want to update a field in the transaction or log the match
                        $transaction->update(['tra_matched_rule_id' => $rule->rul_id, 'tra_category_id' => $rule->rul_category_id]);
                        break;
                    }
                }
            }
        }, 'tra_id');
    }
}
