# Architecture

`kirby-komments-remote-moderation` is a generic Kirby 5 plugin for centralised
moderation of pending Komments comments and webmentions. It has two
independently enabled roles:

- The **remote bridge** runs on a remote Kirby instance without a Panel and
  provides a minimal HMAC-protected JSON API.
- The **moderation console** runs on a local Panel instance. It accesses
  remote sources exclusively server-side and provides a dedicated Remote
  Comments Panel area.

## Responsibilities and boundaries

| Component | Responsible for | Not responsible for |
| --- | --- | --- |
| Bridge HTTP layer | secure request handling and JSON responses | domain storage |
| Security module | HMAC, time window, and nonce replay protection | Panel authorisation and rate limiting |
| Komments adapter | reading and moderation through public Komments classes | direct SQLite access |
| Console application | server-side transport and source selection | exposing secrets to the browser |
| Panel area | translated interface and live confirmation | remote authentication |

The bridge uses `mauricerenck\\Komments\\KommentModeration` and its storage
layer rather than manipulating a database externally. Its DTOs filter email
addresses, IP addresses, verification tokens, and other unnecessary fields.

## Contracts

The bridge exposes only `/api/komments-remote-moderation/v1/*`. Authenticated
signature headers contain a key ID, Unix timestamp, nonce, body hash, and an
HMAC-SHA-256 over method, path, timestamp, nonce, and body hash. An action
contains an idempotency key and the expected `updatedAt` version. The bridge
returns `409 Conflict` for a stale version.

## Dependencies

PHP >= 8.2, Kirby 5, and Komments >= 3.9 are runtime requirements. The
Komments adapter remains the only technical dependency on upstream; domain and
security classes do not depend on the Panel or HTTP transport.

## Security model

Endpoints are disabled by default. Host-side secrets exist only in ignored
configuration. The bridge enforces key allow-listing, constant-time comparison,
a narrow time window, and single-use nonces in Kirby's cache. Cache-based rate
limiting and a metadata-minimising audit-log sink remain release blockers.

See [ADR 0001](../adr/0001-separate-panel-area.md),
[system responsibilities](system-responsibilities.md),
[domain model](domain-model.md), and [deployment](deployment.md).
