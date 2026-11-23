# Domain model

- **Source**: a named local or remote moderation instance with a role, base
  URL, and key identifier.
- **Moderation entry**: a data-minimised representation of a comment or
  webmention with its ID, type, necessary pseudonymised author presentation,
  page data, status, and `updatedAt`.
- **Moderation action**: `publish`, `hold`, `spam`, or `delete`, bound to an
  entry, expected version, and idempotency key.
- **Signed request**: method, path, timestamp, nonce, body hash, and HMAC.
- **Audit event**: non-sensitive evidence of an action.
