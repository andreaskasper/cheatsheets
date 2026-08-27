# Mac-Hilfsskripte

Vier Kommandozeilenprogramme für die Arbeit mit Videobeständen auf dem Mac,
dazu ein Updater, der sie aktuell hält.

| Befehl | Wofür |
| --- | --- |
| `md5er` | Erzeugt für jede Datei eine `.md5`-Prüfsumme |
| `md5check` | Prüft die Dateien gegen ihre `.md5`-Dateien |
| `lfs_files2csv` | Liest die Metadaten aller Videodateien in eine Tabelle |
| `lfs_umzug` | Kopiert einen Bestand von einem NAS auf ein anderes |
| `update-andiscripts.sh` | Holt alle vier frisch von GitHub |

Alle vier kennen `--help`, `--version` und `update`.

## Installation

Einmalig im Terminal:

```bash
curl -sSfL https://raw.githubusercontent.com/andreaskasper/cheatsheets/refs/heads/master/mac_scripts/update-andiscripts.sh -o /tmp/update-andiscripts.sh
bash /tmp/update-andiscripts.sh
```

Der Updater legt alle fünf Dateien nach `/usr/local/bin` und macht sie
ausführbar. Fehlen die Schreibrechte, fragt er nach und startet sich mit `sudo`
neu; dann wird das Anmeldepasswort abgefragt.

`lfs_files2csv` braucht zusätzlich PHP und ffmpeg, `lfs_umzug` braucht rclone.
Der Updater sagt am Ende, ob etwas fehlt:

```bash
brew install php ffmpeg rclone
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
lfs_umzug update
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
lfs_files2csv /Volumes/Archiv/2024 +report         # auflisten, was nicht erfasst wurde
lfs_files2csv /Volumes/Archiv/2024 +dry            # nur zeigen, was passieren würde
lfs_files2csv /Volumes/Archiv/2024 +exclude=Proxy  # Pfade mit "Proxy" auslassen
lfs_files2csv /Volumes/Archiv/2024 +quiet          # nur das Nötigste ausgeben
lfs_files2csv /Volumes/Archiv/2024 +verbose        # ausführlich
```

`+md5` liest jede Datei komplett von der Platte. Bei einem Terabyte Material
dauert das je nach Anbindung Stunden, unabhängig vom Detailgrad. `+exclude=` darf
mehrfach angegeben werden.

### Welche Dateien nicht in der Tabelle stehen

Die Zusammenfassung am Ende sagt, wie viele Dateien nicht erfasst wurden:

```
Dateien insgesamt: 123
Videodateien: 117
Verarbeitet: 116
Übersprungen: 0
Fehler: 1
Nicht erfasst: 6 (5 ohne Videoendung, 1 versteckt)
```

Welche sechs das sind, sagt `+report`:

```bash
lfs_files2csv /Volumes/Archiv/2024 +report
```

Neben der Tabelle liegt danach `archiv_JJJJMMTTThhmmss.bericht.txt` mit einer
Zeile je Datei:

```
NICHT-VIDEO    /Volumes/Archiv/2024/Ablauf.pdf              [keine bekannte Videoendung]
AUSGELASSEN    /Volumes/Archiv/2024/._Band17.mov            [versteckte Datei]
AUSGELASSEN    /Volumes/Archiv/2024/Proxy                   [Ordner passt auf ein +exclude-Muster]
ERLEDIGT  /Volumes/Archiv/2024/Band03.mxf              [in einem früheren Lauf erledigt]
FEHLER         /Volumes/Archiv/2024/Band17.mov              [ffprobe konnte die Datei nicht lesen]
```

Am Ende des Berichts steht dieselbe Bilanz noch einmal in Zahlen. Wer nur die
Problemfälle sehen will:

```bash
grep FEHLER /Volumes/Archiv/archiv_20240314T101500.bericht.txt
```

`+dry +report` ist die schnelle Vorabprüfung: es wird keine Tabelle geschrieben,
aber man sieht vorher, was der richtige Lauf auslassen würde. Das lohnt sich vor
einem Durchgang, der Stunden dauert.

