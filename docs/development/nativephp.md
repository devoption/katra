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
- recent tagged releases are intended to be signed, notarized, and stapled for normal macOS installation
- older preview releases may still trigger Gatekeeper prompts because they were produced before trusted distribution was added

## Release Artifacts

Tagged releases build macOS desktop artifacts in GitHub Actions, stage a release-safe copy of the generated files, and attach those staged assets to the GitHub Release.

The current workflow intentionally keeps this first packaging path small:

- target platform: macOS
- current architecture targets: `x64` and `arm64`
- raw build output: generated under `nativephp/electron/dist`
- workflow artifact: preserved from the staged `nativephp/electron/release-assets` directory
- bundled local data runtime: the official SurrealDB macOS CLI is downloaded during release builds and packaged under NativePHP `extras`
- release assets: a notarized architecture-specific DMG plus a matching `sha256` checksum file
- current GitHub-hosted runners: `macos-15-intel` for Intel builds and `macos-15` for Apple Silicon builds

### Signing And Notarization

Tagged macOS releases now import a Developer ID Application certificate in CI, let NativePHP / Electron Builder sign the generated app bundle, notarize the app with Apple, notarize the packaged DMG, and staple the notarization ticket to both artifacts before upload.

The workflow expects these GitHub repository secrets before a release-worthy merge can publish macOS artifacts:

- `MACOS_DEVELOPER_ID_APPLICATION_CERTIFICATE_P12_BASE64`
- `MACOS_DEVELOPER_ID_APPLICATION_CERTIFICATE_PASSWORD`
- `MACOS_NOTARY_APPLE_ID`
- `MACOS_NOTARY_APP_SPECIFIC_PASSWORD`
- `MACOS_NOTARY_TEAM_ID`

If any of those values are missing, the `Tagged Release` workflow now fails immediately with a clear configuration error instead of silently falling back to an ad-hoc preview signature.

## Current Bootstrap Behavior

The initial shell is intentionally small and focused:

- NativePHP opens the Laravel home route in a remembered desktop window
- the window uses Katra-focused defaults for title and size
- the root page acts as a lightweight desktop landing screen for smoke testing

## Troubleshooting

- If the desktop shell does not reflect frontend changes, restart `composer native:dev` or ensure Vite is running.
- If NativePHP dependencies drift after package updates, rerun `php artisan native:install --force`.
- If the application fails in desktop mode, first verify the root route works in the browser before debugging NativePHP-specific behavior.
