<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\PaymentInstrument;
use App\Models\PaymentMethodCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PaymentInstrumentService
{
    public function __construct(
        protected AccountPathService $paths,
        protected AccountResolver $resolver
    ) {
    }

    /**
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(PaymentMethodCategory $category, array $data): PaymentInstrument
    {
        return DB::transaction(function () use ($category, $data) {
            $category->loadMissing('parentAccount');
            $parent = $category->parentAccount;
            if (! $parent || ! $parent->is_group) {
                throw new InvalidArgumentException('Payment category needs a chart-of-accounts group before accounts can be added.');
            }

            $slug = $this->uniqueSlug($data['slug'] ?? $data['name'], $category->kind);
            $accountId = $data['account_id'] ?? null;

            if (! $accountId) {
                $account = $this->createLedger($parent, $data['name'], $slug);
                $accountId = $account->id;
            }

            if (! empty($data['is_default'])) {
                $this->clearDefault($category->id);
            }

            return PaymentInstrument::create([
                'payment_method_category_id' => $category->id,
                'name' => $data['name'],
                'account_holder_name' => $data['account_holder_name'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'slug' => $slug,
                'account_number' => $data['account_number'] ?? null,
                'branch' => $data['branch'] ?? null,
                'routing' => $data['routing'] ?? null,
                'swift_code' => $data['swift_code'] ?? null,
                'iban' => $data['iban'] ?? null,
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'details' => $data['details'] ?? null,
                'account_id' => $accountId,
                'is_default' => (bool) ($data['is_default'] ?? false),
                'is_active' => (bool) ($data['is_active'] ?? true),
                'sort_order' => (int) ($category->instruments()->max('sort_order') ?? 0) + 10,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PaymentInstrument $instrument, array $data): PaymentInstrument
    {
        return DB::transaction(function () use ($instrument, $data) {
            if (! empty($data['is_default'])) {
                $this->clearDefault($instrument->payment_method_category_id, $instrument->id);
            }

            $fields = [
                'name',
                'account_holder_name',
                'bank_name',
                'account_number',
                'branch',
                'routing',
                'swift_code',
                'iban',
                'address',
                'phone',
                'details',
            ];

            $payload = [];
            foreach ($fields as $field) {
                if (array_key_exists($field, $data)) {
                    $payload[$field] = $data[$field];
                }
            }
            if (array_key_exists('is_default', $data)) {
                $payload['is_default'] = (bool) $data['is_default'];
            }
            if (array_key_exists('is_active', $data)) {
                $payload['is_active'] = (bool) $data['is_active'];
            }

            $instrument->fill($payload);
            $instrument->save();

            if ($instrument->account && $instrument->wasChanged('name')) {
                // Keep display name aligned; slug stays stable for posting history.
                $instrument->account->update(['name' => $instrument->name]);
                $this->resolver->forget($instrument->account->slug);
            }

            return $instrument->fresh(['account', 'category']);
        });
    }

    public function ensureMobileWalletsGroup(): Account
    {
        $existing = Account::query()
            ->where('is_group', true)
            ->where(function ($q) {
                $q->where('code', '1113')->orWhere('name', 'Mobile wallets');
            })
            ->first();

        if ($existing) {
            return $existing;
        }

        $cashAndBank = Account::query()
            ->where('is_group', true)
            ->where(function ($q) {
                $q->where('code', '1110')->orWhere('name', 'Cash & Bank');
            })
            ->first();

        if (! $cashAndBank) {
            throw new InvalidArgumentException('Cash & Bank group missing from chart of accounts.');
        }

        $group = Account::create([
            'code' => '1113',
            'name' => 'Mobile wallets',
            'type' => 'asset',
            'is_group' => true,
            'is_active' => true,
            'sort_order' => 30,
            'report_root' => $cashAndBank->report_root ?: 'assets',
        ]);
        $this->paths->assignHierarchy($group, $cashAndBank);
        $group->save();
        $this->paths->refresh($group);

        return $group->fresh();
    }

    public function findGroupByCodeOrName(string $code, string $name): ?Account
    {
        return Account::query()
            ->where('is_group', true)
            ->where(function ($q) use ($code, $name) {
                $q->where('code', $code)->orWhere('name', $name);
            })
            ->first();
    }

    protected function createLedger(Account $parent, string $name, string $slug): Account
    {
        $account = Account::create([
            'code' => $this->nextChildCode($parent),
            'name' => $name,
            'slug' => $slug,
            'type' => $parent->type ?: 'asset',
            'is_group' => false,
            'is_active' => true,
            'sort_order' => (int) ($parent->children()->max('sort_order') ?? 0) + 10,
            'report_root' => $parent->report_root ?: 'assets',
        ]);
        $this->paths->assignHierarchy($account, $parent);
        $account->save();
        $this->paths->refresh($account);
        $this->resolver->forget($slug);

        return $account->fresh();
    }

    protected function nextChildCode(Account $parent): string
    {
        $codes = $parent->children()->pluck('code')->filter();
        $numeric = $codes->filter(fn ($code) => ctype_digit((string) $code))->map(fn ($c) => (int) $c);

        if ($numeric->isNotEmpty()) {
            return (string) ($numeric->max() + 1);
        }

        $base = preg_replace('/\D+/', '', (string) $parent->code) ?: '1000';

        return $base.str_pad((string) ($parent->children()->count() + 1), 2, '0', STR_PAD_LEFT);
    }

    protected function uniqueSlug(string $source, string $kind): string
    {
        $base = Str::slug($source, '_');
        if ($base === '') {
            $base = $kind.'_account';
        }

        $slug = $base;
        $i = 2;
        while (
            PaymentInstrument::where('slug', $slug)->exists()
            || Account::where('slug', $slug)->exists()
        ) {
            $slug = $base.'_'.$i;
            $i++;
        }

        return $slug;
    }

    protected function clearDefault(int $categoryId, ?int $exceptId = null): void
    {
        PaymentInstrument::query()
            ->where('payment_method_category_id', $categoryId)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->update(['is_default' => false]);
    }
}
