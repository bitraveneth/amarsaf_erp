<?php

namespace App\Services\Accounting;

use App\Models\Account;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class AccountResolver
{
    public function resolve(string $key): Account
    {
        $slug = config("accounting.accounts.{$key}") ?? $key;

        return Cache::remember("account.slug.{$slug}", 3600, function () use ($slug) {
            $account = Account::query()
                ->where('slug', $slug)
                ->where('is_active', true)
                ->first();

            if (! $account) {
                throw new InvalidArgumentException("Chart of accounts slug not found: {$slug}");
            }

            if (! $account->isPostable()) {
                throw new InvalidArgumentException("Account [{$slug}] is a group and cannot be posted to.");
            }

            return $account;
        });
    }

    public function id(string $key): int
    {
        return $this->resolve($key)->id;
    }

    public function name(string $key): string
    {
        return $this->resolve($key)->name;
    }

    public function line(string $key, float $debit = 0, float $credit = 0, ?string $description = null): array
    {
        $account = $this->resolve($key);

        return [
            'account_id' => $account->id,
            'debit' => $debit,
            'credit' => $credit,
            'description' => $description,
        ];
    }

    public function forget(?string $slug = null): void
    {
        if ($slug) {
            Cache::forget("account.slug.{$slug}");

            return;
        }

        foreach (array_values(config('accounting.accounts', [])) as $configuredSlug) {
            Cache::forget("account.slug.{$configuredSlug}");
        }
    }
}
