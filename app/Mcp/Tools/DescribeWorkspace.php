<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Summarize the current Katra workspace configuration and MCP role.')]
#[IsReadOnly]
#[IsIdempotent]
class DescribeWorkspace extends Tool
{
    public function handle(Request $request): Response
    {
        $workspaceName = (string) config('app.name');
        $workspaceUrl = (string) config('app.url');
        $runtimeTargets = Arr::join([
            'desktop-local',
            'server',
            'container',
            'kubernetes-oriented',
        ], ', ', ', and ');

        return Response::text(
            "{$workspaceName} is available at {$workspaceUrl}. ".
            'Laravel MCP is configured here as an interoperability layer for Katra v2, not the center of the product architecture. '.
            "The current planned runtime targets are {$runtimeTargets}."
        );
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            //
        ];
    }
}
