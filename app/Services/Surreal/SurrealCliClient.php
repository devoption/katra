<?php

namespace App\Services\Surreal;

use JsonException;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class SurrealCliClient
{
    public function __construct(
        private readonly ?string $configuredBinary = null,
        private readonly ?string $extrasPath = null,
        private readonly ?string $bundledBinaryRelativePath = null,
    ) {}

    public function isAvailable(): bool
    {
        return $this->binary() !== null;
    }

    public function startLocalServer(string $bindAddress, string $datastorePath, string $username, string $password, string $storageEngine): Process
    {
        $process = new Process([
            $this->requireBinary(),
            'start',
            '--bind',
            $bindAddress,
            '--user',
            $username,
            '--pass',
            $password,
            '--no-banner',
            sprintf('%s://%s', $storageEngine, $datastorePath),
        ], base_path());

        $process->setTimeout(null);
        $process->start();

        return $process;
    }

    public function waitUntilReady(string $endpoint, int $attempts = 20, int $sleepMilliseconds = 250): bool
    {
        for ($attempt = 0; $attempt < $attempts; $attempt++) {
            if ($this->isReady($endpoint)) {
                return true;
            }

            usleep($sleepMilliseconds * 1000);
        }

        return false;
    }

    public function isReady(string $endpoint): bool
    {
        $process = new Process([
            $this->requireBinary(),
            'is-ready',
            '--endpoint',
            $endpoint,
        ], base_path());

        $process->run();

        return $process->isSuccessful();
    }

    public function startDetachedLocalServer(
        string $bindAddress,
        string $datastorePath,
        string $username,
        string $password,
        string $storageEngine,
        string $logPath,
    ): int {
        if (DIRECTORY_SEPARATOR === '\\') {
            throw new RuntimeException('Detached SurrealDB startup is not implemented for Windows yet.');
        }

        $command = implode(' ', [
            'nohup',
            escapeshellarg($this->requireBinary()),
            'start',
            '--bind',
            escapeshellarg($bindAddress),
            '--user',
            escapeshellarg($username),
            '--pass',
            escapeshellarg($password),
            '--no-banner',
            escapeshellarg(sprintf('%s://%s', $storageEngine, $datastorePath)),
            '>>',
            escapeshellarg($logPath),
            '2>&1',
            '<',
            '/dev/null',
            '&',
            'echo',
            '$!',
        ]);

        $process = Process::fromShellCommandline($command, base_path());
        $process->run();

        if (! $process->isSuccessful()) {
            $stderr = trim($process->getErrorOutput());
            $stdout = trim($process->getOutput());

            throw new RuntimeException('Failed to start the detached SurrealDB runtime ('.implode('; ', array_filter([
                sprintf('exit code %d', $process->getExitCode() ?? 1),
                $stderr !== '' ? sprintf('stderr: %s', $stderr) : null,
                $stdout !== '' ? sprintf('stdout: %s', $stdout) : null,
            ])).').');
        }

        $pid = (int) trim($process->getOutput());

        if ($pid <= 0) {
            throw new RuntimeException('Failed to determine the SurrealDB runtime process id.');
        }

        return $pid;
    }

    /**
     * @return array<int, mixed>
     */
    public function runQuery(string $endpoint, string $namespace, string $database, string $username, string $password, string $query): array
    {
        $process = new Process([
            $this->requireBinary(),
            'sql',
            '--endpoint',
            $endpoint,
            '--username',
            $username,
            '--password',
            $password,
            '--auth-level',
            'root',
            '--namespace',
            $namespace,
            '--database',
            $database,
            '--json',
            '--hide-welcome',
        ], base_path());

        $process->setInput($query);
        $process->run();

        if (! $process->isSuccessful()) {
            $stderr = trim($process->getErrorOutput());
            $stdout = trim($process->getOutput());
            $details = array_filter([
                sprintf('exit code %d', $process->getExitCode() ?? 1),
                $stderr !== '' ? sprintf('stderr: %s', $stderr) : null,
                $stdout !== '' ? sprintf('stdout: %s', $stdout) : null,
            ]);

            throw new RuntimeException('Failed to execute the SurrealDB query ('.implode('; ', $details).').');
        }

        return $this->decodeJsonOutput($process->getOutput());
    }

    public function binary(): ?string
    {
        foreach ($this->binaryCandidates() as $candidate) {
            if (is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }

            $resolvedBinary = (new ExecutableFinder)->find($candidate);

            if ($resolvedBinary !== null) {
                return $resolvedBinary;
            }
        }

        return null;
    }

    public function usesBundledBinary(): bool
    {
        $bundledBinary = $this->bundledBinary();
        $resolvedBinary = $this->binary();

        if ($bundledBinary === null || $resolvedBinary === null) {
            return false;
        }

        return realpath($bundledBinary) === realpath($resolvedBinary);
    }

    /**
     * @return array<int, mixed>
     */
    private function decodeJsonOutput(string $output): array
    {
        $decodedResults = [];

        foreach (preg_split('/\R/', $output) ?: [] as $line) {
            $trimmedLine = trim($line);

            if ($trimmedLine === '') {
                continue;
            }

            $jsonPayload = preg_replace('/^[^\[]*/', '', $trimmedLine);

            if (! is_string($jsonPayload) || $jsonPayload === '' || ! str_starts_with($jsonPayload, '[')) {
                continue;
            }

            try {
                $decodedResults[] = json_decode($jsonPayload, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new RuntimeException('Unable to decode the SurrealDB CLI response.', previous: $exception);
            }
        }

        if ($decodedResults === []) {
            throw new RuntimeException('Unable to locate JSON output in the SurrealDB CLI response.');
        }

        return $decodedResults;
    }

    private function requireBinary(): string
    {
        $binary = $this->binary();

        if ($binary === null) {
            throw new RuntimeException('Unable to find the `surreal` CLI. '.implode(' ', $this->missingBinaryGuidance()));
        }

        return $binary;
    }

    /**
     * @return array<int, string>
     */
    private function binaryCandidates(): array
    {
        $configuredBinary = $this->configuredBinary ?: (string) config('surreal.binary', 'surreal');
        $defaultBinary = 'surreal';
        $bundledBinary = $this->bundledBinary();

        return array_values(array_unique(array_filter([
            $configuredBinary !== $defaultBinary ? $configuredBinary : null,
            $bundledBinary,
            $configuredBinary,
        ])));
    }

    private function bundledBinary(): ?string
    {
        $extrasPath = $this->extrasPath;

        if ($extrasPath === null || $extrasPath === '') {
            $configuredExtrasPath = config('surreal.extras_path');

            if (is_string($configuredExtrasPath) && $configuredExtrasPath !== '') {
                $extrasPath = $configuredExtrasPath;
            }
        }

        if ($extrasPath === null || $extrasPath === '') {
            return null;
        }

        $bundledBinaryRelativePath = $this->bundledBinaryRelativePath;

        if ($bundledBinaryRelativePath === null || $bundledBinaryRelativePath === '') {
            $configuredBundledPath = config('surreal.bundled_binary_relative_path', 'surreal/bin/surreal');
            $bundledBinaryRelativePath = is_string($configuredBundledPath) ? $configuredBundledPath : 'surreal/bin/surreal';
        }

        return rtrim($extrasPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.ltrim($bundledBinaryRelativePath, DIRECTORY_SEPARATOR);
    }

    /**
     * @return array<int, string>
     */
    private function missingBinaryGuidance(): array
    {
        $guidance = [];
        $bundledBinary = $this->bundledBinary();

        if ($bundledBinary !== null) {
            $guidance[] = sprintf('Checked bundled NativePHP extras path [%s].', $bundledBinary);
        }

        $configuredBinary = $this->configuredBinary ?: (string) config('surreal.binary', 'surreal');
        $guidance[] = $this->looksLikeBinaryPath($configuredBinary)
            ? sprintf('Checked configured Surreal binary path [%s].', $configuredBinary)
            : sprintf('Checked the current process PATH for [%s].', $configuredBinary);
        $guidance[] = 'Install the CLI manually for local source development or set SURREAL_BINARY to a custom executable path.';

        return $guidance;
    }

    private function looksLikeBinaryPath(string $binary): bool
    {
        return str_contains($binary, '/')
            || str_contains($binary, '\\')
            || str_starts_with($binary, '.');
    }
}
