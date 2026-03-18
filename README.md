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
- SurrealDB v3, embedded-first
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

Katra v2 is an active rewrite. The repository is being reset and rebuilt in small, reviewable pull requests. The proof of concept is preserved at [`v1.0.0`](https://github.com/devoption/katra/tree/v1.0.0) for historical reference and is not being actively developed.

## Planning Docs

- [Katra v2 Overview](docs/v2-overview.md)
- [Katra v2 Product and Architecture Principles](docs/architecture/v2-product-and-architecture.md)
- [Katra Brand Foundation](docs/brand/README.md)
- [Issue #13: Katra v2 product vision and architecture principles](https://github.com/devoption/katra/issues/13)

## Contributing

Contributions are welcome as the rewrite takes shape. For now, the best place to follow the work is the issue tracker and the planning docs linked above.

## License

Katra is open-sourced software licensed under the [MIT License](LICENSE).
