<?php

namespace App\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseBackupManager
{
    protected const DISK = 'local';

    protected const DIRECTORY = 'backups/database';

    public function list(): array
    {
        $disk = Storage::disk(self::DISK);

        if (! $disk->exists(self::DIRECTORY)) {
            return [];
        }

        return collect($disk->files(self::DIRECTORY))
            ->filter(fn (string $path) => str_ends_with($path, '.sql'))
            ->sortByDesc(fn (string $path) => $disk->lastModified($path))
            ->values()
            ->map(function (string $path) use ($disk) {
                return [
                    'filename' => basename($path),
                    'path' => $path,
                    'size' => $disk->size($path),
                    'size_label' => $this->humanBytes($disk->size($path)),
                    'last_modified_at' => Carbon::createFromTimestamp($disk->lastModified($path)),
                ];
            })
            ->all();
    }

    public function create(): string
    {
        $config = $this->mysqlConfig();
        $disk = Storage::disk(self::DISK);

        if (! $disk->exists(self::DIRECTORY)) {
            $disk->makeDirectory(self::DIRECTORY);
        }

        $filename = sprintf(
            '%s-backup-%s.sql',
            str($config['database'])->slug()->value(),
            now()->format('Ymd-His')
        );

        $relativePath = self::DIRECTORY . '/' . $filename;
        $absolutePath = $disk->path($relativePath);
        $handle = fopen($absolutePath, 'wb');

        if ($handle === false) {
            throw new RuntimeException('Unable to create the backup file.');
        }

        $process = new Process($this->dumpCommand($config), base_path(), [
            'MYSQL_PWD' => $config['password'] ?? '',
        ]);
        $process->setTimeout(600);
        $errorOutput = '';

        try {
            $process->mustRun(function (string $type, string $buffer) use ($handle, &$errorOutput) {
                if ($type === Process::ERR) {
                    $errorOutput .= $buffer;

                    return;
                }

                fwrite($handle, $buffer);
            });
        } catch (ProcessFailedException $exception) {
            fclose($handle);
            File::delete($absolutePath);

            throw new RuntimeException('Database backup failed: ' . trim($errorOutput ?: $exception->getProcess()->getErrorOutput()));
        }

        fclose($handle);

        return $filename;
    }

