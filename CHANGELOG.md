# Changelog

All notable changes to this project are documented in this file.

## Unreleased

### Fixed

- Allowed empty content for social reactions such as likes and reposts, so a
  valid reaction can no longer make the remote moderation listing unavailable.

### Added

- Local repository and open-source baseline: Composer metadata, MIT license,
  security and contribution guidance, CI, and Dependabot.
- Architecture documentation, architecture audit, and ADR 0001 for a
  separate, upstream-friendly Panel area without modifying Komments vendor
  code.
- Versioned, disabled-by-default bridge route design for pending entries and
  moderation actions.
- HMAC-SHA-256 signing over method, path, timestamp, nonce, and body hash,
  plus time-window, `hash_equals()`, and replay-protection abstractions.
- Data-minimising moderation DTOs and a unit-test baseline for signing,
  request verification, and field filtering.
- Kirby Panel translation catalogs for German and English. Planned Panel
  components must use Kirby's `$t()` mechanism for all visible static strings.
- Local Panel console area, authenticated Panel API routes, server-side signed
  remote client, source configuration validation, visible live-environment
  warning, and a second confirmation before a live mutation.
- Optional server-side HTTP Basic Auth for remote sources protected by an
  upstream access gate.
- A reusable `remote-komments` page field that filters remote pending
  and published entries by the currently edited page, leaving Komments' own
  Panel section unchanged.
- Status-aware moderation controls: published entries offer “Hold” instead of
  the redundant “Publish” action.

### Changed

- The global Remote Comments area again lists only pending entries; the
  article-level field continues to show published entries as delivery status.
- README updated as an accurate pre-release document: rate limiting and audit
  logging are explicitly marked as release blockers.
- README and deployment guidance now document the `website.test` to Hinterhof
  integration-test procedure without disclosing environment secrets.
