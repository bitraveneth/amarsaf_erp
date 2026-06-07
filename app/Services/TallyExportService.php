<?php

namespace App\Services;

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
}
