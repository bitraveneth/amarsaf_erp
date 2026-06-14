<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TallyExportService
{
    public function vouchers(Carbon $from, Carbon $to): Collection
    {
        return JournalEntry::with(['lines.account'])
            ->where('status', 'posted')
            ->whereDate('entry_date', '>=', $from->toDateString())
            ->whereDate('entry_date', '<=', $to->toDateString())
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();
    }

    public function toXml(Carbon $from, Carbon $to): string
    {
        $journals = $this->vouchers($from, $to);
        $xml = new \SimpleXMLElement('<ENVELOPE></ENVELOPE>');
        $header = $xml->addChild('HEADER');
        $header->addChild('TALLYREQUEST', 'Import Data');
        $body = $xml->addChild('BODY');
        $importData = $body->addChild('IMPORTDATA');
        $requestData = $importData->addChild('REQUESTDATA');

        foreach ($this->groupMasterMessages() as $message) {
            $node = $requestData->addChild('TALLYMESSAGE')->addChild('GROUP');
            $node->addAttribute('NAME', htmlspecialchars($message['name']));
            $node->addAttribute('ACTION', 'Create');
            if (! empty($message['parent'])) {
                $node->addChild('PARENT', htmlspecialchars($message['parent']));
            }
        }

        foreach ($this->ledgerMasterMessages() as $message) {
            $node = $requestData->addChild('TALLYMESSAGE')->addChild('LEDGER');
            $node->addAttribute('NAME', htmlspecialchars($message['name']));
            $node->addAttribute('ACTION', 'Create');
            if (! empty($message['parent'])) {
                $node->addChild('PARENT', htmlspecialchars($message['parent']));
            }
        }

        foreach ($journals as $journal) {
            $tallyMessage = $requestData->addChild('TALLYMESSAGE');
            $voucher = $tallyMessage->addChild('VOUCHER');
            $voucher->addAttribute('VCHTYPE', 'Journal');
            $voucher->addAttribute('ACTION', 'Create');
            $voucher->addChild('DATE', $journal->entry_date->format('Ymd'));
            $voucher->addChild('VOUCHERNUMBER', htmlspecialchars($journal->number));
            $voucher->addChild('NARRATION', htmlspecialchars((string) $journal->description));

            foreach ($journal->lines as $line) {
                $entry = $voucher->addChild('ALLLEDGERENTRIES.LIST');
                $entry->addChild('LEDGERNAME', htmlspecialchars($line->account?->name ?? 'Unknown'));
                if ((float) $line->debit > 0) {
                    $entry->addChild('ISDEEMEDPOSITIVE', 'Yes');
                    $entry->addChild('AMOUNT', number_format((float) $line->debit * -1, 2, '.', ''));
                } else {
                    $entry->addChild('ISDEEMEDPOSITIVE', 'No');
                    $entry->addChild('AMOUNT', number_format((float) $line->credit, 2, '.', ''));
                }
            }
        }

        return $xml->asXML() ?: '';
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    protected function groupMasterMessages(): array
    {
        return Account::query()
            ->groups()
            ->with('parent')
            ->orderBy('level')
            ->orderBy('code')
            ->get()
            ->map(fn (Account $account) => [
                'name' => $account->name,
                'parent' => $account->parent?->name,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    protected function ledgerMasterMessages(): array
    {
        return Account::query()
            ->ledgers()
            ->with('parent')
            ->orderBy('code')
            ->get()
            ->map(fn (Account $account) => [
                'name' => $account->name,
                'parent' => $account->parent?->name,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function preview(Carbon $from, Carbon $to, int $limit = 50): array
    {
        $journals = $this->vouchers($from, $to);
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $lineCount = 0;

        $vouchers = $journals->map(function (JournalEntry $journal) use (&$totalDebit, &$totalCredit, &$lineCount) {
            $debit = $journal->totalDebit();
            $credit = $journal->totalCredit();
            $totalDebit += $debit;
            $totalCredit += $credit;
            $lineCount += $journal->lines->count();

            return [
                'number' => $journal->number,
                'date' => $journal->entry_date->format('d M Y'),
                'type' => str_replace('_', ' ', (string) ($journal->journal_type ?? '')),
                'description' => (string) ($journal->description ?? ''),
                'debit' => round($debit, 2),
                'credit' => round($credit, 2),
                'lines' => $journal->lines->map(fn ($line) => [
                    'account' => $line->account?->name ?? 'Unknown',
                    'debit' => round((float) $line->debit, 2),
                    'credit' => round((float) $line->credit, 2),
                ])->values()->all(),
            ];
        });

        $count = $vouchers->count();

        return [
            'title' => 'Tally XML — posted journals',
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'label' => $from->format('d M Y') . ' – ' . $to->format('d M Y'),
            ],
            'summary' => [
                'voucher_count' => $count,
                'line_count' => $lineCount,
                'total_debit' => round($totalDebit, 2),
                'total_credit' => round($totalCredit, 2),
                'group_count' => Account::groups()->count(),
                'ledger_count' => Account::ledgers()->count(),
            ],
            'vouchers' => $vouchers->take($limit)->values()->all(),
            'total_rows' => $count,
            'truncated' => $count > $limit,
            'preview_limit' => $limit,
        ];
    }
}