    public function restore(string $filename): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException(
                'In-app database restore is disabled outside local/testing because a partial restore can corrupt the live database. Use an offline restore procedure instead.'
            );
        }

        $config = $this->mysqlConfig();
        $relativePath = self::DIRECTORY . '/' . basename($filename);
        $disk = Storage::disk(self::DISK);

        if (! $disk->exists($relativePath)) {
            throw new RuntimeException('Selected backup file was not found.');
        }

        $stream = fopen($disk->path($relativePath), 'r');

        if ($stream === false) {
            throw new RuntimeException('Unable to open the backup file for restore.');
        }

        $process = new Process($this->restoreCommand($config), base_path(), [
            'MYSQL_PWD' => $config['password'] ?? '',
        ]);
        $process->setTimeout(600);
        $process->setInput($stream);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            throw new RuntimeException('Database restore failed: ' . trim($exception->getProcess()->getErrorOutput()));
        } finally {
            fclose($stream);
        }
    }

    public function relativePath(string $filename): string
    {
        return self::DIRECTORY . '/' . basename($filename);
    }

    public function storeUpload(\Illuminate\Http\UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension !== 'sql') {
            throw new RuntimeException('Only .sql database dump files can be imported.');
        }

        $disk = Storage::disk(self::DISK);

        if (! $disk->exists(self::DIRECTORY)) {
            $disk->makeDirectory(self::DIRECTORY);
        }

        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = trim((string) preg_replace('/[^A-Za-z0-9._-]+/', '-', $baseName), '-');
        $safeName = $safeName !== '' ? $safeName : 'database';

        $filename = sprintf('imported-%s-%s.sql', now()->format('Ymd-His'), $safeName);

        $file->storeAs(self::DIRECTORY, $filename, self::DISK);

        if (! $disk->exists($this->relativePath($filename))) {
            throw new RuntimeException('Unable to store the imported SQL file.');
        }

        return $filename;
    }

    public function delete(string $filename): void
    {
        $relativePath = $this->relativePath($filename);
        $disk = Storage::disk(self::DISK);

        if (! $disk->exists($relativePath)) {
            throw new RuntimeException('Backup file was not found.');
        }

        if (! $disk->delete($relativePath)) {
            throw new RuntimeException('Unable to delete the backup file.');
        }
    }

    /**
     * @return array{deleted: array<int, string>, count: int}
     */
    public function prune(int $retentionDays): array
    {
        if ($retentionDays <= 0) {
            return ['deleted' => [], 'count' => 0];
        }

        $cutoff = now()->subDays($retentionDays)->timestamp;
        $deleted = [];

        foreach ($this->list() as $backup) {
            if ($backup['last_modified_at']->getTimestamp() >= $cutoff) {
                continue;
            }

            $this->delete($backup['filename']);
            $deleted[] = $backup['filename'];
        }

        return ['deleted' => $deleted, 'count' => count($deleted)];
    }

    /**
     * @return array<string, mixed>
     */
    public static function scheduleSummary(): array
    {
        $enabled = filter_var(config('database.backup.schedule_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $schedule = config('database.backup.schedule', 'daily');
        $time = config('database.backup.schedule_time', '02:00');
        $day = (int) config('database.backup.schedule_day', 0);
        $retention = (int) config('database.backup.retention_days', 14);

        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return [
            'enabled' => $enabled,
            'schedule' => $schedule,
            'time' => $time,
            'day' => $day,
            'day_label' => $dayNames[$day] ?? 'Sunday',
            'retention_days' => $retention,
            'label' => ! $enabled
                ? 'Automatic backups are disabled'
                : ($schedule === 'weekly'
                    ? "Weekly on {$dayNames[$day]} at {$time}"
                    : "Daily at {$time}"),
        ];
    }

    protected function mysqlConfig(): array
    {
        $defaultConnection = Config::get('database.default');
        $config = Config::get("database.connections.{$defaultConnection}");

        if (($config['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException('Backup and restore currently support MySQL connections only.');
        }

        if (empty($config['database']) || empty($config['username'])) {
            throw new RuntimeException('Database connection settings are incomplete.');
        }

        return $config;
    }

    protected function dumpCommand(array $config): array
    {
        return array_values(array_filter([
            $this->resolveBinary('mysqldump', 'database.backup.mysqldump'),
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--default-character-set=utf8mb4',
            $this->hostFlag($config),
            $this->portFlag($config),
            $this->socketFlag($config),
            $this->userFlag($config),
            $config['database'],
        ]));
    }

    protected function restoreCommand(array $config): array
    {
        return array_values(array_filter([
            $this->resolveBinary('mysql', 'database.backup.mysql'),
            $this->hostFlag($config),
            $this->portFlag($config),
            $this->socketFlag($config),
            $this->userFlag($config),
            $config['database'],
        ]));
    }

    protected function resolveBinary(string $command, string $configKey): string
    {
        $configured = trim((string) Config::get($configKey, ''));

        if ($configured !== '') {
            return $this->assertBinaryExists($configured, $command);
        }

        $discovered = $this->discoverBinary($command);

        if ($discovered !== null) {
            return $discovered;
        }

        throw new RuntimeException($this->missingBinaryMessage($command));
    }

    protected function discoverBinary(string $command): ?string
    {
        if ($this->commandIsRunnable($command)) {
            return $command;
        }

        $executable = PHP_OS_FAMILY === 'Windows' ? $command . '.exe' : $command;

        foreach ($this->candidateBinaryPaths($executable) as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    protected function candidateBinaryPaths(string $executable): array
    {
        $paths = [];

        if (PHP_OS_FAMILY === 'Windows') {
            $programFiles = getenv('ProgramFiles') ?: 'C:\\Program Files';
            $programFilesX86 = getenv('ProgramFiles(x86)') ?: 'C:\\Program Files (x86)';

            foreach ([$programFiles, $programFilesX86] as $root) {
                $mysqlRoot = $root . DIRECTORY_SEPARATOR . 'MySQL';
                if (is_dir($mysqlRoot)) {
                    foreach (glob($mysqlRoot . DIRECTORY_SEPARATOR . 'MySQL Server *' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . $executable) ?: [] as $match) {
                        $paths[] = $match;
                    }
                }
            }

            $paths[] = 'C:\\xampp\\mysql\\bin\\' . $executable;
            $paths[] = 'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\' . $executable;

            foreach (glob('C:\\laragon\\bin\\mysql\\*\\bin\\' . $executable) ?: [] as $match) {
                $paths[] = $match;
            }
        }

        return array_values(array_unique($paths));
    }

    protected function commandIsRunnable(string $command): bool
    {
        $probe = new Process([$command, '--version']);
        $probe->setTimeout(10);

        try {
            $probe->run();

            return $probe->isSuccessful();
        } catch (\Throwable) {
            return false;
        }
    }

    protected function assertBinaryExists(string $path, string $command): string
    {
        if (! is_file($path)) {
            throw new RuntimeException("Configured {$command} path was not found: {$path}");
        }

        return $path;
    }

    protected function missingBinaryMessage(string $command): string
    {
        $envKey = $command === 'mysqldump' ? 'MYSQL_DUMP_PATH' : 'MYSQL_CLIENT_PATH';
        $example = PHP_OS_FAMILY === 'Windows'
            ? 'C:\\Program Files\\MySQL\\MySQL Server 8.4\\bin\\' . $command . '.exe'
            : '/usr/bin/' . $command;

        return "'{$command}' was not found on this server. Set {$envKey} in your .env file, for example: {$envKey}=\"{$example}\"";
    }

    protected function hostFlag(array $config): ?string
    {
        if (! empty($config['unix_socket'])) {
            return null;
        }

        return ! empty($config['host']) ? '--host=' . $config['host'] : null;
    }

    protected function portFlag(array $config): ?string
    {
        if (! empty($config['unix_socket'])) {
            return null;
        }

        return ! empty($config['port']) ? '--port=' . $config['port'] : null;
    }

    protected function socketFlag(array $config): ?string
    {
        return ! empty($config['unix_socket']) ? '--socket=' . $config['unix_socket'] : null;
    }

    protected function userFlag(array $config): ?string
    {
        return ! empty($config['username']) ? '--user=' . $config['username'] : null;
    }

    protected function humanBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $value = $bytes / 1024;
        $unitIndex = 0;

        while ($value >= 1024 && $unitIndex < count($units) - 1) {
            $value /= 1024;
            $unitIndex++;
        }

        return number_format($value, $value >= 10 ? 0 : 1) . ' ' . $units[$unitIndex];
    }
}
