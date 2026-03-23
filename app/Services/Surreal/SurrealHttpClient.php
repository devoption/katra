<?php

namespace App\Services\Surreal;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use JsonException;
use RuntimeException;
use Throwable;

class SurrealHttpClient
{
    /**
     * @var array<string, true>
     */
    private array $preparedContexts = [];

    public function __construct(
        private readonly Factory $http,
    ) {}

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
        try {
            return $this->http->acceptJson()
                ->connectTimeout(2)
                ->timeout(2)
                ->get($this->healthUrl($endpoint))
                ->successful();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return list<mixed>
     */
    public function runQuery(string $endpoint, string $namespace, string $database, string $username, string $password, string $query): array
    {
        $this->ensureContext(
            endpoint: $endpoint,
            namespace: $namespace,
            database: $database,
            username: $username,
            password: $password,
        );

        try {
            $response = $this->request(
                endpoint: $endpoint,
                namespace: $namespace,
                database: $database,
                username: $username,
                password: $password,
            )->withBody($query, 'text/plain')
                ->post($this->sqlUrl($endpoint))
                ->throw();
        } catch (RequestException $exception) {
            throw $this->requestException('Failed to execute the SurrealDB query', $exception);
        }

        return $this->normalizeResults($this->decodeResponse($response->json()));
    }

    private function ensureContext(string $endpoint, string $namespace, string $database, string $username, string $password): void
    {
        $cacheKey = implode('|', [$endpoint, $namespace, $database, $username]);

        if (isset($this->preparedContexts[$cacheKey])) {
            return;
        }

        $this->runContextStatement(
            label: 'prepare the SurrealDB namespace',
            endpoint: $endpoint,
            request: $this->request(
                endpoint: $endpoint,
                namespace: null,
                database: null,
                username: $username,
                password: $password,
            )->withBody(
                sprintf('DEFINE NAMESPACE IF NOT EXISTS %s;', $this->normalizeIdentifier($namespace, 'namespace')),
                'text/plain',
            ),
        );

        $this->runContextStatement(
            label: 'prepare the SurrealDB database',
            endpoint: $endpoint,
            request: $this->request(
                endpoint: $endpoint,
                namespace: $namespace,
                database: null,
                username: $username,
                password: $password,
            )->withBody(
                sprintf('DEFINE DATABASE IF NOT EXISTS %s;', $this->normalizeIdentifier($database, 'database')),
                'text/plain',
            ),
        );

        $this->preparedContexts[$cacheKey] = true;
    }

    private function runContextStatement(string $label, string $endpoint, PendingRequest $request): void
    {
        try {
            $response = $request->post($this->sqlUrl($endpoint))->throw();
        } catch (RequestException $exception) {
            throw $this->requestException(sprintf('Failed to %s', $label), $exception);
        }

        $this->decodeResponse($response->json());
    }

    private function request(string $endpoint, ?string $namespace, ?string $database, string $username, string $password): PendingRequest
    {
        return $this->http->baseUrl($this->baseUrl($endpoint))
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(30)
            ->withBasicAuth($username, $password)
            ->withHeaders(array_filter([
                'Surreal-NS' => $namespace,
                'Surreal-DB' => $database,
            ], static fn (?string $value): bool => $value !== null && $value !== ''));
    }

    private function healthUrl(string $endpoint): string
    {
        return $this->baseUrl($endpoint).'/health';
    }

    private function sqlUrl(string $endpoint): string
    {
        return $this->baseUrl($endpoint).'/sql';
    }

    /**
     * @return list<mixed>
     */
    private function decodeResponse(mixed $decoded): array
    {
        if (! is_array($decoded) || $decoded === []) {
            throw new RuntimeException('Unable to decode the SurrealDB HTTP response.');
        }

        foreach ($decoded as $index => $statement) {
            if (! is_array($statement)) {
                continue;
            }

            if (($statement['status'] ?? null) !== 'OK') {
                $codeName = Arr::get($statement, 'code');
                $details = Arr::get($statement, 'detail')
                    ?? Arr::get($statement, 'result')
                    ?? Arr::get($statement, 'information')
                    ?? 'unknown error';

                throw new SurrealQueryException(
                    message: sprintf(
                        'The SurrealDB query statement at position %d failed (%s).',
                        $index + 1,
                        $this->stringifyDetails($details),
                    ),
                    codeName: is_string($codeName) ? $codeName : null,
                    details: $details,
                    statementIndex: $index + 1,
                );
            }
        }

        return $decoded;
    }

    /**
     * @param  list<mixed>  $statements
     * @return list<mixed>
     */
    private function normalizeResults(array $statements): array
    {
        return array_map(function (mixed $statement): mixed {
            if (! is_array($statement)) {
                return [];
            }

            return $this->normalizeStatementResult($statement['result'] ?? null);
        }, $statements);
    }

    /**
     * @return list<mixed>
     */
    private function normalizeStatementResult(mixed $result): array
    {
        if ($result === null) {
            return [];
        }

        if (! is_array($result)) {
            return [$result];
        }

        if ($this->isAssociative($result)) {
            return [$result];
        }

        return $result;
    }

    private function baseUrl(string $endpoint): string
    {
        $parts = parse_url($endpoint);

        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'])) {
            throw new RuntimeException(sprintf('Unable to parse the SurrealDB endpoint [%s].', $endpoint));
        }

        $scheme = match ($parts['scheme']) {
            'ws' => 'http',
            'wss' => 'https',
            default => $parts['scheme'],
        };

        $authority = $parts['host'];

        if (isset($parts['port'])) {
            $authority .= ':'.$parts['port'];
        }

        $path = trim((string) ($parts['path'] ?? ''), '/');

        return sprintf(
            '%s://%s%s',
            $scheme,
            $authority,
            $path !== '' ? '/'.$path : '',
        );
    }

    private function normalizeIdentifier(string $identifier, string $label): string
    {
        if (! preg_match('/^[A-Za-z0-9_]+$/', $identifier)) {
            throw new RuntimeException(sprintf('The SurrealDB %s identifier [%s] contains unsupported characters.', $label, $identifier));
        }

        return $identifier;
    }

    private function requestException(string $prefix, RequestException $exception): RuntimeException
    {
        $payload = $exception->response?->json();
        $codeName = Arr::get($payload, 'code');
        $details = Arr::get($payload, 'information')
            ?? Arr::get($payload, 'details')
            ?? $exception->response?->body()
            ?? $exception->getMessage();

        return new SurrealQueryException(
            message: sprintf('%s (%s).', $prefix, $this->stringifyDetails($details)),
            codeName: is_string($codeName) ? $codeName : null,
            details: $details,
            previous: new RuntimeException($exception->getMessage(), previous: $exception),
        );
    }

    /**
     * @param  array<mixed>  $value
     */
    private function isAssociative(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }

    private function stringifyDetails(mixed $details): string
    {
        if (is_scalar($details) || $details === null) {
            return trim((string) $details);
        }

        try {
            $encoded = json_encode($details, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return 'unserializable error details';
        }

        return is_string($encoded) ? $encoded : 'unknown error';
    }
}
