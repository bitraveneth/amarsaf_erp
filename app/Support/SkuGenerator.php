<?php

namespace App\Support;

use App\Helpers\SystemSettings;
use App\Models\Product;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SkuGenerator
{
    public const DEFAULT_PREFIX = 'SAF';

    public const DEFAULT_PATTERN = '{BRAND}-{SIZE}-{PACK}';

    /** Modern Saf format: SAF-500ML-CTN, MAT-PET-500ML */
    public const FORMAT_REGEX = '/^[A-Z0-9]+(-[A-Z0-9]+){1,4}$/';

    public static function brandPrefix(): string
    {
        $prefix = SystemSettings::get('sku_brand_prefix', self::DEFAULT_PREFIX);

        return self::sanitizeSegment(is_string($prefix) ? $prefix : self::DEFAULT_PREFIX);
    }

    public static function pattern(): string
    {
        $pattern = SystemSettings::get('sku_format_pattern', self::DEFAULT_PATTERN);

        return is_string($pattern) && trim($pattern) !== ''
            ? trim($pattern)
            : self::DEFAULT_PATTERN;
    }

    public static function normalize(?string $sku): string
    {
        $sku = strtoupper(trim((string) $sku));
        $sku = preg_replace('/[\s_]+/', '-', $sku) ?? '';
        $sku = preg_replace('/[^A-Z0-9-]+/', '', $sku) ?? '';
        $sku = preg_replace('/-+/', '-', $sku) ?? '';

        return trim($sku, '-');
    }

    public static function isValid(?string $sku): bool
    {
        $sku = self::normalize($sku);

        if ($sku === '' || strlen($sku) > 40) {
            return false;
        }

        return (bool) preg_match(self::FORMAT_REGEX, $sku);
    }

    public static function validationRule(): string
    {
        return 'regex:/^[A-Z0-9]+(-[A-Z0-9]+){1,4}$/i';
    }

    public static function formatHint(): string
    {
        return 'Use uppercase segments separated by hyphens, e.g. ' . self::brandPrefix() . '-500ML-CTN';
    }

    /**
     * @param  array{product_type?: string, size?: string|null, uom?: string|null, packaging?: string|null, material_kind?: string|null}  $context
     */
    public static function suggest(array $context = []): string
    {
        $productType = $context['product_type'] ?? 'finished';
        $brand = self::brandPrefix();

        if (in_array($productType, ['raw', 'service', 'inhouse'], true)) {
            $brand = 'MAT';
        }

        $size = self::sanitizeSegment($context['size'] ?? 'STD');
        $pack = self::sanitizeSegment($context['packaging'] ?? $context['uom'] ?? 'UNIT');
        $materialKind = self::sanitizeSegment($context['material_kind'] ?? '');

        $replacements = [
            '{BRAND}' => $brand,
            '{SIZE}' => $size !== '' ? $size : 'STD',
            '{PACK}' => $pack !== '' ? $pack : 'UNIT',
            '{UOM}' => self::sanitizeSegment($context['uom'] ?? 'UNIT') ?: 'UNIT',
            '{KIND}' => $materialKind !== '' ? $materialKind : 'GEN',
        ];

        $candidate = strtr(self::pattern(), $replacements);
        $candidate = self::normalize($candidate);

        return self::ensureUnique($candidate);
    }

    public static function suggestFromProductType(string $productType, ?string $size, ?string $uom): string
    {
        $pack = match (strtolower((string) $uom)) {
            'carton', 'ctn' => 'CTN',
            'bottle', 'btl' => 'BTL',
            'jar' => 'JAR',
            'kg' => 'KG',
            'liter', 'litre', 'l' => 'L',
            default => self::sanitizeSegment($uom) ?: 'UNIT',
        };

        $sizeSegment = self::sizeSegment($size);

        return self::suggest([
            'product_type' => $productType,
            'size' => $sizeSegment,
            'uom' => $uom,
            'packaging' => $pack,
        ]);
    }

    protected static function sizeSegment(?string $size): string
    {
        if (! is_string($size) || trim($size) === '') {
            return 'STD';
        }

        $size = strtoupper(trim($size));

        if (preg_match('/(\d+(?:\.\d+)?)\s*(ML|L|KG|G)/i', $size, $matches)) {
            $value = rtrim(rtrim($matches[1], '0'), '.');
            $unit = strtoupper($matches[2]);

            return $unit === 'L' && ! str_contains($size, 'ML')
                ? $value . 'L'
                : $value . $unit;
        }

        return self::sanitizeSegment($size) ?: 'STD';
    }

    protected static function sanitizeSegment(?string $value): string
    {
        $value = self::normalize((string) $value);

        return Str::limit($value, 12, '');
    }

    protected static function ensureUnique(string $base): string
    {
        if (! Product::where('sku', $base)->exists()) {
            return $base;
        }

        for ($i = 2; $i <= 99; $i++) {
            $candidate = $base . '-' . $i;

            if (! Product::where('sku', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new InvalidArgumentException('Unable to generate a unique SKU.');
    }
}
