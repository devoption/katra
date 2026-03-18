<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['id', 'name', 'summary', 'status'])]
class Workspace extends SurrealModel
{
    protected $table = 'workspaces';

    public static function desktopPreview(): self
    {
        $workspace = static::find('desktop-preview');

        if ($workspace !== null) {
            return $workspace;
        }

        return static::create([
            'id' => 'desktop-preview',
            'name' => 'Desktop Preview Workspace',
            'summary' => 'A Surreal-backed workspace record created to prove the first Katra persistence layer.',
            'status' => 'active',
        ]);
    }
}
