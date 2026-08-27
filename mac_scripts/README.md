# Mac-Hilfsskripte

Drei Kommandozeilenprogramme für die Arbeit mit Videobeständen auf dem Mac,
dazu ein Updater, der sie aktuell hält.

| Befehl | Wofür |
| --- | --- |
| `md5er` | Erzeugt für jede Datei eine `.md5`-Prüfsumme |
| `md5check` | Prüft die Dateien gegen ihre `.md5`-Dateien |
| `lfs_files2csv` | Liest die Metadaten aller Videodateien in eine Tabelle |
| `update-andiscripts.sh` | Holt alle drei frisch von GitHub |

Alle drei kennen `--help`, `--version` und `update`.

## Installation

Einmalig im Terminal:

```bash
curl -sSfL https://raw.githubusercontent.com/andreaskasper/cheatsheets/refs/heads/master/mac_scripts/update-andiscripts.sh -o /tmp/update-andiscripts.sh
bash /tmp/update-andiscripts.sh
```

Der Updater legt alle vier Dateien nach `/usr/local/bin` und macht sie
ausführbar. Fehlen die Schreibrechte, fragt er nach und startet sich mit `sudo`
neu; dann wird das Anmeldepasswort abgefragt.

`lfs_files2csv` braucht zusätzlich PHP und ffmpeg. Der Updater sagt am Ende, ob
etwas fehlt:

```bash
brew install php ffmpeg
```

`md5er` und `md5check` laufen ohne weitere Installation, sie nutzen das
mitgelieferte `md5` von macOS.

## Aktualisieren

```bash
update-andiscripts.sh
```

Geht auch aus jedem der Programme heraus, das ist derselbe Vorgang:

```bash
md5er update
md5check update
lfs_files2csv update
```

---

## md5er

Legt neben jeder Datei eine `.md5`-Datei mit ihrer Prüfsumme an. Damit lässt
sich später belegen, dass eine Kopie unverändert ist.

```bash
md5er /Volumes/Archiv/2024
```

Ohne Ordnerangabe fragt das Programm, ob der aktuelle Ordner gemeint ist.

Bereits vorhandene `.md5`-Dateien werden übersprungen. Ein zweiter Lauf über
denselben Bestand kostet deshalb kaum Zeit und lässt sich nutzen, um neu
hinzugekommenes Material nachzutragen.

Versteckte Dateien und Ordner werden ausgelassen. Auf Mac-Volumes sind das unter
anderem `.Trashes`, `.Spotlight-V100` und `.fseventsd`, deren Inhalt nicht zum
Bestand gehört.

Am Ende steht eine Bilanz:

```
Neu erzeugt: 128
Schon vorhanden: 4021
Fehler: 0
```

Ein Fehler heißt hier, dass die Prüfsumme nicht geschrieben werden konnte, etwa
weil das Volume schreibgeschützt ist. In dem Fall bleibt keine halbe `.md5`-Datei
zurück.

## md5check

Prüft jede Datei gegen ihre `.md5`-Datei.

```bash
md5check /Volumes/Archiv/2024
```

Drei mögliche Meldungen je Datei:

Ein grünes Häkchen heißt, die Prüfsumme stimmt. Eine gelbe Warnung heißt, es gibt
keine oder eine leere `.md5`-Datei; die Datei wurde also nicht geprüft. Eine rote
Fehlermeldung heißt, die Prüfsumme weicht ab, die Datei hat sich seit dem Anlegen
der Prüfsumme verändert. Bei einer Abweichung werden beide Werte ausgegeben.

Am Ende steht die Bilanz und ein Gesamturteil:

```
Geprüft und in Ordnung: 4149
Ohne Prüfsumme: 0
Abweichend: 0
Ergebnis: alles in Ordnung
```

## lfs_files2csv

Durchsucht einen Ordner rekursiv nach Videodateien und schreibt die Metadaten
jeder Datei in eine Tabelle: Dauer, Auflösung, Codec, Bildrate, Timecode und
je nach Detailgrad noch einiges mehr.

