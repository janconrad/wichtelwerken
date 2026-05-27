# Wichtelwerken: Anbieter- und Produktfreigabe

Stand: 2026-05-27

## Statusmodell Anbieter

Anbieter haben einen eigenen Wichtelwerken-Freigabestatus:

- `Angaben unvollständig`: rechtliche Pflichtangaben fehlen.
- `In Prüfung`: Angaben liegen vor, Marktplatz prüft Identität, Pflichtdaten und Plausibilität.
- `Freigegeben`: Anbieter darf verkaufen. Neue oder geänderte Produkte werden trotzdem geprüft.
- `Gesperrt`: Anbieter darf keine Produkte einstellen oder bearbeiten.

Der Status wird im WordPress-Admin im Benutzerprofil eines Anbieters unter `Wichtelwerken-Freigabe` gepflegt.

## Pflichtprüfung vor Freigabe

Vor der Freigabe sollten mindestens geprüft werden:

- Rechtlicher Name/Firma
- ladungsfähige Anschrift
- E-Mail für Kunden und rechtliche Anfragen
- Rücksendeadresse
- DSA-Selbstbestätigung
- VerpackG/LUCID/Systembeteiligung, soweit einschlägig
- Produktsicherheit/GPSR, soweit einschlägig
- Buchpreisbindung bei Büchern

Die technischen Felder liegen im Anbieter-Dashboard unter `Einstellungen`.

## Produktfreigabe

Auch freigegebene Anbieter veröffentlichen neue oder geänderte Produkte nicht direkt. Das Theme setzt Produktänderungen von Anbietern auf `Ausstehende Prüfung`.

Admin-Ablauf:

1. WordPress-Admin öffnen.
2. `Produkte` -> `Ausstehend` öffnen.
3. Produktdaten prüfen:
   - Produktbeschreibung
   - Preis und MwSt.
   - Lagerbestand
   - Versandklasse
   - Produktbilder
   - Pflichtangaben/Warnhinweise
4. Bei Freigabe Produktstatus auf `Veröffentlicht` setzen.
5. Bei Rückfrage Produkt auf `Entwurf` setzen oder Anbieter kontaktieren.

## Sperrprozess

Bei Rechtsverstoß, fehlenden Pflichtdaten, Rückruf, Produktproblem oder Missbrauch:

1. Anbieterstatus auf `Gesperrt` setzen.
2. Betroffene Produkte auf `Entwurf` oder `Ausstehende Prüfung` setzen.
3. Grund intern dokumentieren.
4. Anbieter per E-Mail informieren.
5. Meldung/Beschwerde und Entscheidung dokumentieren.

## Technische Umsetzung

- Anbieterstatus: User-Meta `ww_vendor_review_status`
- Dokan-Verkaufserlaubnis: `dokan_enable_selling`
- Dokan-Direktveröffentlichung: `dokan_publishing` bleibt bei Wichtelwerken auf `no`
- Produktprüfung: Post-Meta `_ww_product_review_status`
- Health/Monitoring separat dokumentiert in `docs/production-performance-monitoring.md`
