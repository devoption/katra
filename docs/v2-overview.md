# Katra v2 Overview

## Status

Katra v2 is an in-progress rewrite of the original proof of concept. The rewrite is happening in the open through small pull requests and linked GitHub issues.

## Product Direction

Katra is being reimagined as a local-first, graph-native AI workspace built on Laravel. While NativePHP is the first-class local shell, the application is intended to support multiple runtime targets, including standard server deployments, containers, and Kubernetes environments.

The product direction is broader than an AI workflow engine. Katra v2 aims to treat conversations, tasks, decisions, artifacts, and related context as durable graph objects that can support collaborative knowledge work over time.

## Planned Foundations

- Laravel 13 as the application core
- NativePHP for the local desktop shell
- SurrealDB v3 with an embedded-first approach
- Laravel AI and Laravel MCP for AI and interoperability foundations
- Fortify for authentication
- Livewire and Tailwind CSS for the UI foundation
- Pest for testing

## Why The Graph Matters

The graph-oriented model is expected to make several things possible:

- Multi-user and multi-model conversations
- Subagents attached to conversation nodes and task nodes
- Context expansion and contraction based on current graph state instead of replaying the full conversation history
- Lower token usage and faster responses through more focused context reconstruction
- Real-time project management rooted in the same conversation and task graph

## Next Step

The deeper product and architecture direction is tracked in [issue #13](https://github.com/devoption/katra/issues/13) and will be expanded into fuller project documentation as the rewrite progresses.
