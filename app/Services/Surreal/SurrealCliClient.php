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
        $binary = $this->configuredBinary ?: (string) config('surreal.binary', 'surreal');

        if (is_file($binary) && is_executable($binary)) {
            return $binary;
        }

        return (new ExecutableFinder)->find($binary);
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
            throw new RuntimeException('Unable to find the `surreal` CLI. Install it or set SURREAL_BINARY to the executable path.');
        }

        return $binary;
    }
}
