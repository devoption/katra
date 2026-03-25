<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthenticateRemoteInstanceConnectionRequest;
use App\Http\Requests\ConnectServerRequest;
use App\Http\Requests\StoreInstanceConnectionRequest;
use App\Http\Requests\UpdateInstanceConnectionRequest;
use App\Models\InstanceConnection;
use App\Models\User;
use App\Support\Connections\InstanceConnectionManager;
use App\Support\Connections\RemoteInstanceAuthenticator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstanceConnectionController extends Controller
{
    private const PENDING_SERVER_URL_SESSION_KEY = 'server_connection.pending_url';

    private const PENDING_SERVER_NAME_SESSION_KEY = 'server_connection.pending_name';

    public function showServerConnect(Request $request): View
    {
        return view('auth.connect-server', [
            'pendingServerUrl' => $request->session()->get(self::PENDING_SERVER_URL_SESSION_KEY),
            'pendingServerName' => $request->session()->get(self::PENDING_SERVER_NAME_SESSION_KEY),
        ]);
    }

    public function prepareServerLogin(
        ConnectServerRequest $request,
        InstanceConnectionManager $connectionManager,
    ): RedirectResponse {
        $serverUrl = $connectionManager->normalizeUrl($request->validated('server_url'));

        $request->session()->put(self::PENDING_SERVER_URL_SESSION_KEY, $serverUrl);
        $request->session()->put(self::PENDING_SERVER_NAME_SESSION_KEY, $this->connectionNameFromUrl($serverUrl));

        return to_route('server.connect');
    }

    public function store(StoreInstanceConnectionRequest $request, InstanceConnectionManager $connectionManager): RedirectResponse
    {
        $connection = $connectionManager->createServerConnection(
            $request->user(),
            $request->validated(),
            $request->session(),
        );

        if ($connection->is_authenticated) {
            return to_route('home');
        }

        return to_route('connections.connect', $connection);
    }

    public function update(
        UpdateInstanceConnectionRequest $request,
        InstanceConnection $instanceConnection,
        InstanceConnectionManager $connectionManager,
    ): RedirectResponse {
        $this->ensureConnectionOwnership($request, $instanceConnection);

        if ($instanceConnection->is_current_instance) {
            $connectionManager->updateCurrentInstanceConnection(
                $instanceConnection,
                $request->validated('name'),
            );

            return to_route('home');
        }

        $connectionManager->updateServerConnection($instanceConnection, $request->validated());

        if (
            (int) $request->session()->get('instance_connection.active_id') === (int) $instanceConnection->getKey()
            && ! $instanceConnection->fresh()->is_authenticated
        ) {
            return to_route('connections.connect', $instanceConnection);
        }

        return to_route('home');
    }

    public function activate(Request $request, InstanceConnection $instanceConnection, InstanceConnectionManager $connectionManager): RedirectResponse
    {
        $this->ensureConnectionOwnership($request, $instanceConnection);

        $connectionManager->activate($instanceConnection, $request->session());

        if ($instanceConnection->kind === InstanceConnection::KIND_SERVER && ! $instanceConnection->is_authenticated) {
            return to_route('connections.connect', $instanceConnection);
        }

        return to_route('home');
    }

    public function destroy(
        Request $request,
        InstanceConnection $instanceConnection,
        InstanceConnectionManager $connectionManager,
    ): RedirectResponse {
        $this->ensureConnectionOwnership($request, $instanceConnection);
        $this->ensureServerConnection($instanceConnection);

        $isActiveConnection = (int) $request->session()->get('instance_connection.active_id') === (int) $instanceConnection->getKey();
        $connectionOwner = $instanceConnection->user;

        $instanceConnection->delete();

        if (! $isActiveConnection) {
            return to_route('home');
        }

        $fallbackConnection = $connectionOwner->instanceConnections()
            ->where('kind', InstanceConnection::KIND_CURRENT_INSTANCE)
            ->latest('last_used_at')
            ->first()
            ?? $connectionManager->ensureCurrentInstanceConnection($connectionOwner, $request->root());

        Auth::login($fallbackConnection->user);
        $connectionManager->activate($fallbackConnection, $request->session());

        return to_route('home');
    }

    public function connect(Request $request, InstanceConnection $instanceConnection): View
    {
        $this->ensureConnectionOwnership($request, $instanceConnection);

        return view('auth.connect-server', [
            'instanceConnection' => $instanceConnection,
        ]);
    }

    public function authenticate(
        AuthenticateRemoteInstanceConnectionRequest $request,
        InstanceConnection $instanceConnection,
        InstanceConnectionManager $connectionManager,
        RemoteInstanceAuthenticator $remoteInstanceAuthenticator,
    ): RedirectResponse {
        $this->ensureConnectionOwnership($request, $instanceConnection);

        $credentials = $request->validated();
        $sessionContext = $remoteInstanceAuthenticator->authenticate($instanceConnection, $credentials);

        $connectionManager->rememberServerAuthentication(
            $request->user(),
            $instanceConnection,
            $sessionContext,
            $request->session(),
        );

        return to_route('home');
    }

    public function authenticateGuestServer(
        AuthenticateRemoteInstanceConnectionRequest $request,
        InstanceConnectionManager $connectionManager,
        RemoteInstanceAuthenticator $remoteInstanceAuthenticator,
    ): RedirectResponse {
        $serverUrl = $request->session()->get(self::PENDING_SERVER_URL_SESSION_KEY);

        if (! is_string($serverUrl) || $serverUrl === '') {
            return to_route('server.connect');
        }

        $connectionName = $request->session()->get(self::PENDING_SERVER_NAME_SESSION_KEY, $this->connectionNameFromUrl($serverUrl));

        $temporaryConnection = new InstanceConnection([
            'name' => $connectionName,
            'kind' => InstanceConnection::KIND_SERVER,
            'base_url' => $serverUrl,
        ]);

        $credentials = $request->validated();
        $sessionContext = $remoteInstanceAuthenticator->authenticate($temporaryConnection, $credentials);
        $user = $this->resolveRemoteUser(
            $credentials['email'],
            is_array(data_get($sessionContext, 'user')) ? data_get($sessionContext, 'user') : null,
        );

        Auth::login($user);

        $connection = $connectionManager->createServerConnection(
            $user,
            [
                'name' => $connectionName,
                'base_url' => $serverUrl,
            ],
            $request->session(),
        );

        $connectionManager->rememberServerAuthentication(
            $user,
            $connection,
            $sessionContext,
            $request->session(),
        );

        $request->session()->forget([
            self::PENDING_SERVER_URL_SESSION_KEY,
            self::PENDING_SERVER_NAME_SESSION_KEY,
        ]);

        return to_route('home');
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    private function connectionNameFromUrl(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return 'Server connection';
        }

        return str($host)
            ->replace(['.test', '.local', '.localhost'], '')
            ->replace(['-', '_', '.'], ' ')
            ->title()
            ->value();
    }

    private function ensureServerConnection(InstanceConnection $instanceConnection): void
    {
        if ($instanceConnection->kind !== InstanceConnection::KIND_SERVER) {
            abort(404);
        }
    }

    private function ensureConnectionOwnership(Request $request, InstanceConnection $instanceConnection): void
    {
        if ((int) $instanceConnection->user_id !== (int) $request->user()->getKey()) {
            abort(404);
        }
    }

    /**
     * @param  array{name?: string, email?: string, first_name?: string, last_name?: string}|null  $remoteIdentity
     */
    private function resolveRemoteUser(string $email, ?array $remoteIdentity = null): User
    {
        $existingUser = User::query()->where('email', $email)->first();

        if ($existingUser instanceof User) {
            $identity = $this->remoteIdentityPayload($email, $remoteIdentity);

            $existingUser->forceFill([
                'first_name' => $identity['first_name'],
                'last_name' => $identity['last_name'],
                'name' => $identity['name'],
            ])->save();

            return $existingUser;
        }

        $nameParts = $this->remoteIdentityPayload($email, $remoteIdentity);

        return User::query()->create([
            'first_name' => $nameParts['first_name'],
            'last_name' => $nameParts['last_name'],
            'name' => $nameParts['name'],
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Str::random(40),
        ]);
    }

    /**
     * @return array{first_name: string, last_name: string, name: string, email: string}
     */
    private function remoteIdentityPayload(string $email, ?array $remoteIdentity = null): array
    {
        $firstName = is_string(data_get($remoteIdentity, 'first_name')) && data_get($remoteIdentity, 'first_name') !== ''
            ? data_get($remoteIdentity, 'first_name')
            : null;
        $lastName = is_string(data_get($remoteIdentity, 'last_name')) && data_get($remoteIdentity, 'last_name') !== ''
            ? data_get($remoteIdentity, 'last_name')
            : null;
        $name = is_string(data_get($remoteIdentity, 'name')) && data_get($remoteIdentity, 'name') !== ''
            ? data_get($remoteIdentity, 'name')
            : null;

        if ($firstName === null || $lastName === null || $name === null) {
            $derivedNameParts = $this->namePartsFromEmail($email);

            $firstName ??= $derivedNameParts['first_name'];
            $lastName ??= $derivedNameParts['last_name'];
            $name ??= $derivedNameParts['name'];
        }

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => $name,
            'email' => $email,
        ];
    }

    /**
     * @return array{first_name: string, last_name: string, name: string}
     */
    private function namePartsFromEmail(string $email): array
    {
        $localPart = (string) str($email)->before('@');
        $segments = preg_split('/[._-]+/', $localPart) ?: [];
        $segments = array_values(array_filter(array_map(
            fn (string $segment): string => str($segment)->title()->value(),
            $segments,
        )));

        $firstName = $segments[0] ?? 'Remote';
        $lastName = count($segments) > 1 ? implode(' ', array_slice($segments, 1)) : 'User';

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => trim($firstName.' '.$lastName),
        ];
    }
}
