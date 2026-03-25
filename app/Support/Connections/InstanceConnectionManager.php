<?php

namespace App\Support\Connections;

use App\Models\InstanceConnection;
use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Eloquent\Collection;

class InstanceConnectionManager
{
    private const ACTIVE_CONNECTION_SESSION_KEY = 'instance_connection.active_id';

    /**
     * @return Collection<int, InstanceConnection>
     */
    public function connectionsFor(User $user, string $currentInstanceUrl): Collection
    {
        return $user->instanceConnections()
            ->orderByDesc('last_used_at')
            ->orderBy('name')
            ->get()
            ->values();
    }

    public function activeConnectionFor(User $user, string $currentInstanceUrl, Session $session): InstanceConnection
    {
        $connections = $this->connectionsFor($user, $currentInstanceUrl);
        $activeConnectionId = $session->get(self::ACTIVE_CONNECTION_SESSION_KEY);
        $activeConnection = $connections->firstWhere('id', $activeConnectionId);

        if (! $activeConnection instanceof InstanceConnection) {
            $activeConnection = $connections->firstWhere('kind', InstanceConnection::KIND_CURRENT_INSTANCE)
                ?? $this->ensureCurrentInstanceConnection($user, $currentInstanceUrl);
        }

        $this->activate($activeConnection, $session);

        return $activeConnection;
    }

    /**
     * @param  array{name: string, base_url: string}  $attributes
     */
    public function createServerConnection(User $user, array $attributes, Session $session): InstanceConnection
    {
        $baseUrl = $this->normalizeUrl($attributes['base_url']);

        $connection = $user->instanceConnections()->updateOrCreate(
            [
                'kind' => InstanceConnection::KIND_SERVER,
                'base_url' => $baseUrl,
            ],
            [
                'name' => $this->connectionName($attributes['name'] ?? null, $baseUrl),
            ],
        );

        $this->activate($connection, $session);

        return $connection;
    }

    /**
     * @param  array{name: string|null, base_url: string}  $attributes
     */
    public function updateServerConnection(InstanceConnection $connection, array $attributes): InstanceConnection
    {
        $baseUrl = $this->normalizeUrl($attributes['base_url']);
        $baseUrlChanged = $connection->base_url !== $baseUrl;

        $payload = [
            'name' => $this->connectionName($attributes['name'] ?? null, $baseUrl),
            'base_url' => $baseUrl,
        ];

        if ($baseUrlChanged) {
            $payload['session_context'] = null;
            $payload['last_authenticated_at'] = null;
        }

        $connection->forceFill($payload)->save();

        return $connection;
    }

    public function updateCurrentInstanceConnection(InstanceConnection $connection, string $name): InstanceConnection
    {
        $connection->forceFill([
            'name' => trim($name) !== '' ? trim($name) : $this->applicationConnectionName(),
        ])->save();

        return $connection;
    }

    public function activate(InstanceConnection $connection, Session $session): void
    {
        $session->put(self::ACTIVE_CONNECTION_SESSION_KEY, $connection->getKey());

        $attributes = [
            'last_used_at' => now(),
        ];

        if ($connection->kind === InstanceConnection::KIND_CURRENT_INSTANCE && $connection->last_authenticated_at === null) {
            $attributes['last_authenticated_at'] = now();
        }

        $connection->forceFill($attributes)->save();
    }

    public function ensureCurrentInstanceConnection(User $user, string $currentInstanceUrl): InstanceConnection
    {
        $connection = $user->instanceConnections()->firstOrCreate(
            [
                'kind' => InstanceConnection::KIND_CURRENT_INSTANCE,
                'base_url' => $this->normalizeUrl($currentInstanceUrl),
            ],
            [
                'name' => $this->applicationConnectionName(),
                'last_authenticated_at' => now(),
                'last_used_at' => now(),
            ],
        );

        return $connection;
    }

    /**
     * @param  array<string, mixed>  $sessionContext
     */
    public function rememberServerAuthentication(User $user, InstanceConnection $connection, array $sessionContext, Session $session): void
    {
        if ((int) $connection->user_id !== (int) $user->getKey()) {
            abort(404);
        }

        $session->put(self::ACTIVE_CONNECTION_SESSION_KEY, $connection->getKey());

        $connection->forceFill([
            'session_context' => $sessionContext,
            'last_authenticated_at' => now(),
            'last_used_at' => now(),
        ])->save();
    }

    public function clearActiveConnection(Session $session): void
    {
        $session->forget(self::ACTIVE_CONNECTION_SESSION_KEY);
    }

    private function normalizeUrl(string $url): string
    {
        $normalized = parse_url(trim($url));

        if (! is_array($normalized) || ! isset($normalized['scheme'], $normalized['host'])) {
            return rtrim(trim($url), '/');
        }

        $scheme = strtolower($normalized['scheme']);
        $host = strtolower($normalized['host']);
        $port = isset($normalized['port']) ? ':'.$normalized['port'] : '';
        $path = trim((string) ($normalized['path'] ?? ''), '/');

        return sprintf(
            '%s://%s%s%s',
            $scheme,
            $host,
            $port,
            $path !== '' ? '/'.$path : '',
        );
    }

    private function connectionName(?string $name, string $baseUrl): string
    {
        $trimmedName = trim((string) $name);

        if ($trimmedName !== '') {
            return $trimmedName;
        }

        $host = parse_url($baseUrl, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return 'Server connection';
        }

        return str($host)
            ->replace(['.test', '.local', '.localhost'], '')
            ->replace(['-', '_', '.'], ' ')
            ->title()
            ->value();
    }

    private function applicationConnectionName(): string
    {
        return (string) config('app.name', 'Katra');
    }
}
