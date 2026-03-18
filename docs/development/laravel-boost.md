# Laravel Boost Setup

## Purpose

Katra keeps the shared Laravel Boost project configuration in the repository, but developer-specific MCP client files stay local.

This is intentional because Boost can generate machine-specific paths for tools such as Herd and for local project checkouts. Those files work well on an individual machine, but they should not be committed to the repository.

## What Is Committed

The repository keeps the portable Boost baseline in git:

- `boost.json`
- shared guideline and skill files generated for supported agents

## What Stays Local

The following files are ignored and should be generated or maintained locally:

- `.mcp.json`
- `opencode.json`
- `.codex/config.toml`
- `.cursor/mcp.json`
- `.gemini/settings.json`
- `.amp/settings.json`
- `.junie/mcp/mcp.json`

## Local Setup

After cloning the repository and installing dependencies, run:

```bash
php artisan boost:install --mcp
```

This installs local MCP client configuration for the tools you use.

If you want to refresh your local Boost configuration later, run:

```bash
php artisan boost:update
```

## Notes

- If you use Herd MCP, Boost may generate absolute local paths. That is expected for local-only config.
- Do not commit your generated MCP client files.
- If a tool needs custom local adjustments, keep those changes in your ignored local files.
