#!/usr/bin/env bash
# Deploy clydehousebuyers.co.uk to Fasthosts ProSite via rsync over SSH.
#
# Usage:
#   ./deploy.sh              # sync local files -> live server
#   ./deploy.sh --dry-run    # preview what would change, no upload
#
# Requires the SSH config entry already set up in ~/.ssh/config:
#   Host clydehosting
#       HostName 77.68.64.22
#       User csh3420361
#       Port 1022
#       IdentityFile ~/.ssh/id_ed25519
#
# private/ is excluded entirely — it holds server-only runtime state
# (leads.csv, ratelimit.json, secrets.php with real credentials) that
# has no local equivalent. Never sync it in either direction.

set -euo pipefail

SSH_HOST="${SSH_HOST:-clydehosting}"
REMOTE_PATH="${REMOTE_PATH:-~/htdocs/}"
LOCAL_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/"

DRY_RUN=()
if [[ "${1:-}" == "--dry-run" ]]; then
  DRY_RUN=(--dry-run)
  echo "DRY RUN — no files will be changed on the server."
fi

echo "Deploying $LOCAL_PATH -> $SSH_HOST:$REMOTE_PATH"

rsync -avz --delete "${DRY_RUN[@]}" \
    --exclude='.git/' \
    --exclude='.gitignore' \
    --exclude='.claude/' \
    --exclude='deploy.sh' \
    --exclude='README.md' \
    --exclude='.DS_Store' \
    --exclude='node_modules/' \
    --exclude='private/' \
    "$LOCAL_PATH" "$SSH_HOST:$REMOTE_PATH"

if [[ ${#DRY_RUN[@]} -eq 0 ]]; then
  echo "Re-asserting private/ permissions..."
  ssh "$SSH_HOST" "chmod 700 ~/htdocs/private 2>/dev/null || true"
  echo "Done. Visit https://clydehousebuyers.co.uk to verify."
else
  echo "Dry run complete."
fi
