# Wichtelwerken: Performance und Monitoring

Stand: 2026-05-26

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
