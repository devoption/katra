<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\DescribeWorkspace;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Katra MCP Server')]
#[Version('0.0.1')]
#[Instructions('Use this server for lightweight Katra interoperability tasks. It is intended to support integrations and tooling, not to become the center of Katra\'s product architecture.')]
class KatraServer extends Server
{
    protected array $tools = [
        DescribeWorkspace::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
