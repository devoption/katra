<?php

use App\Services\Surreal\Query\SurrealQueryBuilder;
use App\Services\Surreal\Schema\SurrealSchemaConnection;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;

test('single-row inserts can contain array values without being treated as bulk inserts', function () {
    $builder = (new ReflectionClass(SurrealQueryBuilder::class))->newInstanceWithoutConstructor();
    $prepareInsertValues = new ReflectionMethod(SurrealQueryBuilder::class, 'prepareInsertValues');

    $prepareInsertValues->setAccessible(true);

    $records = $prepareInsertValues->invoke($builder, [
        'name' => 'example',
        'meta' => [
            'channel' => 'desktop',
            'participants' => ['user', 'agent'],
        ],
    ]);

    expect($records)->toBe([
        [
            'name' => 'example',
            'meta' => [
                'channel' => 'desktop',
                'participants' => ['user', 'agent'],
            ],
        ],
    ]);
});

test('surreal operators are whitelisted before query compilation', function () {
    $connection = (new ReflectionClass(SurrealSchemaConnection::class))->newInstanceWithoutConstructor();
    $normalizeOperator = new ReflectionMethod(SurrealSchemaConnection::class, 'normalizeOperator');

    $normalizeOperator->setAccessible(true);

    expect($normalizeOperator->invoke($connection, ' like '))->toBe('LIKE');

    expect(fn () => $normalizeOperator->invoke($connection, '= 1; DELETE users'))
        ->toThrow(RuntimeException::class, 'does not support the [= 1; DELETE users] operator');
});

test('surreal select column lists are projected when specific columns are requested', function () {
    $connection = (new ReflectionClass(SurrealSchemaConnection::class))->newInstanceWithoutConstructor();
    $compileSelectColumns = new ReflectionMethod(SurrealSchemaConnection::class, 'compileSelectColumns');

    $compileSelectColumns->setAccessible(true);

    expect($compileSelectColumns->invoke($connection, ['id', 'email']))->toBe('id, email')
        ->and($compileSelectColumns->invoke($connection, ['*']))->toBe('*');
});

test('surreal query builder resolves expression columns with the active grammar', function () {
    $builder = (new ReflectionClass(SurrealQueryBuilder::class))->newInstanceWithoutConstructor();
    $resolveColumns = new ReflectionMethod(SurrealQueryBuilder::class, 'resolveColumns');

    $resolveColumns->setAccessible(true);
    $builder->grammar = (new ReflectionClass(Grammar::class))->newInstanceWithoutConstructor();

    $columns = $resolveColumns->invoke($builder, [
        new Expression('count(*) as aggregate'),
        'email',
    ]);

    expect($columns)->toBe([
        'count(*) as aggregate',
        'email',
    ]);
});

test('surreal datetime encoding normalizes DateTimeInterface values to utc', function () {
    $connection = (new ReflectionClass(SurrealSchemaConnection::class))->newInstanceWithoutConstructor();
    $encodeValue = new ReflectionMethod(SurrealSchemaConnection::class, 'encodeValue');

    $encodeValue->setAccessible(true);

    $encoded = $encodeValue->invoke(
        $connection,
        'created_at',
        CarbonImmutable::parse('2026-03-24 09:15:00', 'America/New_York'),
    );

    expect($encoded)->toBe("d'2026-03-24T13:15:00+00:00'");
});

test('upsert with no update columns behaves like insert or ignore', function () {
    $connection = Mockery::mock(SurrealSchemaConnection::class);
    $grammar = (new ReflectionClass(Grammar::class))->newInstanceWithoutConstructor();
    $processor = new Processor;

    $connection->shouldReceive('insertOrIgnoreRecord')
        ->once()
        ->with('features', ['name' => 'ui.desktop.mvp-shell'])
        ->andReturnTrue();

    $builder = new SurrealQueryBuilder($connection, $grammar, $processor);
    $builder->from('features');

    expect($builder->upsert([
        'name' => 'ui.desktop.mvp-shell',
    ], ['name'], []))->toBe(1);
});
