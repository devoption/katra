<?php

use App\Mcp\Servers\KatraServer;
use App\Mcp\Tools\DescribeWorkspace;

test('the katra mcp server exposes a lightweight workspace description tool', function () {
    KatraServer::tool(DescribeWorkspace::class)
        ->assertOk()
        ->assertName('describe-workspace')
        ->assertDescription('Summarize the current Katra workspace configuration and MCP role.')
        ->assertSee([
            config('app.name'),
            config('app.url'),
            'interoperability layer',
            'not the center',
            'desktop-local',
        ]);
});
