# Contributing

## Workflow

Katra v2 is being developed in the open through issues and pull requests.

Current expectations:

- follow the Branch Protection policy for `main` (see "Branch Protection" below)
- every pull request should reference an issue
- use the repository pull request template
- keep pull requests focused and reviewable

## Branch Protection

The `main` branch is intended to stay protected so the branch history remains reviewable and predictable.

Current protection expectations:

- direct pushes to `main` are disabled
- force pushes to `main` are disabled
- branch deletion is disabled for `main`
- conversation resolution is required before merge
- admins are also subject to branch protection

At the moment, an approving review is not required because the repository is being maintained through a single authenticated GitHub account in this workflow. That rule can be tightened later when the review flow supports it cleanly.

Required status checks are part of the policy as well. The current required status check for pull requests is `validate`, provided by the `PR Validation` GitHub Actions workflow.

## Conventional Commits

Katra uses Conventional Commits for commit messages—especially pull request titles (used as squash-merge commit titles)—to keep merge history and release automation consistent.

Expected format:

```text
type(scope optional): short summary
```

Examples:

```text
feat(chat): add conversation node scaffolding
fix(auth): handle expired Fortify sessions
docs: rewrite the README for Katra v2
chore: sanitize Boost MCP configuration
```

### Preferred Types

- `feat`: a user-facing feature or capability
- `fix`: a bug fix
- `perf`: a performance improvement
- `refactor`: an internal code change without intended behavior change
- `docs`: documentation-only work
- `test`: test-only work
- `ci`: CI or automation changes
- `build`: build, packaging, or dependency-system changes
- `chore`: repository maintenance or non-product changes

### Breaking Changes

Breaking changes should be called out explicitly using Conventional Commits syntax.

Examples:

```text
feat!: replace the conversation node schema
feat(api)!: redesign graph query responses
```

You can also describe the break in the commit body or PR body with:

```text
BREAKING CHANGE: explanation
```

## Pull Requests And Merge Strategy

Because Katra intends to automate version bumps and releases from merges to `main`, pull requests should be merged in a way that keeps the final history predictable.

Current policy:

- prefer squash merges into `main`
- the pull request title should follow Conventional Commits
- the final merged commit on `main` should clearly describe the released change

In practice, this means the pull request title matters. If a PR is squash-merged, the squash commit title should remain a valid conventional commit.

## Semantic Versioning

Katra uses Semantic Versioning with tags in the format:

```text
vX.Y.Z
```

Examples:

```text
v0.1.0
v0.2.3
v1.0.0
```

### Version Bump Rules

The intended release policy is:

- `feat` => minor version bump
- `fix` => patch version bump
- `perf` => patch version bump
- commits marked with `!` or `BREAKING CHANGE:` => major version bump

The following commit types should not create a product release by themselves unless they also include a breaking change:

- `docs`
- `test`
- `chore`
- `ci`
- `build`
- `refactor`

## Release Policy

Katra intends to automate tags and GitHub releases from merges into `main`.

Target behavior:

- a pull request is merged into `main`
- release automation evaluates the conventional commit on `main`
- the next semantic version is calculated automatically
- a tag in `vX.Y.Z` format is created automatically
- a GitHub release is created from that tag automatically

This means contributors should think of pull request titles and merge commits as release inputs, not just review labels.

## Notes

- if a pull request contains mixed work, choose the title based on the highest-impact user-facing change
- if a pull request includes a breaking change, make that explicit in the title or body
- if release behavior becomes more specific later, this policy should be updated to match the automation
