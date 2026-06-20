# Wichtelwerken: Performance und Monitoring

Stand: 2026-06-19

## Performance

- Hero-Bild: `wp-content/themes/wichtelwerken-child/images/hero-bg.webp` ist die primäre Variante. `hero-bg.png` bleibt als Fallback.
- Theme-JavaScript lädt ohne jQuery-Abhängigkeit.
- WordPress-Emoji-/OEmbed-Assets werden im MU-Plugin entfernt.
- `wc-cart-fragments` bleibt auf Shop-, Produkt-, Warenkorb-, Kassen-, Konto- und Startseite aktiv, wird aber auf statischen Seiten entfernt.
- Produktbilder nutzen WordPress/WooCommerce-Größen; WooCommerce-Thumbnailbreite lokal: `300`, Einzelbildbreite: `600`.

Produktiv zusätzlich auf Server/CDN setzen:

```nginx
location ~* \.(?:css|js|jpg|jpeg|png|gif|svg|webp|avif|ico|woff2?)$ {
    expires 30d;
    add_header Cache-Control "public, max-age=2592000, immutable";
    access_log off;
}

location ~* ^/wp-content/.*\.(?:log|sql|sqlite|env)$ {
    deny all;
    return 404;
}
```

Empfohlen: serverseitiger Full-Page-Cache für öffentliche Seiten, aber Warenkorb, Kasse, Mein Konto, Verkäufer-Dashboard und REST/WooCommerce-Store-API ausschließen.

## Security Headers und XML-RPC

Das MU-Plugin `wp-content/mu-plugins/wichtelwerken-production-hardening.php` setzt:

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy` mit deaktivierten Sensor-/Kamera-/Mikrofonrechten und erlaubtem Payment-Kontext für die eigene Seite

XML-RPC ist zusätzlich per WordPress-Filter deaktiviert. In DDEV wird `/xmlrpc.php` auch direkt in Nginx mit `403` blockiert; auf dem Produktionsserver sollte dieselbe Regel gesetzt werden, falls dort Nginx verwendet wird.

HSTS ist bewusst opt-in, damit lokale Entwicklung und Staging nicht versehentlich festgepinnt werden:

```php
putenv('WW_ENABLE_HSTS=true');
```

Nur setzen, wenn die finale Domain dauerhaft über HTTPS läuft.

## E-Mail/SMTP

Der produktive Mailversand wird über Umgebungsvariablen konfiguriert. Ohne diese Variablen nutzt WordPress das Standard-Mailverhalten.

```text
WW_SMTP_HOST=smtp.example.com
WW_SMTP_PORT=587
WW_SMTP_SECURE=tls
WW_SMTP_USER=postfach@example.com
WW_SMTP_PASS=...
WW_MAIL_FROM=shop@example.com
WW_MAIL_FROM_NAME=Wichtelwerken
```

Nach Einrichtung auf Produktion testen:

```bash
wp eval 'var_dump(wp_mail("zieladresse@example.com", "Wichtelwerken Mailtest", "Testmail vom Produktivsystem"));'
```

Danach im Postfach und in den Serverlogs prüfen, ob SPF, DKIM und DMARC zur Absenderdomain passen.

## Backups und Restore-Test

Lokales Backup:

```bash
bin/create-backup.sh
bin/verify-backup.sh
```

Das Backup enthält Datenbank und `wp-content/uploads`. Code kommt aus GitHub; Plugins müssen vor einem Restore anhand des dokumentierten Plugin-Stands aktualisiert werden.

Produktiv mindestens täglich sichern:

- Datenbank
- `wp-content/uploads`
- produktive `wp-config.php` bzw. Secrets/Umgebungsvariablen außerhalb des Webroots

Monatlicher Restore-Test:

1. Frisches Staging anlegen.
2. Datenbankbackup importieren.
3. Uploads-Archiv entpacken.
4. Plugins aktualisieren und `wp wc update` ausführen.
5. Startseite, Shop, Produktseite, Warenkorb, Kasse, Mein Konto, Verkäufer-Dashboard und Health-Endpunkt prüfen.

## Monitoring

Health-Endpunkt:

```text
/wp-json/wichtelwerken/v1/health
```

Öffentliche Antwort enthält nur minimal:

```json
{
  "status": "ok",
  "checks": {
    "database": "ok"
  }
}
```

Für Uptime-Monitoring:

- Intervall: 1 bis 5 Minuten
- Erwarteter HTTP-Status: `200`
- Erwarteter Inhalt: `"status":"ok"`
- Alarmierung: E-Mail plus zweiter Kanal, z. B. App/SMS

Update-/Security-Prozess:

- Wöchentlich: `wp core check-update`, `wp plugin update --dry-run --all`, `wp theme update --dry-run --all`
- Vor Updates: Datenbank + `wp-content/uploads` sichern
- Plugin-Updates, die vor Go-live zuletzt geprüft wurden: Complianz `7.5.0`, Dokan Lite `5.0.4`, WooCommerce `10.8.1`
- Nach WooCommerce-Updates: `wp wc update` ausführen, bis `WC_Install::needs_db_update()` `false` meldet
- Nach Updates: Health-Endpunkt, Startseite, Shop, Produktseite, Warenkorb, Kasse und Verkäufer-Dashboard prüfen
- Monatlich: Restore-Test eines Backups
- Bei Security-Advisory: Staging aktualisieren, Smoke-Test, danach Produktion

Optional kann produktiv ein Token in `wp-config.php` gesetzt werden:

```php
define('WW_MONITORING_TOKEN', 'langen-zufaelligen-token-eintragen');
```

Der Token wird per Header gesendet:

```text
X-Wichtelwerken-Monitoring-Token: langen-zufaelligen-token-eintragen
```

## Error-Logging

Das MU-Plugin schreibt PHP-Fehler in:

```text
private-logs/php-errors.log
```

Der Ordner liegt neben `wp-content` und ist nicht öffentlich erreichbar. `wp-content/debug.log` darf produktiv nicht aktiv sein. Für Go-live in `wp-config.php`:

```php
define('WP_DEBUG', false);
define('WP_DEBUG_DISPLAY', false);
define('WP_DEBUG_LOG', false);
```

Falls temporäres Debugging nötig ist, nur mit privatem Logpfad:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', false);
define('WP_DEBUG_LOG', dirname(__FILE__) . '/private-logs/wp-debug.log');
```
