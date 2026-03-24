<?php

use App\Services\Surreal\Query\SurrealQueryBuilder;
use App\Services\Surreal\Schema\SurrealSchemaConnection;

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
