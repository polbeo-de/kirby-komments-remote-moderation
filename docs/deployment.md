# Deployment

Installieren Sie das Plugin per Composer auf jeder beteiligten Kirby-Instanz.
Aktivieren Sie die Bridge nur auf moderierten Instanzen und die Konsole nur auf
der lokalen Panel-Instanz. Hinterlegen Sie Schlüssel nur in einer ignorierten
Host-Konfigurationsdatei; nutzen Sie in Versionskontrolle ausschließlich
Platzhalter.

Vor einem Update: Sicherung und Wartungsfenster nach den lokalen Regeln planen,
Composer-Abhängigkeiten prüfen, Konfiguration validieren und zunächst Staging
testen. Nach dem Update Endpunkte, Zeitfenster und einen testweisen
Moderationsablauf prüfen. Bei Problemen die vorige getestete Paketversion
wiederherstellen; keine Kommentare direkt in der Komments-Datenbank ändern.

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
