<p align="center">
  <img src="docs/brand/katra-logo.svg" alt="Katra" width="360">
</p>

Katra is an open source, graph-native AI workspace for conversations, tasks, and collaborative intelligence.

> [!WARNING]
> `main` now tracks the in-progress Katra v2 rewrite.
> Looking for the original proof of concept? See [`v1.0.0`](https://github.com/devoption/katra/tree/v1.0.0).

## Katra v2

Katra v2 is being rebuilt as a local-first Laravel application that can run in multiple environments. NativePHP is the first-class local shell, but the application is also intended to support server, container, and Kubernetes-style deployment targets.

The long-term direction is a graph-native system where conversations, tasks, decisions, artifacts, and related context become first-class objects instead of disposable chat history. That foundation is intended to support multi-user and multi-model collaboration, subagents, GraphRAG-style retrieval, and real-time project management.

## Planned Stack

- Laravel 13
- NativePHP
- SurrealDB v3 with a desktop-embedded and server-remote runtime strategy
- Laravel AI
- Laravel MCP
- Laravel Fortify
- Livewire
- Tailwind CSS
- Pest
- Laravel Boost for local development

## Runtime Targets

- Local-first desktop experience through NativePHP
- Traditional Laravel server deployments
- Docker-based deployments
- Helm and Kubernetes-oriented deployments

## Current Status

Katra v2 is an active rewrite. The repository is being rebuilt in small, reviewable pull requests, and preview macOS desktop artifacts are now attached to GitHub Releases as the shell evolves. The proof of concept is preserved at [`v1.0.0`](https://github.com/devoption/katra/tree/v1.0.0) for historical reference and is not being actively developed.

## Desktop Preview

The current desktop shell is now installable as a real macOS app preview. It is still early, but the downloadable build already shows the direction clearly: durable rooms, a conversation-first center pane, a contextual right rail, and a local-first desktop shell that can grow into local and remote workflows.

| Dark Theme | Light Theme |
| --- | --- |
| ![Katra desktop shell in dark mode](docs/images/readme/katra-dark.png) | ![Katra desktop shell in light mode](docs/images/readme/katra-light.png) |

## Try Katra

There are two practical ways to try Katra today.

### Download A Desktop Preview

- Browse the [GitHub Releases](https://github.com/devoption/katra/releases) page and download the latest macOS desktop asset for your machine.
- Choose the architecture-specific asset that matches your Mac when it is available: `x64` for Intel, `arm64` for Apple Silicon.
- Desktop preview builds now bundle the local Surreal runtime instead of expecting a separate machine-local `surreal` CLI install.
- Recent tagged release builds are signed, notarized, and stapled for macOS distribution, though older preview tags may still require a manual `Open Anyway` flow.
- The app is still preview-quality even when the install path is trusted.

### Run From Source

```bash
composer setup
composer native:dev
```

That path installs dependencies, prepares the Laravel app, bootstraps NativePHP, and starts the local desktop development loop.

### Authentication

Katra now uses Laravel Fortify for the first authentication foundation.

- Create an account at `/register`, then sign in at `/login`.
- The desktop shell route at `/` is now authentication-protected.
- Password recovery is available through `/forgot-password`.
- Make sure your local database migrations are current before you try the auth flow:

```bash
php artisan migrate
```

- If you are testing password reset locally and want to inspect the reset link without sending mail, use a local-safe mailer such as `MAIL_MAILER=log`.

### Configure AI Providers

The Laravel AI SDK is installed and its conversation storage migrations are part of the application now.

- For hosted model access, set `OPENAI_API_KEY` and leave `AI_DEFAULT_PROVIDER=openai`.
- For local model experiments, set `AI_DEFAULT_PROVIDER=ollama` and point `OLLAMA_BASE_URL` at your local Ollama instance.
- Additional provider keys and per-capability defaults are available in [config/ai.php](config/ai.php) if you want to swap providers later.
- Some capabilities still default to specific providers like Gemini or Cohere, so if you want everything to follow your primary provider you should update the operation-specific defaults in `config/ai.php` as well.

The current AI foundation test uses agent fakes, so the repo test suite does not require live provider credentials just to verify the integration.

### Surreal-Backed Migrations

Katra now includes a first Laravel-compatible Surreal schema driver for migration work.

- Use `Schema::connection('surreal')` inside migrations when you want to target Surreal-backed application data.
- You can also set `DB_CONNECTION=surreal` and run Laravel migrations, migration status, and `migrate:fresh` directly against SurrealDB.
- The current slice is intentionally narrow: table creation, field creation, field removal, and table removal are supported for common Katra field types.
- This is still not full SQL-driver parity yet, but it is enough for Katra's current migration set and for Surreal-backed application schema work without relying on SQLite migration bookkeeping.

### Surreal-Backed Cache

Katra also supports Laravel's cache abstraction against SurrealDB by pointing the database cache store at the `surreal` connection.

- Set `CACHE_STORE=surreal` when you want the main cache store to live in SurrealDB.
- The dedicated `surreal` cache store alias uses Laravel's built-in `database` cache driver with the `surreal` connection, so application code can keep using normal `Cache` APIs.
- Override the table and connection names with `SURREAL_CACHE_CONNECTION`, `SURREAL_CACHE_TABLE`, `SURREAL_CACHE_LOCK_CONNECTION`, and `SURREAL_CACHE_LOCK_TABLE` if you need something other than the defaults.
- Make sure the cache tables are migrated on the Surreal connection before you rely on the store:

```bash
php artisan migrate --database=surreal --path=database/migrations/0001_01_01_000001_create_cache_table.php
```

- Core cache operations like `get`, `put`, `add`, `many`, `forever`, `forget`, and `flush` are covered in the test suite against a real Surreal runtime.
- SQL-transaction-dependent limiter semantics are still treated as unsupported on Surreal-backed cache storage, so Katra keeps `CACHE_LIMITER=file` by default for Fortify throttling and other limiter middleware.

### Surreal-Backed Sessions

Katra now also exposes a dedicated `surreal` Laravel session driver backed by the framework's database session handler on the Surreal connection.

- Set `SESSION_DRIVER=surreal` to store Laravel sessions in SurrealDB.
- The driver defaults to the `surreal` connection, but you can still override the table and connection with `SESSION_CONNECTION` and `SESSION_TABLE` if needed.
- Make sure the sessions table exists on the Surreal connection before relying on this driver. Katra's current auth/session migration also creates the `users` and `password_reset_tokens` tables alongside `sessions`:

```bash
php artisan migrate --database=surreal --path=database/migrations/0001_01_01_000000_create_users_table.php
```

- Session read, write, update, and expiry behavior are covered in the test suite against a real Surreal runtime.
- This driver intentionally follows Laravel's normal database-session lifecycle, so expiry cleanup still relies on Laravel's standard session lottery / pruning behavior instead of Surreal-native TTL features.

### Surreal-Backed Queues

Katra now also exposes a first-class `surreal` Laravel queue connection for teams that want jobs to live in SurrealDB alongside the rest of the application state.

- Set `QUEUE_CONNECTION=surreal` to use the Surreal-backed queue connector.
- The connector defaults to the `surreal` database connection, but you can override the queue connection, table, queue name, and retry window with `SURREAL_QUEUE_CONNECTION`, `SURREAL_QUEUE_TABLE`, `SURREAL_QUEUE`, and `SURREAL_QUEUE_RETRY_AFTER`.
- If you also want failed job records in SurrealDB, set `QUEUE_FAILED_DRIVER=database-uuids` and `QUEUE_FAILED_DATABASE=surreal`.
- Make sure the queue tables exist on the Surreal connection before you start a worker. Katra's queue migration also creates `job_batches` and `failed_jobs` alongside `jobs`:

```bash
php artisan migrate --database=surreal --path=database/migrations/0001_01_01_000002_create_jobs_table.php
```

- Start a worker against the Surreal connection with `php artisan queue:work surreal`.
- Queue enqueue, reserve, complete, retry, and failed-job behavior are covered in the test suite against a real Surreal runtime.
- This connector currently uses optimistic job reservation instead of SQL row locks and database transactions, so it is best suited to Katra's current low-contention worker model rather than high-volume multi-worker contention scenarios.
- `after_commit` is not supported on the Surreal queue connection because the Surreal database connection does not expose SQL transaction semantics.

## Planning Docs

- [Katra v2 Overview](docs/v2-overview.md)
- [Katra v2 Product and Architecture Principles](docs/architecture/v2-product-and-architecture.md)
- [Katra Brand Foundation](docs/brand/README.md)
- [Issue #13: Katra v2 product vision and architecture principles](https://github.com/devoption/katra/issues/13)

## Contributing

Contributions are welcome as the rewrite takes shape. For now, the best place to follow the work is the issue tracker and the planning docs linked above.

Repository workflow, commit conventions, and release policy are documented in [CONTRIBUTING.md](CONTRIBUTING.md).

Optional local AI tooling setup is documented in [Laravel Boost Setup](docs/development/laravel-boost.md).
Native desktop bootstrap, release artifacts, and local shell setup are documented in [NativePHP Desktop Setup](docs/development/nativephp.md).

## License

Katra is open-sourced software licensed under the [MIT License](LICENSE).
