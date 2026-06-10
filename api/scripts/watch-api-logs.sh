#!/usr/bin/env bash


#
# This file is part of the Expense Tracker.
#
#  (c) SekjuRiczard <dawidosak32@gmail.com>
#
#  For the full copyright and license information, please view the LICENSE
#  file that was distributed with this source code.
#
set -euo pipefail

LOG_FILE="var/log/api.jsonl"
SERVICE="api"

if ! command -v jq >/dev/null 2>&1; then
    echo "Brakuje programu jq."
    echo "Zainstaluj go poleceniem: sudo apt install jq"
    exit 1
fi

echo "Nasłuchiwanie logów API..."
echo "Zatrzymaj podgląd skrótem Ctrl+C."
echo

sudo docker compose exec -T "$SERVICE" \
    sh -c "touch '$LOG_FILE' && tail -n 0 -F '$LOG_FILE'" \
    | jq --unbuffered -C '.'