`FEHLER` heißt fast immer: die Datei ist beschädigt oder unvollständig kopiert.
`NICHT-VIDEO` sind Beilagen wie Textdateien, Bilder oder PDFs. Taucht dort
Material auf, das eigentlich Video ist, fehlt seine Endung in der Liste weiter
unten, und das gehört ins Skript ergänzt.

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
övnen. Wird sie trotzdem falsch dargestellt, hilft in Excel der Weg über
Daten, Aus Text/CSV, dort UTF-8 und Semikolon wählen.

## lfs_umzug

Kopiert einen Bestand von einem Ordner in einen anderen, typischerweise von
einem NAS auf ein anderes.

```bash
lfs_umzug /Volumes/NAS_alt/Sendungen /Volumes/NAS_neu/Sendungen
```

Es wird ausschließlich kopiert. Im Ziel wird nichts gelöscht, in der Quelle
erst recht nicht. Gleichnamige Dateien im Ziel werden überschrieben, die alte
Fassung wandert vorher in `.lfs_papierkorb_<zeitstempel>` neben dem Ziel.

### Was vor dem Start passiert

```
════════════════════════════════════════════════════════════
  Umzug
════════════════════════════════════════════════════════════
  Von:   /Volumes/NAS_alt/Sendungen
  Nach:  /Volumes/NAS_neu/Sendungen

  Dateien in der Quelle: 4.812
  Gesamtgröße:         6,4 TB
  Frei am Ziel:          11,2 TB

  [WARNUNG]   Der Zielordner ist NICHT leer. Es liegt bereits etwas darin.
  Gleichnamige Dateien werden überschrieben, alles andere bleibt.
  Papierkorb:  /Volumes/NAS_neu/.lfs_papierkorb_20240314T101500
  Protokoll:   /Volumes/NAS_neu/umzug_20240314T101500.log
════════════════════════════════════════════════════════════

Weiter? (j/N)
```

Der Bildschirm ist die eigentliche Sicherung: wer Quelle und Ziel vertauscht
hat, sieht es an der Richtung, bevor irgendetwas passiert. `+ja` überspringt die
Rückfrage.

Vorher wird außerdem geprüft, ob rclone installiert ist, ob die Quelle existiert
und nicht leer ist, ob das Ziel erreichbar ist, ob eines der beiden im anderen
liegt und ob der Platz reicht. Gibt es weder den Zielordner noch den Ordner
darüber, bricht das Programm mit dem Hinweis ab, dass das NAS vermutlich nicht
eingebunden ist. Ein nicht eingebundenes Volume sieht sonst aus wie ein leerer
Ordner, und dann landet das Archiv auf der internen Festplatte.

### Prüfen, dass die Kopie stimmt

```bash
lfs_umzug /Volumes/NAS_alt/Sendungen /Volumes/NAS_neu/Sendungen +md5
```

`+md5` ist der Weg für einen Archivumzug. Fehlen in der Quelle Prüfsummen, legt
`md5er` sie zuerst an. Die `.md5`-Dateien reisen mit, danach prüft `md5check`
das Ziel. Verglichen wird also gegen Werte, die vor dem Transport entstanden
sind, und der Bestand bleibt am Ziel dauerhaft nachweisbar.

`+verify` ist die Alternative ohne Prüfsummen: rclone vergleicht Quelle und Ziel
direkt. Braucht keine Vorbereitung, liest dafür beide Seiten und sagt nur, ob
die Kopie zur Quelle passt.

Beides findet Fehler, die rclone beim Kopieren selbst übersieht. rclone
vergleicht Dateien nach Größe und Änderungszeit; eine Datei, die am Ziel bei
gleicher Größe und Zeit inhaltlich kippt, fällt erst bei der Prüfung auf.

### Weitere Optionen

