# Architektur-Audit

Datum: 2026-10-30

## Ergebnis

Die Architektur erfüllt PES-008: Komments wurde als maintained Community
Component geprüft; eine Konfiguration, ein Hook, API oder Adapter für eine
zusätzliche Quelle ist in 3.9.5 nicht vorhanden. Keine Kopie und kein Patch von
Vendor-Code sind vorgesehen. Der Komments-Adapter liefert technische
Moderationsoperationen, während die Bridge nur Transport und Schutz übernimmt.

## Befunde und Maßnahmen

- Die separate Area ist akzeptiert und in ADR 0001 festgehalten.
- Konkretes Status-Mapping und Panel-Build sind offene, testspezifische
  Entscheidungen und in `docs/open-decisions.md` verfolgt.
- Produktive Implementierung darf für jene Details erst beginnen, wenn die
  Abhängigkeiten mittels Composer installiert und die API mit Tests validiert
  werden kann.