```bash
lfs_files2csv /Volumes/Archiv/2024
```

Die Ergebnisdatei heißt `archiv_JJJJMMTTThhmmss.csv` und landet im
übergeordneten Ordner des durchsuchten Ordners, im Beispiel also in
`/Volumes/Archiv/`. Das durchsuchte Material selbst wird nie angefasst.

### Detailgrad

Je mehr Angaben, desto länger dauert der Lauf.

```bash
lfs_files2csv /Volumes/Archiv/2024 +info=more
```

`standard` liefert die Basisdaten und ist die Voreinstellung. `more` ergänzt Ton
und erweiterte Bildangaben, `max` zusätzlich Qualitäts- und Technikwerte,
`full` alle Streams und Diagnoseangaben.

Richtwerte pro Datei: standard rund 0,1 Sekunden, more 0,2, max 0,5, full 1 bis 2.
Bei 5000 Dateien ist das der Unterschied zwischen zehn Minuten und mehreren
Stunden.

### Weitere Optionen

```bash
lfs_files2csv /Volumes/Archiv/2024 +md5            # MD5 und SHA1 mitberechnen
lfs_files2csv /Volumes/Archiv/2024 +json           # JSON statt CSV
lfs_files2csv /Volumes/Archiv/2024 +xml            # XML statt CSV
lfs_files2csv /Volumes/Archiv/2024 +raw            # rohe ffprobe-Ausgabe als Spalte
lfs_files2csv /Volumes/Archiv/2024 +dry            # nur zeigen, was passieren würde
lfs_files2csv /Volumes/Archiv/2024 +exclude=Proxy  # Pfade mit "Proxy" auslassen
lfs_files2csv /Volumes/Archiv/2024 +quiet          # nur das Nötigste ausgeben
lfs_files2csv /Volumes/Archiv/2024 +verbose        # ausführlich
```

`+md5` liest jede Datei komplett von der Platte. Bei einem Terabyte Material
dauert das je nach Anbindung Stunden, unabhängig vom Detailgrad. `+exclude=` darf
mehrfach angegeben werden.

### Abgebrochenen Lauf fortsetzen

Während der Verarbeitung wird jede fertige Datei in einer versteckten
Fortschrittsdatei vermerkt. Wurde ein Lauf abgebrochen, etwa durch Strg+C, durch
einen Neustart oder ein ausgehängtes Volume, geht es so weiter:

```bash
lfs_files2csv /Volumes/Archiv/2024 +resume
```

Der Lauf schreibt dann in dieselbe Ergebnisdatei weiter und überspringt, was
schon erledigt ist. Die Einstellungen müssen dieselben sein wie beim ersten Lauf.
Sind sie es nicht, fängt das Programm mit einer neuen Datei von vorn an, statt
Zeilen mit unterschiedlichen Spalten zu mischen.

Nach einem vollständig fehlerfreien Lauf wird die Fortschrittsdatei gelöscht.
Gab es Fehler, bleibt sie liegen, damit `+resume` möglich bleibt.

### Wenn etwas nicht klappt

`ffprobe not found`: ffmpeg ist nicht installiert. `brew install ffmpeg`.

`command not found: lfs_files2csv`: das Programm liegt nicht in `/usr/local/bin`
oder dieser Ordner ist nicht im Suchpfad. `update-andiscripts.sh` erneut laufen
lassen.

Einzelne Dateien werden als Fehler gemeldet: die Datei ist beschädigt, unvollständig
kopiert oder kein Video. Der Lauf geht weiter, die Datei fehlt in der Tabelle. Der
Rückgabewert des Programms ist dann 1.

Umlaute in Excel falsch: die CSV-Datei wird mit Byte-Order-Mark und Semikolon als
Trennzeichen geschrieben, damit Excel und Numbers sie ohne Nacharbeit richtig
öffnen. Wird sie trotzdem falsch dargestellt, hilft in Excel der Weg über
Daten, Aus Text/CSV, dort UTF-8 und Semikolon wählen.

---

# Technischer Teil