```bash
lfs_umzug QUELLE ZIEL +dry            # Trockenlauf, schreibt nichts
lfs_umzug QUELLE ZIEL +ja             # ohne Rückfrage
lfs_umzug QUELLE ZIEL +alles          # auch .DS_Store, ._* und Thumbs.db
lfs_umzug QUELLE ZIEL +chucknorris    # ohne Papierkorb
lfs_umzug QUELLE ZIEL +quiet          # ohne Fortschrittsbalken
lfs_umzug QUELLE ZIEL -report         # ohne Protokoll
lfs_umzug QUELLE ZIEL +limit=50M      # auf 50 MB/s bremsen
lfs_umzug QUELLE ZIEL +parallel=2     # zwei Dateien gleichzeitig
```

`+limit=` geht unverändert an rclone weiter, also funktioniert auch ein
Zeitplan: `+limit="08:00,20M 18:00,off"` bremst tagsüber und gibt abends frei.

### Wenn der Lauf abbricht

Denselben Befehl noch einmal aufrufen. rclone macht dort weiter, wo es
aufgehört hat, bereits vollständig übertragene Dateien werden übersprungen. Es
gibt keine Wiederaufnahme-Option, weil sie nicht nötig ist.

---

# Technischer Teil

## Aufbau

Fünf eigenständige Dateien ohne gemeinsame Bibliothek, jede für sich lauffähig.
`md5er`, `md5check`, `lfs_umzug` und `update-andiscripts.sh` sind Bash,
`lfs_files2csv` ist PHP mit `#!/usr/bin/env php`.

Der Updater kennt die Skriptliste als Array. Ein neues Skript ist eine Zeile in
`SCRIPTS=()`, mehr ist nicht nötig.

## Rückgabewerte

Alle Programme geben 0 zurück, wenn alles glatt lief, und 1, wenn es etwas zu
bemängeln gab.

`md5er` gibt 1 zurück, wenn mindestens eine Prüfsumme nicht geschrieben werden
konnte. `md5check` gibt 1 zurück, wenn mindestens eine Prüfsumme abweicht oder
fehlt. `lfs_files2csv` gibt 1 zurück, wenn mindestens eine Datei nicht gelesen
werden konnte oder der Aufruf fehlerhaft war. `lfs_umzug` gibt 1 zurück bei
Abbruch, Übertragungsfehler oder nicht bestandener Prüfung.
`update-andiscripts.sh` gibt 1 zurück, wenn mindestens ein Download fehlschlug.

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

## Bericht

`+report` hängt jede aussortierte Datei sofort an die Berichtsdatei an, statt
sie zu sammeln und am Ende auszugeben. Sonst wäre über den Umweg der Liste genau
das Speicherproblem zurück, das oben beschrieben ist. Gemessen an 55 000 Dateien
mit 5000 Nicht-Video-Dateien: unverändert 2 MB Spitzenspeicher, der Bericht ist
428 KB groß.

Die Datei wird angehängt, nicht überschrieben. Bei `+resume` steht der Bericht
des abgebrochenen Laufs also weiterhin darin, jeder Lauf beginnt mit einem
eigenen Kopf.

Aussortiert wird an zwei Stellen: Ordner beim Betreten (versteckt, Symlink,
`+exclude`), Dateien beim Prüfen der Endung. Der Zähldurchlauf am Anfang schreibt
nichts in den Bericht und erhöht keine Zähler, sonst stünde alles doppelt drin.

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

`md5er`, `md5check` und `lfs_files2csv` lassen alle versteckten Dateien und
Ordner aus. Dazu zählen die AppleDouble-Reste `._name.mov`, die auf exFAT- und
Netzlaufwerken neben dem Original liegen, denselben Namen und dieselbe Endung
tragen und kein Bild enthalten.

`lfs_umzug` ist bewusst großzügiger: es lässt nur die bekannten System- und
Mülldateien aus (`.DS_Store`, `._*`, `.Trashes`, `.Spotlight-V100`,
`.fseventsd`, `.TemporaryItems`, `.DocumentRevisions-V100`, `.apdisk`,
`Thumbs.db`, `desktop.ini`). Ein selbst angelegter versteckter Ordner zieht mit
um. Bei einem Umzug wäre stilles Weglassen der schlimmere Fehler. Die Folge:
`md5er` legt für versteckte Ordner keine Prüfsummen an, deren Inhalt kommt bei
`+md5` also ungeprüft am Ziel an.

