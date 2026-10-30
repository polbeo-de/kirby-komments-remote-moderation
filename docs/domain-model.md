# Domänenmodell

- **Quelle**: benannte lokale oder Remote-Moderationsinstanz mit Rolle,
  Basis-URL und Schlüsselkennung.
- **Moderationseintrag**: datensparsames Abbild eines Kommentars oder einer
  Webmention mit ID, Typ, anonymisierter notwendiger Autorendarstellung,
  Seitendaten, Status und `updatedAt`.
- **Moderationsaktion**: `publish`, `hold`, `spam` oder `delete`, gebunden an
  Eintrag, erwartete Version und Idempotenzschlüssel.
- **Signierte Anfrage**: Methode, Pfad, Zeit, Nonce, Body-Hash und HMAC.
- **Audit-Ereignis**: nicht sensitiver Nachweis über eine Aktion.
