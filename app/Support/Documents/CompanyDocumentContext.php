<?php

namespace App\Support\Documents;

use App\Helpers\SystemSettings;
use App\Support\PdfDocumentBuilder;
use Illuminate\Support\Facades\Storage;

class CompanyDocumentContext
{
    /**
     * Letterhead used when Settings has not saved a company yet.
     * A saved company name, phone, email, or address replaces these.
     *
     * @return array{legal_name: string, phone: string, email: string, address: string}
     */
    public static function letterhead(): array
    {
        return [
            'legal_name' => SystemSettings::configuredLegalName() ?: 'Sohag Food & Beverage Ltd.',
            'phone' => self::stringSetting('company_phone') ?: '+8801750720000',
            'email' => self::stringSetting('company_email') ?: 'contact@amarsaf.com',
            'address' => self::stringSetting('company_address') ?: "Gokulkhali, Ward No: 08, Alamdanga,\nPS; Chuadanga-7200; Bangladesh",
        ];
    }

    public static function make(): array
    {
        $letterhead = self::letterhead();
        $legalName = $letterhead['legal_name'];
        $logoPath = self::resolveLogoPath();
        $primary = SystemSettings::get('brand_primary', SystemSettings::defaultBrandPrimary());

        return [
            'legal_name' => $legalName,
            'brand_name' => SystemSettings::brandName(),
            'initials' => SystemSettings::initials($legalName),
            'email' => $letterhead['email'],
            'phone' => $letterhead['phone'],
            'address' => $letterhead['address'],
            'logo_url' => SystemSettings::logoUrl(),
            'logo_path' => $logoPath,
            'logo_file_uri' => PdfDocumentBuilder::fileUri($logoPath),
            'primary' => is_string($primary) && $primary !== '' ? $primary : SystemSettings::defaultBrandPrimary(),
            'currency' => config('app.currency', 'BDT'),
        ];
    }

    protected static function resolveLogoPath(): ?string
    {
        $logoSetting = SystemSettings::get('company_logo_path');

        if (is_string($logoSetting) && trim($logoSetting) !== '') {
            $absolute = Storage::disk('public')->path(trim($logoSetting));

            if (is_file($absolute)) {
                return $absolute;
            }
        }

        $defaultLogo = public_path(SystemSettings::defaultLogoAsset());

        return is_file($defaultLogo) ? $defaultLogo : null;
    }

    protected static function stringSetting(string $key): ?string
    {
        $value = SystemSettings::get($key);

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
