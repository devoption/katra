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

## Trying A Release Build

If you want to try Katra without cloning the repository, use the desktop assets attached to the [GitHub Releases](https://github.com/devoption/katra/releases) page.

- choose the asset that matches your Mac architecture when it is available: `x64` for Intel or `arm64` for Apple Silicon
- release builds now bundle the Surreal runtime through NativePHP `extras`, so the desktop shell does not require a separate machine-local `surreal` CLI install
- expect preview-quality behavior while the desktop shell and local runtime story are still being built out
- expect Gatekeeper prompts until macOS signing and notarization are in place

## Release Artifacts

Tagged releases build macOS desktop artifacts in GitHub Actions, stage a release-safe copy of the generated files, and attach those staged assets to the GitHub Release.

The current workflow intentionally keeps this first packaging path small:

- target platform: macOS
- current architecture targets: `x64` and `arm64`
- raw build output: generated under `nativephp/electron/dist`
- workflow artifact: preserved from the staged `nativephp/electron/release-assets` directory
- bundled local data runtime: the official SurrealDB macOS CLI is downloaded during release builds and packaged under NativePHP `extras`
- release assets: uploaded to the matching GitHub Release with architecture-explicit filenames
- current GitHub-hosted runners: `macos-15-intel` for Intel builds and `macos-15` for Apple Silicon builds

### Signing And Notarization

If the repository provides the following secrets, the macOS build can attempt notarization during packaging:

- `NATIVEPHP_APPLE_ID`
- `NATIVEPHP_APPLE_ID_PASS`
- `NATIVEPHP_APPLE_TEAM_ID`

If those secrets are not configured, the workflow still builds and uploads artifacts, but signing and notarization remain a manual follow-up step.

## Current Bootstrap Behavior

The initial shell is intentionally small and focused:

- NativePHP opens the Laravel home route in a remembered desktop window
- the window uses Katra-focused defaults for title and size
- the root page acts as a lightweight desktop landing screen for smoke testing

## Troubleshooting

- If the desktop shell does not reflect frontend changes, restart `composer native:dev` or ensure Vite is running.
- If NativePHP dependencies drift after package updates, rerun `php artisan native:install --force`.
- If the application fails in desktop mode, first verify the root route works in the browser before debugging NativePHP-specific behavior.
