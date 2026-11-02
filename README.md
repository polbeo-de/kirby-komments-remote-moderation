# Kirby Komments Remote Moderation

> Status: maintained plugin. Release artifacts and version tags are published
> through the repository release process.

A Kirby 5 plugin for securely moderating pending
[Komments](https://github.com/mauricerenck/komments) comments and webmentions
from one local Kirby Panel across multiple remote installations. GitHub-facing
documentation is maintained in English; the plugin user interface is German.
The Panel interface uses Kirby's translation mechanism and ships with German
and English catalogs.

## Requirements

- PHP >= 8.2
- Kirby 5
- Komments >= 3.9

## Installation

```sh
composer require polbeo/kirby-komments-remote-moderation
```

## Roles and configuration

The remote bridge and local console are opt-in. Place the actual configuration
in an ignored host configuration file, never in version control:

```php
<?php

return [
    'polbeo.komments-remote-moderation' => [
        'bridge' => [
            'enabled' => true,
            'keys' => [
                'console-staging' => ['secret' => 'replace-with-a-random-secret'],
            ],
        ],
        'console' => [
            'enabled' => true,
            'sources' => [
                'staging' => [
                    'label' => 'Staging',
                    'baseUrl' => 'https://moderation-target.invalid',
                    'keyId' => 'console-staging',
                    'secret' => 'replace-with-the-matching-random-secret',
                    // Optional upstream HTTP access protection; keep local.
                    'basicAuthUser' => 'replace-with-operator-user',
                    'basicAuthPassword' => 'replace-with-operator-password',
                    'live' => false,
                ],
            ],
        ],
    ],
];
```

Use a distinct random secret for every source. Secrets must never reach Panel
JavaScript, URLs, query parameters, responses, or audit logs. The shown values
are placeholders only.

For development without modifying the host's existing Kirby configuration, the
same structure may be returned from the ignored plugin file `config/local.php`.
Host configuration takes precedence over this development-only fallback.

## Testing with a local Panel and a remote bridge

For a non-production integration test, configure `website.test` as the local
Panel console and Hinterhof as the remote bridge. Keep both configuration files
ignored. Create a unique test key and random secret on both sides, enable only
the bridge on Hinterhof and only the console on `website.test`, then use a
non-personal pending test entry. Verify listing, every moderation action,
stale-version handling, invalid signatures, nonce replay, and the extra write
confirmation after marking a source as `live`.

Do not enable a bridge on an environment until its host configuration and
deployment have been reviewed. The console never returns source URLs or
secrets to Panel JavaScript.

## Security model and threat model

The bridge signs and verifies HTTP method, versioned path, timestamp, nonce,
and body hash with HMAC-SHA-256. It rejects unknown keys, stale timestamps,
replayed nonces, and invalid signatures using `hash_equals()`. Endpoints are
disabled by default. Mutations verify an expected version and treat already
reached target states idempotently.

The bridge deliberately returns no email addresses, IP addresses, verification
tokens, or other unnecessary private fields. Production-ready rate limiting and
persistent audit logging remain outstanding release blockers; they will be
implemented and tested before a release.

## Panel operation

The Panel area is named **Remote Comments** (or its translated equivalent). It provides source selection,
pending entries, and moderation actions through authenticated Panel API routes.
When the `remote-komments` field is added to a page blueprint, the same
controls appear in that page's Panel view and show pending and published
entries for the current page, including their status. This integration does
not modify Komments vendor files.
A source marked `live` remains visible and requires an additional confirmation
for every write action.

## Compatibility

| Component | Supported |
| --- | --- |
| PHP | >= 8.2 |
| Kirby | 5.x |
| Komments | >= 3.9 |

See [architecture](docs/architecture.md), [deployment](docs/deployment.md),
[security policy](SECURITY.md), and [contribution guide](CONTRIBUTING.md).
