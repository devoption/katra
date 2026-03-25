<?php

namespace App\Support\Connections;

use App\Models\InstanceConnection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class RemoteInstanceAuthenticator
{
    /**
     * @param  array{email: string, password: string}  $credentials
     * @return array{
     *     base_url: string,
     *     email: string,
     *     user: array{name: string, email: string, first_name: string, last_name: string}|null,
     *     cookies: array<string, string>
     * }
     */
    public function authenticate(InstanceConnection $connection, array $credentials): array
    {
        if (! is_string($connection->base_url) || $connection->base_url === '') {
            throw ValidationException::withMessages([
                'server' => 'This connection does not have a valid server URL yet.',
            ]);
        }

        $loginUrl = rtrim($connection->base_url, '/').'/login';
        $homeUrl = rtrim($connection->base_url, '/').'/';
        $profileUrl = rtrim($connection->base_url, '/').'/_katra/profile';

        $loginPageResponse = Http::accept('text/html,application/xhtml+xml')
            ->withoutRedirecting()
            ->get($loginUrl);

        if (! $loginPageResponse->successful()) {
            throw ValidationException::withMessages([
                'server' => 'Katra could not reach that server login screen.',
            ]);
        }

        $csrfToken = $this->extractCsrfToken($loginPageResponse);

        if ($csrfToken === null) {
            throw ValidationException::withMessages([
                'server' => 'The selected server did not return a compatible Katra login form.',
            ]);
        }

        $cookies = $this->mergeCookies([], $loginPageResponse);

        $loginResponse = Http::accept('text/html,application/xhtml+xml')
            ->withoutRedirecting()
            ->withHeaders([
                'Cookie' => $this->cookieHeader($cookies),
                'Referer' => $loginUrl,
            ])
            ->asForm()
            ->post($loginUrl, [
                '_token' => $csrfToken,
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ]);

        $cookies = $this->mergeCookies($cookies, $loginResponse);

        if ($loginResponse->clientError() || $loginResponse->serverError()) {
            throw ValidationException::withMessages([
                'server' => 'The selected server could not complete the sign-in request.',
            ]);
        }

        $verificationResponse = Http::accept('text/html,application/xhtml+xml')
            ->withoutRedirecting()
            ->withHeaders([
                'Cookie' => $this->cookieHeader($cookies),
            ])
            ->get($homeUrl);

        if (! $this->isAuthenticatedResponse($verificationResponse)) {
            throw ValidationException::withMessages([
                'email' => 'Those server credentials were not accepted.',
            ]);
        }

        $profileResponse = Http::acceptJson()
            ->withoutRedirecting()
            ->withHeaders([
                'Cookie' => $this->cookieHeader($cookies),
            ])
            ->get($profileUrl);

        return [
            'base_url' => $connection->base_url,
            'email' => $credentials['email'],
            'user' => $this->extractUserProfile($profileResponse),
            'cookies' => $cookies,
        ];
    }

    /**
     * @param  array<string, string>  $cookies
     * @return array<string, string>
     */
    private function mergeCookies(array $cookies, Response $response): array
    {
        foreach ($this->cookieHeaders($response) as $header) {
            $pair = trim(explode(';', $header, 2)[0]);

            if ($pair === '' || ! str_contains($pair, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $pair, 2);

            if ($name === '') {
                continue;
            }

            $cookies[$name] = $value;
        }

        return $cookies;
    }

    /**
     * @return array<int, string>
     */
    private function cookieHeaders(Response $response): array
    {
        $headers = $response->headers();
        $cookieHeaders = $headers['Set-Cookie'] ?? $headers['set-cookie'] ?? [];

        return array_values(array_filter(
            is_array($cookieHeaders) ? $cookieHeaders : [$cookieHeaders],
            fn (mixed $header): bool => is_string($header) && $header !== '',
        ));
    }

    private function extractCsrfToken(Response $response): ?string
    {
        $matched = preg_match('/name="_token"[^>]*value="([^"]+)"/', $response->body(), $matches);

        if ($matched !== 1) {
            return null;
        }

        return html_entity_decode($matches[1], ENT_QUOTES);
    }

    /**
     * @param  array<string, string>  $cookies
     */
    private function cookieHeader(array $cookies): string
    {
        return collect($cookies)
            ->map(fn (string $value, string $name): string => sprintf('%s=%s', $name, $value))
            ->implode('; ');
    }

    private function isAuthenticatedResponse(Response $response): bool
    {
        if ($response->successful()) {
            return true;
        }

        if (! $response->redirect()) {
            return false;
        }

        return ! str_contains($response->header('Location'), '/login');
    }

    /**
     * @return array{name: string, email: string, first_name: string, last_name: string}|null
     */
    private function extractUserProfile(Response $response): ?array
    {
        if (! $response->successful()) {
            return null;
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            return null;
        }

        $name = data_get($payload, 'name');
        $email = data_get($payload, 'email');
        $firstName = data_get($payload, 'first_name');
        $lastName = data_get($payload, 'last_name');

        if (! is_string($name) || ! is_string($email) || ! is_string($firstName) || ! is_string($lastName)) {
            return null;
        }

        return [
            'name' => $name,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ];
    }
}
