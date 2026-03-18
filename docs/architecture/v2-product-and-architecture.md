# Katra v2 Product and Architecture Principles

## Purpose

This document defines the current product direction and architecture principles for Katra v2. It is the planning reference for early implementation work, repository messaging, and follow-up design decisions during the rewrite.

## Product Positioning

### Working Description

Katra is an open source, graph-native AI workspace for conversations, tasks, and collaborative intelligence.

### What Katra Is Becoming

Katra v2 is not just a desktop chat client and not just an AI workflow engine.

Katra is being rebuilt as a local-first Laravel application that can run in multiple environments while treating conversations, tasks, decisions, artifacts, and related context as durable graph objects. The application should support personal local use, shared multi-user collaboration, and future deployment targets without changing the core product model.

### What This Replaces

The earlier proof of concept centered more heavily on MCP tooling and multi-agent workflows. Those ideas still matter, but they are no longer the main frame for the product.

In v2:

- MCP remains an important interoperability layer, not the center of the system.
- Multi-agent behavior becomes a capability built on the graph, not the product definition.
- Chat becomes the primary interface, but the real product is the underlying graph of work and knowledge.

## Product Thesis

Katra should make conversations operational.

Instead of treating a conversation as an append-only transcript, Katra should make it possible for a conversation to create, reference, update, and relate durable objects such as:

- questions
- tasks
- decisions
- artifacts
- projects
- people
- agents
- tools
- model outputs

This graph-native model is expected to enable:

- multi-user and multi-model conversations
- subagents attached to conversation nodes or task nodes
- real-time project management rooted in the same graph
- explainable outputs tied to prior decisions and artifacts
- lower token usage through context reconstruction instead of full transcript replay
- faster responses through focused graph expansion and contraction

## Primary Product Principles

### 1. Local-First, Not Local-Only

Katra should feel first-class in local use while still supporting remote and shared deployment modes.

That means:

- NativePHP is the first-class local shell.
- Laravel remains the product core.
- The application should still be able to run as a server, container image, or Kubernetes-oriented deployment.
- Local mode should not be treated as a toy environment.

### 2. Laravel Is The Core Platform

Katra is a Laravel application with multiple runtime targets, not a desktop-only application that happens to embed Laravel.

That means:

- business logic should live in Laravel domain and service layers
- runtime-specific concerns should be adapters, not the core product architecture
- the system should remain operable in desktop, server, and automated environments

### 3. The Graph Is The Source Of Truth

Katra should treat graph state as the durable source of truth for work and context.

Chat history still matters, but the system should not depend on replaying raw transcripts as its main memory model. Instead, relevant context should be reconstructed from graph relationships.

That means:

- conversations are not just message lists
- tasks, decisions, artifacts, and references are first-class
- relationships matter as much as documents
- retrieval should be driven by graph state, not only semantic similarity

### 4. Conversation Is The Interface, Not The Storage Model

The user experience may begin with chat, but the underlying system should not collapse everything into transcript text.

Katra should support conversation nodes layered on top of Graph-based Retrieval-Augmented Generation (GraphRAG)-oriented graph structures so the application can move fluidly between discussion, memory, tasking, and execution.

### 5. Context Must Expand And Contract Intelligently

One of the central goals of v2 is to avoid the N+1 cost of continuously resending full conversation history.

Instead, the system should use the most recent interaction as a starting point, then expand into the graph to reconstruct the relevant context for the next step.

The expected benefits are:

- lower token usage
- faster response times
- less context drift
- better long-lived conversations

### 6. Authorization Must Be Relationship-Aware

Katra intends to use Relationship-Based Access Control (ReBAC) for graph authorization.

This is important because access should depend on graph relationships, not only flat roles. As the system grows into multi-user and multi-agent collaboration, authorization needs to reason about objects and relationships such as:

- who owns a task
- who participates in a conversation
- which agents can access which project contexts
- which artifacts are inherited or shared across boundaries

### 7. Multi-User And Multi-Model Are First-Class

Katra should be designed for more than one user and more than one model from the start.

That means the model should support:

- human participants
- model participants
- tool outputs as artifacts
- agent workers attached to graph state
- future model routing and orchestration strategies

### 8. MCP Matters, But It Is Not The Product

