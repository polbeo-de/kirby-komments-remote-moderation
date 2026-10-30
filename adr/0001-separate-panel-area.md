# ADR 0001: Separate Panel-Area für Remote-Kommentare

Status: accepted

## Kontext

Komments 3.9.5 registriert seine Area `komments` sowie Views und Dialoge direkt
und bietet keinen dokumentierten, stabilen Provider-, Hook- oder Adapterpunkt,
um zusätzliche Quellen in die bestehende Comments-Ansicht einzubringen. Vendor
Dateien dürfen nicht geändert oder kopiert werden.

## Alternativen

1. Komments vendor patchen oder seine Komponenten kopieren.
2. Einen nicht dokumentierten internen Erweiterungspunkt verwenden.
3. Eigene Area mit gleicher, klarer Bedienlogik bereitstellen.

## Entscheidung und Begründung

Wir wählen Alternative 3. Die Area heißt „Remote-Kommentare“, zeigt die
gewählte Quelle dauerhaft an und verlangt für `live` vor schreibenden Aktionen
eine zweite Bestätigung. Das vermeidet Vendor-Kopien und eine Kopplung an
interne Komments-Details.

## Konsequenzen

Die Oberfläche ist zunächst getrennt von „Komments“. Als Upstream-Vorschlag
empfehlen wir eine dokumentierte `CommentSourceProvider`-Schnittstelle, über
die Komments Quellen, Detaildaten und moderierende Commands registriert. Die
Kern-Area könnte diese Provider per Dependency Injection abfragen; der
Provider bliebe für eigenen Transport und Authentifizierung zuständig.

## Erforderliche Evidenz

Kompatibilitätstests mit Komments >= 3.9 und eine Prüfung des Upstream-API bei
jeder Major-Version von Komments.
