#!/bin/bash

# Zielpfad, wo deine Skripte liegen
TARGET_DIR="/usr/local/bin"

# GitHub-Roh-URL deiner Skripte
BASE_URL="https://raw.githubusercontent.com/andreaskasper/cheatsheets/refs/heads/master/mac_scripts"

echo "👨‍💻 Install md5er…"
echo 'curl -sSf "$BASE_URL/md5er" -o "$TARGET_DIR/md5er" && chmod +x "$TARGET_DIR/md5er"'
curl -sSf "$BASE_URL/md5er" -o "$TARGET_DIR/md5er" && chmod +x "$TARGET_DIR/md5er"

echo "👨‍💻 Install md5check…"
curl -sSf "$BASE_URL/md5check" -o "$TARGET_DIR/md5check" && chmod +x "$TARGET_DIR/md5check"

echo "👨‍💻 Update update-andiscripts.sh…"
curl -sSf "$BASE_URL/update-andiscripts.sh" -o "$TARGET_DIR/update-andiscripts.sh" && chmod +x "$TARGET_DIR/update-andiscripts.sh"

echo "Update abgeschlossen: $(date '+%Y-%m-%d %H:%M:%S')"