MCP support is still important for interoperability, tools, and provider integration. But the system should not be architected as an MCP-first workflow shell.

Katra should remain useful even when MCP is only one of several integration surfaces.

### 9. The Brand Should Feel Intentional Early

The current Katra identity should be preserved during the rewrite so docs and early UI work feel like one product.

Current baseline:

- Katra wordmark/logo
- Nord color palette
- `nord15` as the primary accent color

See [Katra Brand Foundation](../brand/README.md) for the current brand baseline.

## Technical Direction

### Runtime Targets

Katra v2 should support multiple runtime modes:

- NativePHP desktop shell for local-first use
- standard Laravel server deployment
- Docker image deployment
- Helm and Kubernetes-oriented deployment targets

### Core Stack

- Laravel 13
- NativePHP
- SurrealDB v3 with a desktop-embedded and server-remote runtime strategy
- Laravel AI
- Laravel MCP
- Fortify
- Livewire
- Tailwind CSS
- Pest
- Laravel Boost for local development

### Data Model Direction

The current planned direction is to build conversation-oriented nodes on top of GraphRAG-oriented graph structures.

This should support:

- conversational context
- task state
- decision history
- artifact lineage
- explainable retrieval
- resumable work

### SurrealDB Direction

SurrealDB remains the planned persistence layer for the graph-native model, but the runtime strategy is now more explicit:

- desktop mode should prefer an embedded SurrealDB runtime owned by the NativePHP / Electron side of the app
- server mode should prefer an external SurrealDB instance or cluster
- Laravel should sit behind a stable persistence boundary instead of assuming PHP-native in-process embedding

Katra also expects to rely on SurrealDB for deeper Laravel integration work over time, including:

- model persistence
- cache
- sessions
- queues
- graph traversal and streaming graph APIs

This remains an area of technical risk and should continue to be validated through spikes before large-scale implementation work is locked in. The current implementation strategy is documented in [SurrealDB Laravel Integration Strategy](surreal-integration-strategy.md).

### AI Direction

Katra should be capable of supporting:

- multiple model providers
- multiple models within a conversation
- embeddings
- model routing
- speculative decoding and related inference optimizations
- subagent execution patterns grounded in graph state

Not all of this needs to exist in the first implementation, but the architecture should avoid blocking it.

## Deferred Decisions

The following areas are important but intentionally not finalized yet:

- the exact shape of the conversation-node schema
- the exact GraphRAG implementation model
- synchronization strategy between local and shared deployments
- the first production-ready model provider set
- how release, packaging, and distribution should evolve after the initial NativePHP foundation
- whether a dedicated public website should live at `katra.io`, and how it should relate to docs and releases

## Risks And Open Questions

### Major Risks

- SurrealDB Laravel integration may be larger and riskier than expected.
- The graph model could become over-designed before enough product behavior is proven.
- Supporting both local-first and shared deployment modes may introduce synchronization and complexity tradeoffs early.
- Multi-model and subagent capabilities can create product sprawl if they are treated as ends instead of capabilities.
- NativePHP packaging and release automation may constrain the speed of local-first iteration.
- The bridge between Laravel and the desktop-owned embedded SurrealDB runtime may become more complex than expected.

### Open Questions

- What is the smallest vertical slice that proves the graph-native conversation model is working?
- Which graph objects should exist first: conversation, task, decision, artifact, project, or all of them?
- How much of the first release should be collaborative versus single-user local-first?
- What should the minimum ReBAC model be for the first useful version?
- Which capabilities belong in the first app shell versus later operational tooling?
- How should `katra.io` eventually be used: landing page, docs portal, app entry point, release surface, or some combination?

## Near-Term Guidance

In the near term, contributors should use this document to keep the rewrite aligned around a few core truths:

- Katra is a graph-native workspace, not just a workflow engine.
- Laravel is the product core.
- NativePHP is the first-class local shell, not the whole product.
- durable graph state matters more than transcript accumulation
- MCP is important, but not the center
- ReBAC is the intended authorization model

## Related Docs

- [Katra v2 Overview](../v2-overview.md)
- [SurrealDB Laravel Integration Strategy](surreal-integration-strategy.md)
- [Katra Brand Foundation](../brand/README.md)
- [Issue #41: define the katra.io domain plan](https://github.com/devoption/katra/issues/41)
