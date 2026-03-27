<?php

namespace App\Support\Connections;

use App\Models\InstanceConnection;
use App\Models\User;

class ViewerIdentityResolver
{
    /**
     * @return array{name: string, email: string, initials: string}
     */
    public function resolve(?User $viewer, InstanceConnection $activeConnection): array
    {
        $remoteIdentity = $activeConnection->kind === InstanceConnection::KIND_SERVER
            ? data_get($activeConnection->session_context, 'user')
            : null;
        $remoteEmail = $activeConnection->kind === InstanceConnection::KIND_SERVER
            ? data_get($activeConnection->session_context, 'email')
            : null;
        $remoteName = data_get($remoteIdentity, 'name');

        if ((! is_string($remoteName) || $remoteName === '') && is_string($remoteEmail) && $remoteEmail !== '') {
            $remoteName = $this->nameFromEmail($remoteEmail);
        }

        $name = $remoteName
            ?: $viewer?->name
            ?: 'Katra User';

        $email = data_get($remoteIdentity, 'email')
            ?: $remoteEmail
            ?: $viewer?->email
            ?: 'katra@example.test';

        $initials = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $segment): string => strtoupper(substr($segment, 0, 1)))
            ->implode('');

        return [
            'name' => $name,
            'email' => $email,
            'initials' => $initials !== '' ? $initials : 'K',
        ];
    }

    private function nameFromEmail(string $email): string
    {
        $localPart = (string) str($email)->before('@');
        $segments = preg_split('/[._-]+/', $localPart) ?: [];
        $segments = array_values(array_filter(array_map(
            fn (string $segment): string => str($segment)->title()->value(),
            $segments,
        )));

        $firstName = $segments[0] ?? 'Remote';
        $lastName = count($segments) > 1 ? implode(' ', array_slice($segments, 1)) : 'User';

        return trim($firstName.' '.$lastName);
    }
}
