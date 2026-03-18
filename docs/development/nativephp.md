# NativePHP Desktop Setup

## Purpose

Katra v2 is being built as a local-first Laravel application, and NativePHP is the first-class local desktop shell for that experience.

This document covers the current bootstrap path for running the app in a NativePHP window during development.

## Current Compatibility Note

NativePHP desktop does not yet ship an official Laravel 13-compatible tagged release.

For now, this repository is pinned to the upstream Laravel 13 compatibility branch from the open NativePHP desktop compatibility pull request:

- `laravel-shift/desktop` branch `l13-compatibility`
- upstream PR: <https://github.com/NativePHP/desktop/pull/96>

This is intended as a temporary bridge until NativePHP publishes Laravel 13 support in an official release.

## Prerequisites

- PHP `8.4+`
- Node `22+`
- a supported desktop OS for NativePHP development

The official NativePHP installation guide is here:

- <https://nativephp.com/docs/desktop/2/getting-started/installation>

## First-Time Setup

If you are starting from a clean clone, the simplest path is:

```bash
composer setup
```

That script now installs PHP dependencies, prepares the Laravel app, runs the NativePHP installer, and builds the frontend assets.

If you need to run the NativePHP installer manually, use:

```bash
php artisan native:install --force
```

## Running The Desktop Shell

For the normal local workflow, run:

```bash
composer native:dev
```

This starts:

- `php artisan native:run --no-interaction`
- `npm run dev`

If you want to run the NativePHP shell directly and manage frontend tooling separately, use:

```bash
php artisan native:run --no-interaction
```

## Current Bootstrap Behavior

The initial shell is intentionally small and focused:

- NativePHP opens the Laravel home route in a remembered desktop window
- the window uses Katra-focused defaults for title and size
- the root page acts as a lightweight desktop landing screen for smoke testing

## Troubleshooting

- If the desktop shell does not reflect frontend changes, restart `composer native:dev` or ensure Vite is running.
- If NativePHP dependencies drift after package updates, rerun `php artisan native:install --force`.
- If the application fails in desktop mode, first verify the root route works in the browser before debugging NativePHP-specific behavior.
