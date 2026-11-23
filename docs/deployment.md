# Deployment

Install the plugin with Composer on every participating Kirby instance. Enable
the bridge only on moderated instances and the console only on the local Panel
instance. Store keys exclusively in an ignored host configuration file; commit
placeholders only.

Before an update, plan a backup and maintenance window according to local
policy, check Composer dependencies, validate configuration, and test Staging
first. After the update, verify endpoints, time windows, and a test moderation
flow. If necessary, restore the previously tested package version; never alter
comments directly in the Komments database.

## Integration test: Hinterhof and website.test

Use `website.test` only as the local Panel console and Hinterhof only as the
remote bridge. Install the identical plugin revision in both Kirby instances.
Create a fresh, environment-specific test key and random secret in each
ignored host configuration. Do not store either value in Git, deployment logs,
or test reports. Activate neither role by default; activate exactly the bridge
on Hinterhof and exactly the console on `website.test` for the test window.

Start with a synthetic, non-personal pending comment or webmention. Confirm
the signed list request, publish, hold, spam, delete, stale-version conflict,
invalid signature, nonce replay rejection, and the visible live-source warning
plus second confirmation for every mutation. Disable the test bridge or remove
its test key after the test if it is not needed for continued staging work.
