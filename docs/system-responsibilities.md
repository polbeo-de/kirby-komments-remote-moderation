# Systemverantwortlichkeiten

Die Remote-Bridge schützt und übersetzt einen kleinen Moderationsvertrag. Sie
entscheidet nicht über redaktionelle Regeln; Komments bleibt Eigentümer von
Kommentarstatus und Speicherung. Die lokale Konsole ist der einzige Ort für
Panel-Interaktion und die zusätzliche Schreibbestätigung für eine Quelle mit
der Kennzeichnung `live`.

Der Browser kommuniziert nur mit authentifizierten Kirby-Panel-Routen. Diese
routen intern zum serverseitigen Remote-Client. HMAC-Schlüssel verlassen weder
die lokale PHP-Laufzeit noch die Remote-Bridge-Konfiguration.