## Aufbau

Vier eigenständige Dateien ohne gemeinsame Bibliothek, jede für sich lauffähig.
`md5er`, `md5check` und `update-andiscripts.sh` sind Bash, `lfs_files2csv` ist
PHP mit `#!/usr/bin/env php`.

Der Updater kennt die Skriptliste als Array. Ein neues Skript ist eine Zeile in
`SCRIPTS=()`, mehr ist nicht nötig.

## Rückgabewerte

Alle drei Programme geben 0 zurück, wenn alles glatt lief, und 1, wenn es etwas
zu bemängeln gab.

`md5er` gibt 1 zurück, wenn mindestens eine Prüfsumme nicht geschrieben werden
konnte. `md5check` gibt 1 zurück, wenn mindestens eine Prüfsumme abweicht oder
fehlt. `lfs_files2csv` gibt 1 zurück, wenn mindestens eine Datei nicht gelesen
werden konnte oder der Aufruf fehlerhaft war. `update-andiscripts.sh` gibt 1
zurück, wenn mindestens ein Download fehlschlug.

## Fortschritt und Wiederaufnahme

Die Fortschrittsdatei heißt `.lfs_progress_<hash>.jsonl` und liegt im
übergeordneten Ordner des durchsuchten Ordners. Der Hash sind die ersten zwölf
Zeichen des MD5 über den absoluten Pfad des durchsuchten Ordners. Dadurch findet
ein späterer Lauf die Datei desselben Bestandes wieder, und parallele Läufe über
verschiedene Ordner kommen sich nicht ins Gehege.

Aufbau: die erste Zeile ist ein JSON-Objekt mit Version, Ordner, Ausgabedatei,
Format, Detailgrad und den Schaltern für Prüfsumme und Rohdaten. Jede weitere
Zeile ist ein JSON-kodierter Dateipfad. Angehängt wird zeilenweise, es wird nie
etwas neu geschrieben.

Bei `+resume` wird die Kopfzeile gegen die aktuellen Einstellungen geprüft. Nur
bei vollständiger Übereinstimmung wird fortgesetzt.

## Speicherverhalten

Bis Fassung 3.0.2 gab es zwei Stellen, deren Bedarf mit der Dateizahl wuchs, und
bei großen Beständen führte das zu `Allowed memory size exhausted`:

Der Verzeichnisdurchlauf sammelte alle Pfade in einem Array, bevor die erste
Datei verarbeitet wurde. Bei 300 000 Dateien mit archivtypischen Pfadlängen sind
das rund 66 MB nur für die Liste.

Schwerer wog die Fortschrittsliste: sie wurde als Array gehalten und nach *jeder*
Datei komplett per `json_encode` neu serialisiert und geschrieben. Der Aufwand
wächst quadratisch. Bei 20 000 Dateien waren allein dafür rund 45 Sekunden reine
Rechenzeit und etwa 28 GB geschriebene Daten angefallen, und die Suche mit
`in_array` durch dieselbe Liste kam obendrauf.

Seit 3.1.0 liefert `iterateFiles()` die Pfade als Generator; im Speicher liegen
nur die noch nicht besuchten Unterordner. Die Fortschrittsdatei wird zeilenweise
angehängt, und das Nachschlagen läuft über Array-Schlüssel statt `in_array`.
Gemessen an 50 000 Dateien: 2 MB Spitzenspeicher für den PHP-Teil.

Für die Prozentanzeige braucht es die Gesamtzahl, die beim Streamen nicht mehr
nebenbei anfällt. Deshalb läuft vorweg ein reiner Zähldurchlauf, der keine Pfade
speichert. Auf langsamen Netzlaufwerken kostet das etwas Zeit vor dem Start.

`ini_set('memory_limit', '-1')` steht weiterhin oben im Skript, jetzt aber als
Sicherheitsgurt für einzelne sehr große ffprobe-Ausgaben und nicht mehr, um ein
strukturelles Problem zu überdecken.

## Spalten

