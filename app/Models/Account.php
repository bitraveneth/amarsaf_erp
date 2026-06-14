<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Account extends Model
{
    use HasFactory;

    public const REPORT_ROOTS = [
        'assets',
        'liabilities',
        'equity',
        'revenue',
        'manufacturing',
        'operatingexpense',
    ];

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'slug',
        'type',
        'is_group',
        'level',
        'sort_order',
        'path',
        'report_root',
        'is_active',
    ];

    protected $casts = [
        'is_group' => 'boolean',
        'is_active' => 'boolean',
        'level' => 'integer',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('code');
    }

    public function descendants(): Collection
    {
        $all = collect();
        foreach ($this->children as $child) {
            $all->push($child);
            $all = $all->merge($child->descendants());
        }

        return $all;
    }

    public function ancestors(): Collection
    {
        $nodes = collect();
        $current = $this->parent;

        while ($current) {
            $nodes->prepend($current);
            $current = $current->parent;
        }

        return $nodes;
    }

    public function breadcrumb(): string
    {
        return $this->ancestors()
            ->push($this)
            ->pluck('name')
            ->implode(' › ');
    }

    public function isPostable(): bool
    {
        return ! $this->is_group && $this->is_active;
    }

    public function scopeGroups(Builder $query): Builder
    {
        return $query->where('is_group', true);
    }

    public function scopeLedgers(Builder $query): Builder
    {
        return $query->where('is_group', false);
    }

    public function scopePostable(Builder $query): Builder
    {
        return $query->where('is_group', false)->where('is_active', true);
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeInReportRoot(Builder $query, string $reportRoot): Builder
    {
        return $query->where('report_root', $reportRoot);
    }

    public function ledgerDescendantIds(): array
    {
        if (! $this->is_group) {
            return [$this->id];
        }

        return $this->descendants()
            ->filter(fn (self $node) => ! $node->is_group)
            ->pluck('id')
            ->all();
    }

    public function rollupBalance(?Carbon $from = null, ?Carbon $to = null): array
    {
        $ids = $this->ledgerDescendantIds();

        if ($ids === []) {
            return ['debit' => 0.0, 'credit' => 0.0, 'balance' => 0.0];
        }

        $query = JournalEntryLine::query()
            ->whereIn('account_id', $ids)
            ->whereHas('journalEntry', function (Builder $q) use ($from, $to) {
                $q->where('status', 'posted');
                if ($from) {
                    $q->whereDate('entry_date', '>=', $from->toDateString());
                }
                if ($to) {
                    $q->whereDate('entry_date', '<=', $to->toDateString());
                }
            });

        $debit = round((float) $query->sum('debit'), 2);
        $credit = round((float) (clone $query)->sum('credit'), 2);

        return [
            'debit' => $debit,
            'credit' => $credit,
            'balance' => round($debit - $credit, 2),
        ];
    }
}
