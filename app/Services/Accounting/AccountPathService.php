<?php

namespace App\Services\Accounting;

use App\Models\Account;

class AccountPathService
{
    public function refresh(Account $account): Account
    {
        $path = $account->parent_id
            ? trim((Account::find($account->parent_id))?->path, '/') . '/' . $account->id . '/'
            : '/' . $account->id . '/';

        $account->path = $path;
        $account->saveQuietly();

        foreach ($account->children as $child) {
            $this->refresh($child);
        }

        return $account->fresh();
    }

    public function assignHierarchy(Account $account, ?Account $parent): void
    {
        if ($parent) {
            $account->parent_id = $parent->id;
            $account->level = $parent->level + 1;
            $account->report_root = $account->report_root ?: $parent->report_root;
            $account->type = $account->type ?: $parent->type;
        } else {
            $account->parent_id = null;
            $account->level = 0;
        }
    }
}
