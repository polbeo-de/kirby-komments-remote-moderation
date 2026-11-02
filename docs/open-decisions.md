# Open decisions

| Decision | Status | Required evidence |
| --- | --- | --- |
| Exact mapping of `hold` to Komments 3.9 | open | Upstream API review and tests against Komments 3.9 |
| Panel components and build strategy | implemented, pending validation | Browser integration test against Kirby 5; all visible strings use Kirby's translation catalog |
| Audit-log sink | open | Operational requirements without content or PII logging |
| Upstream provider hook | proposed | Feedback from the Komments maintainer |

These decisions block only the affected implementation details, not the
documented security and transport architecture.
