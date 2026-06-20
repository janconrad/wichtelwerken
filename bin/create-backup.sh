#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKUP_ROOT="${WW_BACKUP_DIR:-"$ROOT_DIR/backups"}"
STAMP="$(date -u +"%Y%m%d-%H%M%S")"
TARGET_DIR="$BACKUP_ROOT/$STAMP"

mkdir -p "$TARGET_DIR"

if command -v ddev >/dev/null 2>&1 && (cd "$ROOT_DIR" && ddev describe >/dev/null 2>&1); then
  (cd "$ROOT_DIR" && ddev export-db --file "$TARGET_DIR/database.sql.gz")
else
  (cd "$ROOT_DIR" && wp db export "$TARGET_DIR/database.sql")
  gzip "$TARGET_DIR/database.sql"
fi

if [ -d "$ROOT_DIR/wp-content/uploads" ]; then
  tar -czf "$TARGET_DIR/uploads.tar.gz" -C "$ROOT_DIR" wp-content/uploads
fi

(
  cd "$TARGET_DIR"
  shasum -a 256 ./*.gz > SHA256SUMS
)

printf 'Backup written to %s\n' "$TARGET_DIR"
