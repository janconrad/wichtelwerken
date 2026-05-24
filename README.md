# Wichtelwerken

WordPress/WooCommerce/Dokan-Projekt fuer den Marktplatz Wichtelwerken.

## Inhalt dieses Repositories

Versioniert werden nur projektspezifische Dateien:

- Child Theme: `wp-content/themes/wichtelwerken-child/`
- Must-use Plugin fuer Datenschutz/Cookie-Ergaenzungen: `wp-content/mu-plugins/wichtelwerken-privacy.php`
- Rechtstexte-Update-Skript: `wp-content/uploads/ww-import-source/legal/update-legal-pages.php`
- DDEV Basis-Konfiguration: `.ddev/config.yaml`

Nicht versioniert werden WordPress-Core, installierte Plugins, Uploads, Datenbank, lokale Konfigurationen und Secrets.

## Deployment

Das Repository enthaelt eine GitHub-Actions-Vorlage unter `.github/workflows/deploy.yml`.
Fuer automatisches Deployment muessen im GitHub-Repository diese Secrets gesetzt werden:

- `DEPLOY_HOST`
- `DEPLOY_USER`
- `DEPLOY_PATH`
- `DEPLOY_SSH_KEY`
- optional: `DEPLOY_PORT`

`DEPLOY_PATH` ist der absolute Pfad zum WordPress-Root auf dem Server, z. B. `/var/www/example.com/htdocs`.

Deployt werden:

- `wp-content/themes/wichtelwerken-child/`
- `wp-content/mu-plugins/wichtelwerken-privacy.php`
- `wp-content/uploads/ww-import-source/legal/update-legal-pages.php`

## Lokale Entwicklung

Das Projekt ist fuer DDEV vorbereitet:

```bash
ddev start
```

Danach ist die lokale Seite unter `https://wichtelwerken.ddev.site` erreichbar.
