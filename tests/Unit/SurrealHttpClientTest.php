<?php

use App\Services\Surreal\SurrealHttpClient;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

test('it sends surreal queries over the http sql endpoint', function () {
    Http::fake([
        'http://127.0.0.1:18001/sql' => Http::response([
            [
                'status' => 'OK',
                'result' => [
                    ['id' => 'workspaces:test'],
                ],
            ],
        ]),
    ]);

    $results = new SurrealHttpClient(app(Factory::class))->runQuery(
        endpoint: 'ws://127.0.0.1:18001',
        namespace: 'katra',
        database: 'workspace',
        username: 'root',
        password: 'root',
        query: 'SELECT * FROM workspaces;',
    );

    Http::assertSentCount(3);
    Http::assertSent(function ($request): bool {
        return $request->url() === 'http://127.0.0.1:18001/sql'
            && $request->hasHeader('Surreal-NS', 'katra')
            && $request->hasHeader('Surreal-DB', 'workspace')
            && $request->body() === 'SELECT * FROM workspaces;';
    });

    expect($results)->toHaveCount(1)
        ->and($results[0][0]['id'] ?? null)->toBe('workspaces:test');
});

test('it probes surreal health over http for websocket endpoints', function () {
    Http::fake([
        'http://127.0.0.1:18001/health' => Http::response(null, 200),
    ]);

    expect(new SurrealHttpClient(app(Factory::class))->isReady('ws://127.0.0.1:18001'))->toBeTrue();

    Http::assertSent(fn ($request): bool => $request->url() === 'http://127.0.0.1:18001/health');
});
