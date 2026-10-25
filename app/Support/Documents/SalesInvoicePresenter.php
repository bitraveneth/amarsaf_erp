<?php

namespace App\Support\Documents;

use App\Helpers\SystemSettings;
use App\Models\Invoice;
use App\Support\PdfDocumentBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class SalesInvoicePresenter
{
    public static function forInvoice(Invoice $invoice, bool $forPdf = false): array
    {
        $agent = $invoice->order?->agent;
        $company = CompanyDocumentContext::make();

        $credits = self::sumLoaded($invoice, 'creditNotes', 'creditNotes');
        $receipts = self::sumLoaded($invoice, 'receipts', 'receipts');
        $advances = 0.0;
        if (Schema::hasTable('agent_advance_applications')) {
            $advances = self::sumLoaded($invoice, 'advanceApplications', 'advanceApplications');
        }

        $subTotal = round((float) $invoice->net_total, 2);
        $vat = round((float) $invoice->vat_amount, 2);
        $discount = round((float) $invoice->withholding + $credits, 2);
        $invoiceTotal = round($subTotal - $discount + $vat, 2);
        $paidAdvance = round($receipts + $advances, 2);
        $totalDue = round(max(0, $invoiceTotal - $paidAdvance), 2);

        $address = trim((string) ($invoice->order?->delivery_address ?? ''));
        if ($address === '') {
            $address = collect([$agent?->area, $agent?->zone])
                ->filter(fn ($part) => filled($part))
                ->implode(', ');
        }

        return [
            'number' => (string) $invoice->number,
            'date' => self::longDate($invoice->issued_at),
            'customer' => $agent?->name ?: 'Customer',
            'phone' => $agent?->phone ?: ($invoice->order?->delivery_contact_phone ?: '—'),
            'email' => $agent?->email ?: '—',
            'address' => $address !== '' ? $address : '—',
            'lines' => $invoice->items->values()->map(function ($item, int $index) {
                $unit = $item->product?->uom;

                return [
                    'index' => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                    'description' => (string) $item->description,
                    'quantity' => self::quantity($item->quantity),
                    'unit' => filled($unit) ? (string) $unit : '—',
                    'rate' => self::money($item->unit_price),
                    'amount' => self::money($item->line_total),
                ];
            })->all(),
            'sub_total' => self::money($subTotal),
            'discount' => self::money($discount),
            'vat' => self::money($vat),
            'invoice_total' => self::money($invoiceTotal),
            'paid_advance' => self::money($paidAdvance),
            'total_due' => self::money($totalDue),
            'currency_symbol' => (string) config('app.currency_symbol', '৳'),
            'payment' => self::payment(is_string($agent?->bank_details) ? $agent->bank_details : null),
            'signatory_name' => (string) SystemSettings::get('invoice_signatory_name', 'Kazuma Jean'),
            'signatory_title' => (string) SystemSettings::get('invoice_signatory_title', 'HR Director'),
            'terms' => (string) SystemSettings::get(
                'invoice_terms',
                'Lorem ipsum dolor sit amet, consectetuer adipiscing elit.'
            ),
            'footer_phone' => $company['phone'] ?: '+8801750720000',
            'footer_email' => $company['email'] ?: 'contact@amarsaf.com',
            'footer_address_lines' => self::addressLines($company['address'] ?? null),
            'logo_src' => self::assetSrc(
                public_path('images/brand/saf-logomark-white.svg'),
                'images/brand/saf-logomark-white.svg',
                $forPdf
            ),
            'taka_src' => self::assetSrc(
                public_path('images/brand/taka-white.png'),
                'images/brand/taka-white.png',
                $forPdf
            ),
            'signature_src' => self::assetSrc(
                public_path('images/brand/invoice-signature.png'),
                'images/brand/invoice-signature.png',
                $forPdf
            ),
            'icon_phone' => self::assetSrc(
                public_path('images/brand/invoice-icon-phone.png'),
                'images/brand/invoice-icon-phone.png',
                $forPdf
            ),
            'icon_mail' => self::assetSrc(
                public_path('images/brand/invoice-icon-mail.png'),
                'images/brand/invoice-icon-mail.png',
                $forPdf
            ),
            'icon_pin' => self::assetSrc(
                public_path('images/brand/invoice-icon-pin.png'),
                'images/brand/invoice-icon-pin.png',
                $forPdf
            ),
        ];
    }

    public static function money(float|int|string|null $amount): string
    {
        $number = round((float) $amount, 2);

        if (abs($number - round($number)) < 0.001) {
            return number_format($number, 0);
        }

        return number_format($number, 2);
    }

    public static function longDate(Carbon|string|null $date): string
    {
        if ($date === null || $date === '') {
            return '—';
        }

        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        $day = (int) $date->format('j');
        $mod100 = $day % 100;
        $suffix = ($mod100 >= 11 && $mod100 <= 13)
            ? 'th'
            : match ($day % 10) {
                1 => 'st',
                2 => 'nd',
                3 => 'rd',
                default => 'th',
            };

        return $date->format('F').' '.$day.$suffix.', '.$date->format('Y');
    }

    protected static function quantity(float|int|string|null $value): string
    {
        $number = (float) $value;

        if (abs($number - round($number)) < 0.001) {
            return (string) (int) round($number);
        }

        return rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.');
    }

    protected static function sumLoaded(Invoice $invoice, string $relation, string $queryMethod): float
    {
        if ($invoice->relationLoaded($relation)) {
            return (float) $invoice->{$relation}->sum('amount');
        }

        return (float) $invoice->{$queryMethod}()->sum('amount');
    }

    protected static function payment(?string $bankDetails): array
    {
        $parsed = self::parseBankDetails($bankDetails);
        if ($parsed !== null) {
            return $parsed;
        }

        return [
            'account_id' => (string) SystemSettings::get('invoice_account_id', '8765 4321 2345 6789'),
            'account_name' => (string) SystemSettings::get('invoice_account_name', 'Kazuma Jean'),
            'branch' => (string) SystemSettings::get('invoice_branch', 'Khulna'),
            'routing' => (string) SystemSettings::get('invoice_routing', '567473534'),
        ];
    }

    protected static function parseBankDetails(?string $raw): ?array
    {
        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        $map = [];
        foreach (preg_split('/\r\n|\r|\n/', $raw) ?: [] as $line) {
            if (! preg_match('/^\s*(account\s*id|account\s*name|branch|routing)\s*[:\-]\s*(.+)$/i', $line, $matches)) {
                continue;
            }

            $key = strtolower((string) preg_replace('/\s+/', '_', $matches[1]));
            $map[$key] = trim($matches[2]);
        }

        if (! isset($map['account_id'], $map['account_name'])) {
            return null;
        }

        return [
            'account_id' => $map['account_id'],
            'account_name' => $map['account_name'],
            'branch' => $map['branch'] ?? '—',
            'routing' => $map['routing'] ?? '—',
        ];
    }

    protected static function addressLines(?string $address): array
    {
        $address = trim((string) $address);
        if ($address === '') {
            return [
                'Gokulkhali, Ward No: 08, Alamdanga, PS;',
                'Chuadanga-7200, Bangladesh',
            ];
        }

        $lines = array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', $address) ?: []
        ), fn ($line) => $line !== ''));

        return $lines !== [] ? $lines : ['—'];
    }

    protected static function assetSrc(string $absolute, string $publicRelative, bool $forPdf): string
    {
        if ($forPdf && is_file($absolute)) {
            return PdfDocumentBuilder::fileUri($absolute) ?? asset($publicRelative);
        }

        return asset($publicRelative);
    }
}
