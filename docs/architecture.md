# Architektur

`kirby-komments-remote-moderation` ist ein generisches Kirby-5-Plugin für die
zentralisierte Moderation ausstehender Komments-Kommentare und Webmentions.
Es hat zwei unabhängig aktivierbare Rollen:

- Die **Remote-Bridge** läuft auf einer entfernten Kirby-Instanz ohne Panel und
  stellt eine minimalistische, HMAC-geschützte JSON-API bereit.
- Die **Moderationskonsole** läuft auf einer lokalen Panel-Instanz. Sie ruft
  Remote-Quellen ausschließlich serverseitig auf und stellt eine eigene Panel
  Area „Remote-Kommentare“ bereit.

## Verantwortlichkeiten und Grenzen

| Komponente | Verantwortung | Nicht verantwortlich für |
| --- | --- | --- |
| Bridge-HTTP-Schicht | sichere Entgegennahme und JSON-Antworten | Fachliche Speicherung |
| Sicherheitsmodul | HMAC, Zeitfenster und Nonce-Replay-Schutz | Panel-Berechtigungen und Rate Limit |
| Komments-Adapter | Lesen und Moderieren über öffentliche Komments-Klassen | direkte SQLite-Zugriffe |
| Konsolen-Anwendung | serverseitiger Transport und Quellenwahl | Übergabe von Secrets an Browser |
| Panel-Area | deutsche Bedienung und Live-Bestätigung | Remote-Authentifizierung |

Die Bridge verwendet `mauricerenck\\Komments\\KommentModeration` und dessen
Storage-Schicht statt einer externen Datenbankmanipulation. Ihre DTOs filtern
E-Mail, IP, Verifikationstoken und andere nicht benötigte Felder aus.

## Verträge

Die Bridge veröffentlicht nur `/api/komments-remote-moderation/v1/*`.
Authentifizierte Signatur-Header enthalten Key-ID, Unix-Zeit, Nonce, Body-Hash
und HMAC-SHA-256 über Methode, Pfad, Zeit, Nonce und Body-Hash. Eine Aktion
enthält einen Idempotenzschlüssel und die erwartete `updatedAt`-Version. Bei
einer veralteten Version liefert die Bridge `409 Conflict`.

## Abhängigkeiten

PHP >= 8.2, Kirby 5 und Komments >= 3.9 sind Laufzeitvoraussetzungen. Der
Komments-Adapter bleibt die einzige technische Abhängigkeit vom Upstream;
Domain- und Sicherheitsklassen hängen nicht von Panel oder HTTP-Transport ab.

## Sicherheitsmodell

Endpunkte sind standardmäßig deaktiviert. Host-seitige Geheimnisse liegen nur
in ignorierter Konfiguration. Die Bridge erzwingt Schlüssel-Whitelisting,
konstante Zeitvergleich, ein enges Zeitfenster und einmalige Nonces im
Kirby-Cache. Cache-basiertes Rate Limiting und eine metadatenarme Audit-Log-Senke
sind vor einer Veröffentlichung noch zu implementieren.

Weiterführend: [ADR 0001](../adr/0001-separate-panel-area.md),
[Systemverantwortlichkeiten](system-responsibilities.md),
[Domänenmodell](domain-model.md) und [Deployment](deployment.md).
