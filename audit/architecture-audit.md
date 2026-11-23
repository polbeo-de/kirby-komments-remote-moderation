# Architecture audit

Date: 2026-10-30

## Result

The architecture satisfies PES-008: Komments was assessed as a maintained
community component; version 3.9.5 has no configuration, hook, API, or adapter
for an additional source. No vendor-code copy or patch is planned. The Komments
adapter provides technical moderation operations while the bridge provides only
transport and protection.

## Findings and actions

- The separate area is accepted and recorded in ADR 0001.
- Exact status mapping and the Panel build are open, test-specific decisions
  tracked in `docs/open-decisions.md`.
- Production implementation of those details may begin only after dependencies
  can be installed with Composer and the API can be validated by tests.
