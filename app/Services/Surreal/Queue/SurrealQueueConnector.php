<?php

namespace App\Services\Surreal\Queue;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Queue\Connectors\ConnectorInterface;

class SurrealQueueConnector implements ConnectorInterface
{
    public function __construct(
        private readonly ConnectionResolverInterface $connections,
    ) {}

    public function connect(array $config): SurrealQueue
    {
        return new SurrealQueue(
            $this->connections->connection($config['connection'] ?? 'surreal'),
            $config['table'],
            $config['queue'],
            $config['retry_after'] ?? 60,
            $config['after_commit'] ?? null,
        );
    }
}