Die Spalten sind an genau einer Stelle definiert, in `getColumns()`. Kopfzeile,
CSV-Zeile, JSON-Objekt und XML-Element leiten sich daraus ab, deshalb können
Kopf und Inhalt nicht auseinanderlaufen. Jeder Eintrag ist ein Tripel aus
Beschriftung, Schlüssel und Typ (`text`, `int`, `float`).

Spaltenzahl je Detailgrad, ohne `+raw`: standard 22, more 34, max 45, full 56.
Mit `+raw` kommt jeweils die Spalte `data` mit der vollständigen
ffprobe-Ausgabe hinzu.

CSV wird mit Semikolon getrennt, mit UTF-8-BOM eingeleitet und nach
CSV-Konvention maskiert, Anführungszeichen also verdoppelt. Bis 3.0.2 stand dort
`addslashes`, was jede Zeile mit einem Anführungszeichen im Pfad und mit `+raw`
praktisch jede Zeile unbrauchbar machte. Zeilenumbrüche innerhalb eines Feldes
werden durch Leerzeichen ersetzt.

Im XML werden Steuerzeichen entfernt, die in XML 1.0 unabhängig von jeder
Maskierung nicht vorkommen dürfen.

## Was ausgelassen wird

Versteckte Dateien und Ordner in allen drei Programmen. Dazu zählen die
AppleDouble-Reste `._name.mov`, die auf exFAT- und Netzlaufwerken neben dem
Original liegen, denselben Namen und dieselbe Endung tragen und kein Bild
enthalten.

Verknüpfte Ordner werden nicht verfolgt, sonst läuft der Durchlauf bei einer
Schleife endlos.

## Erkannte Endungen

`.mp4 .mov .avi .mpg .mpeg .mkv .wmv .flv .webm .m4v .3gp .asf .mxf .mts .m2ts
.dv .vob .ts .m2v .ogv .rm`

Die neun letzten kamen mit 3.1.0 dazu. MXF und MTS/M2TS sind in Fernseh- und
Kameraarchiven verbreitet; vorher tauchte solches Material im Ergebnis nicht auf,
ohne dass eine Meldung darauf hinwies.

## Nicht umgesetzt

Vier Felder im Detailgrad `full` bleiben leer: `Black_Frames_Start`,
`Black_Frames_End`, `Scene_Changes` und `Duplicate_Frames`. Sie brauchen eine
vollständige Dekodierung des Materials über `ffmpeg` mit `blackdetect`, `select`
beziehungsweise `mpdecimate`. Das dauert je Datei ein Vielfaches der übrigen
Analyse. `GOP_Size` ist aus derselben Überlegung leer.

`Video_Bitrate` ist eine Schätzung, wenn der Videostream selbst keine Bitrate
meldet: dann werden 80 Prozent der Gesamtbitrate angesetzt.

## Aktualisieren des Updaters

Der Updater steht in seiner eigenen Liste an letzter Stelle und ersetzt sich
selbst. Das geht, weil die neue Fassung mit `mv` an ihren Platz kommt: `mv`
tauscht den Verzeichniseintrag in einem Schritt aus, die bereits laufende Bash
behält ihre alte Fassung geöffnet.

Aus demselben Grund wird jede Datei erst nach `/tmp` geladen und dort auf
Mindestgröße und `#!`-Zeile geprüft. Ein abgebrochener Download überschreibt so
kein funktionierendes Skript.

## Getestet mit

PHP 8.1 und 8.5, jeweils mit `error_reporting=E_ALL` ohne Meldungen. ffprobe 4.4.
Bash 5. Geprüft wurden CSV-, JSON- und XML-Ausgabe gegen die jeweiligen Parser,
Dateinamen mit Leerzeichen und Anführungszeichen, Abbruch und Wiederaufnahme,
Wiederaufnahme mit abweichenden Einstellungen sowie ein Trockenlauf über
50 000 Dateien.

Nicht automatisch geprüft werden konnte, was nur auf macOS auftritt: das
BSD-`md5`, das Verhalten von `sudo` und die Rechtesituation auf `/usr/local/bin`.
