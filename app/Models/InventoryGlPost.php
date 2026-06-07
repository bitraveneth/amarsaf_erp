<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryGlPost extends Model
{
    protected $fillable = [
        'event_type',
        'source_type',
        'source_id',
        'journal_entry_id',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
