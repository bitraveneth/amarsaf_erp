<?php

namespace App\Support;

use App\Models\KycDocumentType;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AgentKycDocuments
{
    public const MAX_FILE_KB = 4096;

    public static function maxFileSizeLabel(): string
    {
        if (self::MAX_FILE_KB >= 1024) {
            $mb = self::MAX_FILE_KB / 1024;

            return (floor($mb) == $mb ? (int) $mb : number_format($mb, 1)) . ' MB';
        }

        return self::MAX_FILE_KB . ' KB';
    }

    public static function formatBytes(?int $bytes): string
    {
        if ($bytes === null || $bytes <= 0) {
            return '—';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }

    /**
     * @param  array<int, mixed>|null  $raw
     * @return array<int, array<string, mixed>>
     */
    public static function normalizeList(?array $raw): array
    {
        if ($raw === null || $raw === []) {
            return [];
        }

        $normalized = [];

        foreach ($raw as $item) {
            if (is_string($item)) {
                $normalized[] = self::normalizeLegacyString($item);
                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            $path = $item['path'] ?? null;
            $typeName = trim((string) ($item['type_name'] ?? $item['label'] ?? 'Document'));
            $uid = (string) ($item['uid'] ?? md5(($path ?: $typeName) . serialize($item)));

            $normalized[] = [
                'uid' => $uid,
                'type_id' => isset($item['type_id']) ? (int) $item['type_id'] : null,
                'type_name' => $typeName !== '' ? $typeName : 'Document',
                'path' => is_string($path) && $path !== '' ? $path : null,
                'original_name' => $item['original_name'] ?? ($path ? basename($path) : null),
                'size' => isset($item['size']) ? (int) $item['size'] : null,
                'uploaded_at' => $item['uploaded_at'] ?? null,
            ];
        }

        return array_values($normalized);
    }

    /**
     * @return array<string, mixed>
     */
    protected static function normalizeLegacyString(string $item): array
    {
        $trimmed = trim($item);

        if (str_starts_with($trimmed, 'agents/kyc/')) {
            return [
                'uid' => md5($trimmed),
                'type_id' => null,
                'type_name' => 'Document',
                'path' => $trimmed,
                'original_name' => basename($trimmed),
                'size' => Storage::disk('public')->exists($trimmed)
                    ? Storage::disk('public')->size($trimmed)
                    : null,
                'uploaded_at' => null,
            ];
        }

        return [
            'uid' => md5($trimmed),
            'type_id' => null,
            'type_name' => $trimmed,
            'path' => null,
            'original_name' => null,
            'size' => null,
            'uploaded_at' => null,
        ];
    }

    /**
     * @param  array<int, mixed>|null  $existingDocs
     * @return array<int, array<string, mixed>>
     */
    public static function syncFromRequest(Request $request, ?array $existingDocs): array
    {
        self::assertValidUploadRows($request);

        $existing = self::normalizeList($existingDocs);
        $keepUids = array_map('strval', $request->input('kyc_keep', []));

        $retained = [];

        foreach ($existing as $doc) {
            if (in_array((string) $doc['uid'], $keepUids, true)) {
                $retained[] = $doc;
                continue;
            }

            if (! empty($doc['path']) && Storage::disk('public')->exists($doc['path'])) {
                Storage::disk('public')->delete($doc['path']);
            }
        }

        $newDocs = [];
        $uploadRows = $request->input('kyc_new', []);

        foreach ($uploadRows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            /** @var UploadedFile|null $file */
            $file = $request->file("kyc_new.{$index}.file");

            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $typeName = self::resolveTypeName($row);
            $path = $file->store('agents/kyc', 'public');

            $newDocs[] = [
                'uid' => uniqid('kyc_', true),
                'type_id' => self::resolveTypeId($row),
                'type_name' => $typeName,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'uploaded_at' => now()->toIso8601String(),
            ];
        }

        return array_values(array_merge($retained, $newDocs));
    }

    protected static function assertValidUploadRows(Request $request): void
    {
        $errors = [];
        $uploadRows = $request->input('kyc_new', []);

        foreach ($uploadRows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $file = $request->file("kyc_new.{$index}.file");

            if (! $file instanceof UploadedFile) {
                continue;
            }

            $typeId = (string) ($row['type_id'] ?? '');
            $customType = trim((string) ($row['custom_type'] ?? ''));

            if ($typeId === '' && $customType === '') {
                $errors["kyc_new.{$index}.type_id"] = 'Select a document type for each uploaded file.';
            }

            if ($typeId === '__custom__' && $customType === '') {
                $errors["kyc_new.{$index}.custom_type"] = 'Enter a name for the custom document type.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected static function resolveTypeName(array $row): string
    {
        $typeId = (string) ($row['type_id'] ?? '');

        if ($typeId === '__custom__') {
            return trim((string) ($row['custom_type'] ?? '')) ?: 'Custom Document';
        }

        if ($typeId !== '' && is_numeric($typeId)) {
            $type = KycDocumentType::find((int) $typeId);

            if ($type) {
                return $type->name;
            }
        }

        return 'Document';
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected static function resolveTypeId(array $row): ?int
    {
        $typeId = (string) ($row['type_id'] ?? '');

        if ($typeId === '' || $typeId === '__custom__' || ! is_numeric($typeId)) {
            return null;
        }

        return (int) $typeId;
    }
}
