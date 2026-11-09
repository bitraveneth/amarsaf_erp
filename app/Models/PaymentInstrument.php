<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentInstrument extends Model
{
    protected $fillable = [
        'payment_method_category_id',
        'name',
        'account_holder_name',
        'bank_name',
        'slug',
        'account_number',
        'branch',
        'routing',
        'swift_code',
        'iban',
        'address',
        'phone',
        'details',
        'account_id',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PaymentMethodCategory::class, 'payment_method_category_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function label(): string
    {
        $bits = [$this->name];
        if ($this->account_number) {
            $bits[] = $this->account_number;
        }

        return implode(' · ', $bits);
    }

    public function ledgerSlug(): ?string
    {
        return $this->account?->slug ?: $this->slug;
    }

    /**
     * @return list<string>
     */
    public function detailLines(): array
    {
        return array_values(array_filter([
            $this->account_holder_name ? 'Holder: '.$this->account_holder_name : null,
            $this->bank_name ?: null,
            $this->account_number ? 'A/C '.$this->account_number : null,
            $this->branch ? 'Branch: '.$this->branch : null,
            $this->routing ? 'Routing '.$this->routing : null,
            $this->swift_code ? 'SWIFT '.$this->swift_code : null,
            $this->iban ? 'IBAN '.$this->iban : null,
            $this->phone ?: null,
            $this->address ?: null,
            $this->details ?: null,
        ]));
    }
}
