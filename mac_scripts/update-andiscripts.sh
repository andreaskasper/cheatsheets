#!/bin/bash
#
# Holt die Mac-Hilfsskripte frisch von GitHub und legt sie nach /usr/local/bin.
#
# Ein Skript fuer Manuel vom Haus des Dokumentarfilms
# Autor: Andreas Kasper <andreas.kasper@goo1.de>

set -u

VERSION="v1.3.260827"

TARGET_DIR="/usr/local/bin"
BASE_URL="https://raw.githubusercontent.com/andreaskasper/cheatsheets/refs/heads/master/mac_scripts"

# Neue Skripte hier eintragen, sonst nichts. update-andiscripts.sh steht
# absichtlich zuletzt: es ersetzt sich selbst, und das soll erst passieren,
# wenn alles andere durch ist.
SCRIPTS=(
  "md5er"
  "md5check"
  "lfs_files2csv"
  "lfs_umzug"
  "update-andiscripts.sh"
)

MIN_BYTES=200

show_help() {
  cat <<'EOF'
Verwendung: update-andiscripts.sh [optionen]

Laedt die aktuellen Fassungen der Hilfsskripte von GitHub und installiert sie
nach /usr/local/bin. Bereits vorhandene Fassungen werden ersetzt.

Optionen:
  --help      Diese Hilfe
  --version   Versionsinformationen

Installiert werden:
  md5er                  Pruefsummen erzeugen
  md5check               Pruefsummen kontrollieren
  lfs_files2csv          Video-Metadaten in eine Tabelle schreiben
  lfs_umzug              Bestaende von einem NAS auf ein anderes kopieren
  update-andiscripts.sh  dieses Skript

Braucht Schreibrechte auf /usr/local/bin. Fehlen sie, fragt das Skript nach und
startet sich mit sudo neu.
EOF
}

show_version() {
  echo "update-andiscripts.sh $VERSION"
  echo "Quelle: $BASE_URL"
}

case "${1:-}" in
  --help|-h|help|\?)
    show_help
    exit 0
    ;;
  --version|-v|version)
    show_version
    exit 0
    ;;
  "")
    ;;
  *)
    echo "Unbekannte Option: $1" >&2
    echo "Hilfe mit: update-andiscripts.sh --help" >&2
    exit 1
    ;;
esac

# Eigener absoluter Pfad, damit der Neustart mit sudo auch dann klappt, wenn das
# Skript ueber einen relativen Pfad aufgerufen wurde.
SELF="$(cd "$(dirname "$0")" 2>/dev/null && pwd)/$(basename "$0")"

if [ ! -d "$TARGET_DIR" ]; then
  echo "Zielordner $TARGET_DIR existiert nicht."
  echo "Bitte einmalig anlegen:  sudo mkdir -p $TARGET_DIR"
  exit 1
fi

if [ ! -w "$TARGET_DIR" ]; then
  if [ "$(id -u)" -eq 0 ]; then
    echo "Auch als root nicht schreibbar: $TARGET_DIR" >&2
    exit 1
  fi

  echo "Keine Schreibrechte auf $TARGET_DIR."

  if [ ! -t 0 ] || [ ! -f "$SELF" ]; then
    echo "Bitte erneut aufrufen mit:  sudo $0"
    exit 1
  fi

  read -r -p "Mit sudo fortfahren? Dein Passwort wird abgefragt. (j/N) " answer
  case "$answer" in
    j|J|ja|Ja|y|Y|yes)
      exec sudo "$SELF"
      ;;
    *)
      echo "Abgebrochen."
      exit 1
      ;;
  esac
fi

WORK_DIR="$(mktemp -d)"
trap 'rm -rf "$WORK_DIR"' EXIT

failed=0
installed=0

for script in "${SCRIPTS[@]}"; do
  echo "👨‍💻 Lade $script …"

  tmp_file="$WORK_DIR/$script"

  if ! curl -sSfL "$BASE_URL/$script" -o "$tmp_file"; then
    echo "  ❌ fehlgeschlagen, $script bleibt unveraendert." >&2
    failed=$((failed + 1))
    continue
  fi

  # Ein abgebrochener Download liefert oft eine kurze oder leere Datei, die
  # trotzdem ausfuehrbar waere. Deshalb vor dem Installieren pruefen.
  size=$(wc -c < "$tmp_file" | tr -d ' ')
  if [ "$size" -lt "$MIN_BYTES" ]; then
    echo "  ⚠️ Datei ist nur $size Byte gross, das sieht nach Abbruch aus. Uebersprungen." >&2
    failed=$((failed + 1))
    continue
  fi

  if ! head -c 2 "$tmp_file" | grep -q '#!'; then
    echo "  ⚠️ Datei beginnt nicht mit #!, das ist kein Skript. Uebersprungen." >&2
    failed=$((failed + 1))
    continue
  fi

  chmod +x "$tmp_file"

  # mv ersetzt den Verzeichniseintrag in einem Schritt. Ein bereits laufendes
  # Skript behaelt seine alte Fassung geoeffnet, deshalb darf sich dieses
  # Skript hier auch selbst ueberschreiben.
  if mv -f "$tmp_file" "$TARGET_DIR/$script"; then
    echo "  ✅ installiert nach $TARGET_DIR/$script"
    installed=$((installed + 1))
  else
    echo "  ❌ konnte nicht nach $TARGET_DIR verschoben werden." >&2
    failed=$((failed + 1))
  fi
done

echo
echo "Installiert: $installed, fehlgeschlagen: $failed"

# Voraussetzungen pruefen. Die Skripte selbst laufen, ihre Werkzeuge fehlen aber.
missing=""
command -v php >/dev/null 2>&1    || missing="$missing php"
command -v ffprobe >/dev/null 2>&1 || missing="$missing ffprobe"
command -v rclone >/dev/null 2>&1  || missing="$missing rclone"

if [ -n "$missing" ]; then
  echo
  echo "⚠️ Es fehlen noch:$missing"
  case "$missing" in
    *php*) echo "  php     fuer lfs_files2csv:  brew install php" ;;
  esac
  case "$missing" in
    *ffprobe*) echo "  ffprobe fuer lfs_files2csv:  brew install ffmpeg" ;;
  esac
  case "$missing" in
    *rclone*) echo "  rclone  fuer lfs_umzug:      brew install rclone" ;;
  esac
fi

echo "🕰️ Update abgeschlossen: $(date '+%Y-%m-%d %H:%M:%S')"

if [ "$failed" -gt 0 ]; then
  exit 1
fi

exit 0
