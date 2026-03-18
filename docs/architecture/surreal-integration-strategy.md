# SurrealDB Laravel Integration Strategy

## Purpose

This document defines the current SurrealDB integration strategy for Katra v2 after the initial connectivity spike in [PR #59](https://github.com/devoption/katra/pull/59).

The goal is to make the next SurrealDB issues easier to sequence and review by clarifying:

- the intended runtime model
- the Laravel integration layers
- the highest-risk incompatibilities and unknowns
- the package and module boundaries that should guide implementation

## Architectural Decision

Katra should support two SurrealDB runtime modes behind the same higher-level graph model:

- desktop mode: an embedded SurrealDB runtime owned by the NativePHP / Electron side of the application
- server mode: an external SurrealDB instance or cluster managed outside the Laravel process

Laravel should not assume that it embeds SurrealDB directly in-process. Instead, Laravel should depend on a stable data boundary that can be backed by either runtime.

This keeps the local-first desktop experience aligned with Katra's product direction while still supporting conventional server deployments and future clustered deployments.

## Why This Direction

The SurrealDB connectivity spike proved that Laravel can drive a local-first SurrealDB runtime and complete a write/read round-trip. It did not prove PHP-native in-process embedding.

That result is acceptable because the target product shape is:

- NativePHP desktop builds should keep storage local and self-contained
- server deployments should rely on standalone SurrealDB infrastructure
- the graph-native workspace model should not depend on one runtime topology

In practice, this means "embedded-first" for Katra should be understood as "embedded in the app runtime" rather than "embedded inside PHP specifically".

## Runtime Model

### Desktop Runtime

In desktop mode, the NativePHP / Electron runtime should own the embedded SurrealDB instance.

Preferred properties:

- data stored locally with `SurrealKV`
- lifecycle managed by the desktop shell
- fast local graph reads and writes
- no requirement for a separately installed database by the end user

Laravel should access this runtime through a local boundary such as:

- a localhost RPC or HTTP endpoint
- an internal bridge owned by the desktop shell
- another narrow local transport that keeps the persistence contract stable

The important point is that Laravel should not depend on SurrealDB's process model directly.

### Server Runtime

In server mode, Laravel should connect to a remote SurrealDB deployment.

Expected targets:

- single-node SurrealDB for early hosted environments
- clustered SurrealDB for collaborative and larger-scale deployments
- deployment-managed credentials, topology, and operational settings

This mode should preserve the same logical graph model and repository contracts used by desktop mode.

## Laravel Integration Layers

### 1. Graph Domain Layer

The graph domain layer should model Katra concepts such as:

- conversations
- tasks
- decisions
- artifacts
- projects
- links and traversal edges between these objects

This layer should express intent in Laravel terms and stay isolated from raw Surreal transport details.

The graph domain layer should be the long-term home for:

- graph-oriented repository contracts
- query and traversal services
- graph state reconstruction for context expansion and contraction
- object lifecycle rules and consistency expectations

### 2. Surreal Runtime Adapter Layer

The Surreal runtime adapter layer should translate Laravel-side requests into Surreal operations for the active runtime.

The first explicit adapter split should be:

- local embedded runtime adapter for the desktop shell
- remote runtime adapter for external SurrealDB instances

These adapters should own:

- transport selection
- authentication and connection policy
- namespace and database selection
- query execution
- serialization and deserialization conventions
- runtime capability detection

### 3. Laravel Integration Surfaces

Katra expects multiple Laravel-facing integration surfaces over time:

- graph repository access
- experimental Eloquent persistence support
- cache
- sessions
- queues
- graph traversal and streaming graph APIs

These should not all be implemented at once.

Recommended implementation order:

1. graph repository contracts and runtime adapters
2. graph-native query and traversal primitives
3. Eloquent-oriented persistence experiments where the fit is strong enough
4. cache, sessions, and queues only after runtime semantics are understood
5. streaming graph APIs after the model and runtime boundary are stable

## Eloquent Strategy

Katra should not begin by forcing SurrealDB to impersonate a relational database everywhere.

Instead:

- prefer graph repository patterns first
- use Eloquent only where the model benefits from Laravel conventions enough to justify the fit
- expect some graph-native aggregates to live outside classic Eloquent assumptions

The likely end state is mixed:

- some application data can fit Eloquent-like patterns
- graph traversal, edges, and contextual reconstruction should use graph-native access patterns

Issue [#25](https://github.com/devoption/katra/issues/25) should treat Eloquent support as a focused foundation, not as proof that all persistence should be Eloquent-shaped.

## Cache, Session, And Queue Strategy

Cache, session, and queue integrations should be treated as second-wave work, not prerequisites for the graph model.

Reasons:

- these integrations depend on predictable runtime semantics
- desktop embedded mode and remote server mode may have different durability and concurrency tradeoffs
- forcing these integrations too early would add surface area before the graph core is proven

Suggested sequence:

- define the graph and runtime adapter boundary first
- prove the read/write and traversal model
- then evaluate which Laravel primitives belong on SurrealDB versus which should remain pluggable

Issues [#26](https://github.com/devoption/katra/issues/26), [#27](https://github.com/devoption/katra/issues/27), and [#28](https://github.com/devoption/katra/issues/28) should follow the repository and adapter work, not lead it.

## Graph And Streaming API Strategy

The graph API should be treated as a first-class capability rather than an afterthought layered onto generic persistence.

That means:

- the graph schema should be explicit about node and edge responsibilities
- traversal and context reconstruction should live above low-level transport details
- streaming graph APIs should build on the same graph contracts used for direct application reads and writes

Issue [#29](https://github.com/devoption/katra/issues/29) should depend on the graph repository and adapter decisions from this document.

## Known Incompatibilities And Unknowns

### Known Constraints

- PHP is not currently the preferred SurrealDB embedding runtime.
- Desktop-local and server-remote modes do not have identical lifecycle and operational assumptions.
- NativePHP packaging must eventually account for the local embedded runtime and its lifecycle.
- Laravel-first ergonomics may not map cleanly to every SurrealDB capability.

### Open Unknowns

- What is the best Laravel-to-desktop runtime bridge for local embedded access?
- How much of the graph object model should be represented through Eloquent-like APIs versus dedicated graph repositories?
- Which consistency guarantees are required for collaborative multi-user server mode?
- How should local-first data synchronize with shared or hosted deployments over time?
- What events and subscriptions are required for streaming graph updates in the UI and agent workflows?

### Fallback Options

If embedded desktop integration proves more complex than expected, Katra can still preserve the same architectural boundary by using:

- a managed local SurrealDB sidecar runtime in desktop mode
- a remote single-node SurrealDB deployment during early hosted rollout

The key fallback principle is that Laravel should depend on a stable Katra persistence boundary, not on one Surreal runtime shape.

## Package And Module Boundaries

The codebase should evolve toward these boundaries:

- graph domain contracts and services in the Laravel app
- Surreal runtime adapters separated from domain behavior
- transport-specific logic isolated from graph semantics
- Laravel driver experiments isolated from the graph domain core

Near-term module intent:

- issue `#25`: Surreal persistence foundation behind explicit interfaces
- issues `#26` to `#28`: Laravel driver experiments layered after runtime boundaries are stable
- issue `#29`: graph and streaming capabilities layered on the same graph contracts

If the implementation grows enough, the Surreal integration code may eventually justify extraction into an internal package boundary, but it should first stabilize inside the repository.

## Recommended Next Steps

1. Keep this document as the sequencing reference for SurrealDB work.
2. Define the first graph repository contracts before broad driver work begins.
3. Treat issue [#25](https://github.com/devoption/katra/issues/25) as the first implementation issue after this strategy lands.
4. Revisit desktop runtime ownership when NativePHP packaging and local process lifecycle work become deeper concerns.

## Related Issues

- [#23](https://github.com/devoption/katra/issues/23) prove embedded SurrealDB connectivity from Laravel
- [#24](https://github.com/devoption/katra/issues/24) define the Surreal-backed Laravel integration strategy
- [#25](https://github.com/devoption/katra/issues/25) implement the Surreal Eloquent driver foundation
- [#26](https://github.com/devoption/katra/issues/26) add a SurrealDB cache store driver
- [#27](https://github.com/devoption/katra/issues/27) add a SurrealDB session driver
- [#28](https://github.com/devoption/katra/issues/28) add a SurrealDB queue driver
- [#29](https://github.com/devoption/katra/issues/29) add Surreal streaming graph API support for GraphRAG
