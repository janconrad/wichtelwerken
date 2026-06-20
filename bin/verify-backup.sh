#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKUP_ROOT="${WW_BACKUP_DIR:-"$ROOT_DIR/backups"}"
TARGET_DIR="${1:-}"

if [ -z "$TARGET_DIR" ]; then
  TARGET_DIR="$(find "$BACKUP_ROOT" -mindepth 1 -maxdepth 1 -type d 2>/dev/null | sort | tail -1)"
fi

if [ -z "$TARGET_DIR" ] || [ ! -d "$TARGET_DIR" ]; then
  echo "No backup directory found." >&2
  exit 1
fi

gzip -t "$TARGET_DIR/database.sql.gz"

if [ -f "$TARGET_DIR/uploads.tar.gz" ]; then
  tar -tzf "$TARGET_DIR/uploads.tar.gz" >/dev/null
fi

(
  cd "$TARGET_DIR"
  shasum -a 256 -c SHA256SUMS
)

printf 'Backup verified: %s\n' "$TARGET_DIR"