Verknüpfte Ordner werden von `lfs_files2csv` nicht verfolgt, sonst läuft der
Durchlauf bei einer Schleife endlos. `lfs_umzug` überlässt das rclone, das
Verknüpfungen überspringt und im Protokoll vermerkt.

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

## Umzug

`lfs_umzug` ruft `rclone copy` auf, nie `rclone sync`. Der Unterschied ist der
Grund für die ganze Bauart: `sync` gleicht das Ziel an die Quelle an und löscht
dabei im Ziel alles, was in der Quelle fehlt, ohne Rückfrage und ohne
Papierkorb. Bei vertauschten Argumenten ist damit der Zielbestand weg, und
rclone kennt kein Rückgängig. `copy` fügt nur hinzu und überschreibt.

Überschriebene Dateien fängt `--backup-dir` ab. rclone verbietet einen
Backup-Ordner innerhalb des Ziels, deshalb liegt er daneben, im übergeordneten
Ordner. Ist das Ziel ein Volume-Wurzelordner, liegt "daneben" auf einer anderen
Platte; aus dem Verschieben wird dann ein echtes Kopieren. Das Skript vergleicht
die Geräte aus `df` und warnt in dem Fall. Ein leer gebliebener Papierkorb wird
am Ende entfernt.

Die Übersicht vor dem Start kostet einen `rclone size`-Durchlauf über die
Quelle, mit denselben Ausschlüssen wie der Umzug selbst. Ohne das nennt die
Übersicht eine Dateizahl, die später niemand wiederfindet.

Im Trockenlauf wird wirklich nichts geschrieben: kein Zielordner, kein
Protokoll. `--log-file` würde die Datei sonst auch bei `--dry-run` anlegen.

Bei einem Pfad in der Form `remote:ordner` entfallen alle Prüfungen auf
Erreichbarkeit, Platz und Verschachtelung, und das Skript sagt das. Solange
beide Pfade lokal sind, wird `RCLONE_CONFIG=/dev/null` gesetzt: ohne
Konfigurationsdatei meldet rclone sonst ein NOTICE, das wie ein Fehler aussieht.

Zwei Bash-Details, die auf dem Mac zählen: die Argumente werden mit `while` und
`shift` gelesen statt mit `for arg in "$@"`, weil das Bash 3.2 von macOS unter
`set -u` über ein leeres `"$@"` stolpert. Und die Prüfung, ob ein Pfad im
anderen liegt, läuft über `${DEST#"$SRC"/}` statt über ein `case`-Muster, sonst
würden eckige Klammern in einem Ordnernamen als Suchmuster gelesen.

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
rclone 1.75. Bash 5. Geprüft wurden CSV-, JSON- und XML-Ausgabe gegen die
jeweiligen Parser, Dateinamen mit Leerzeichen und Anführungszeichen, Abbruch und
Wiederaufnahme, Wiederaufnahme mit abweichenden Einstellungen, der Bericht mit
allen fünf Kategorien sowie ein Trockenlauf über 55 000 Dateien.

Für `lfs_umzug`: Papierkorb bei überschriebenen Dateien, vertauschte Argumente,
Ziel innerhalb der Quelle und umgekehrt, fehlende Quelle, nicht eingebundenes
Ziel, Trockenlauf ohne jeden Schreibzugriff, `+md5` und `+verify` im guten Fall
und gegen eine am Ziel bei gleicher Größe und Änderungszeit verfälschte Datei
(beide schlagen an, Rückgabewert 1), Systemdateien mit und ohne `+alles`,
Papierkorb auf einem anderen Volume und die Warnung bei rclone-Remotes.

Nicht automatisch geprüft werden konnte, was nur auf macOS auftritt: das
BSD-`md5`, das Verhalten von `sudo`, die Rechtesituation auf `/usr/local/bin`
und SMB-Freigaben als Quelle oder Ziel.
