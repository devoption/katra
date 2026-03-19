<?php

use App\Services\Surreal\SurrealCliClient;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

test('it prefers a bundled surreal binary from the nativephp extras path', function () {
    $extrasPath = storage_path('framework/testing/nativephp-extras-'.Str::uuid());
    $bundledBinary = $extrasPath.'/surreal/bin/surreal';

    File::ensureDirectoryExists(dirname($bundledBinary));
    File::put($bundledBinary, "#!/bin/sh\nexit 0\n");
    chmod($bundledBinary, 0755);

    try {
        $client = new SurrealCliClient(
            configuredBinary: 'surreal',
            extrasPath: $extrasPath,
            bundledBinaryRelativePath: 'surreal/bin/surreal',
        );

        expect($client->binary())->toBe($bundledBinary)
            ->and($client->usesBundledBinary())->toBeTrue();
    } finally {
        File::deleteDirectory($extrasPath);
    }
});

test('it reports the bundled lookup path when no surreal binary is available', function () {
    $extrasPath = storage_path('framework/testing/nativephp-extras-'.Str::uuid());
    $missingBinary = 'surreal-missing-binary-for-test';

    try {
        $client = new SurrealCliClient(
            configuredBinary: $missingBinary,
            extrasPath: $extrasPath,
            bundledBinaryRelativePath: 'surreal/bin/surreal',
        );

        expect(fn () => $client->isReady('ws://127.0.0.1:18001'))
            ->toThrow(RuntimeException::class, sprintf('Checked bundled NativePHP extras path [%s].', $extrasPath.'/surreal/bin/surreal'));
    } finally {
        File::deleteDirectory($extrasPath);
    }
});

test('it reports the configured binary path when the surreal binary is missing from an explicit path', function () {
    $extrasPath = storage_path('framework/testing/nativephp-extras-'.Str::uuid());
    $missingBinary = $extrasPath.'/custom/surreal';

    try {
        $client = new SurrealCliClient(
            configuredBinary: $missingBinary,
            extrasPath: null,
            bundledBinaryRelativePath: null,
        );

        expect(fn () => $client->isReady('ws://127.0.0.1:18001'))
            ->toThrow(RuntimeException::class, sprintf('Checked configured Surreal binary path [%s].', $missingBinary));
    } finally {
        File::deleteDirectory($extrasPath);
    }
});

test('it honors an explicitly configured surreal binary before the bundled binary', function () {
    $workspaceRoot = storage_path('framework/testing/nativephp-extras-'.Str::uuid());
    $configuredBinary = $workspaceRoot.'/custom/surreal';
    $bundledBinary = $workspaceRoot.'/extras/surreal/bin/surreal';

    File::ensureDirectoryExists(dirname($configuredBinary));
    File::put($configuredBinary, "#!/bin/sh\nexit 0\n");
    chmod($configuredBinary, 0755);

    File::ensureDirectoryExists(dirname($bundledBinary));
    File::put($bundledBinary, "#!/bin/sh\nexit 0\n");
    chmod($bundledBinary, 0755);

    try {
        config()->set('surreal.binary', $configuredBinary);

        $client = new SurrealCliClient(
            configuredBinary: null,
            extrasPath: $workspaceRoot.'/extras',
            bundledBinaryRelativePath: 'surreal/bin/surreal',
        );

        expect($client->binary())->toBe($configuredBinary)
            ->and($client->usesBundledBinary())->toBeFalse();
    } finally {
        File::deleteDirectory($workspaceRoot);
        config()->set('surreal.binary', 'surreal');
    }
});
