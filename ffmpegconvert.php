#!/usr/bin/env php
<?php

if (php_sapi_name() != 'cli') die("Not in CLI".PHP_EOL);
$version = explode('.', PHP_VERSION);
if ($version < 7) die("php Version is lower than 7.0".PHP_EOL);

if (!empty($argv[1]) AND $argv[1] == "--update") app:self_update();
if (empty($argv[2]) OR in_array($argv[1], array("-h","--help"))) app::show_help();

$file = $argv[1];
$covert = $argv[2];

$rootdir = dirname($file);
$fileinfo = pathinfo($file);
if (empty($fileinfo["filename"])) die("Filename in File not found".PHP_EOL);



if (!file_exists($file)) die("File ".$file." doesn not exists".PHP_EOL);


switch (strtolower($covert)) {
    case "web":
        $cmd  = 'ffmpeg -i "'.$file.'" ';
        $cmd .= ' -threads 0 -vf "scale=-2:1080" -movflags +faststart "'.$fileinfo["filename"].'.1080p.mp4" ';
        $cmd .= ' -threads 0 -vf "scale=-2:720" -movflags +faststart "'.$fileinfo["filename"].'.720p.mp4" ';
        $cmd .= ' -threads 0 -vf "scale=-2:480" -movflags +faststart "'.$fileinfo["filename"].'.480p.mp4" ';
        $cmd .= ' -threads 0 -vf "scale=-2:240" -movflags +faststart "'.$fileinfo["filename"].'.240p.mp4" ';
        exec($cmd);
        break;
    case "webipfs":
        $cmd  = 'ffmpeg -i "'.$file.'" ';
        $cmd .= ' -threads 0 -vf "scale=-2:1080" -movflags +faststart "'.$fileinfo["filename"].'.1080p.mp4" ';
        $cmd .= ' -threads 0 -vf "scale=-2:720" -movflags +faststart "'.$fileinfo["filename"].'.720p.mp4" ';
        $cmd .= ' -threads 0 -vf "scale=-2:480" -movflags +faststart "'.$fileinfo["filename"].'.480p.mp4" ';
        $cmd .= ' -threads 0 -vf "scale=-2:240" -movflags +faststart "'.$fileinfo["filename"].'.240p.mp4" ';
        exec($cmd);
        exec('ipfs add "'.$fileinfo["filename"].'.1080p.mp4"');
        exec('ipfs add "'.$fileinfo["filename"].'.720p.mp4"');
        exec('ipfs add "'.$fileinfo["filename"].'.480p.mp4"');
        exec('ipfs add "'.$fileinfo["filename"].'.240p.mp4"');
        break;
	case "1080p":
        $cmd  = 'ffmpeg -i "'.$file.'" ';
        $cmd .= ' -threads 0 -vf "scale=-2:1080" -movflags +faststart "'.$fileinfo["filename"].'.1080p.'.$fileinfo["extension"].'" ';
        exec($cmd);
        break;
	case "720p":
        $cmd  = 'ffmpeg -i "'.$file.'" ';
        $cmd .= ' -threads 0 -vf "scale=-2:1080" -movflags +faststart "'.$fileinfo["filename"].'.720p.'.$fileinfo["extension"].'" ';
        exec($cmd);
        break;
	case "480p":
        $cmd  = 'ffmpeg -i "'.$file.'" ';
        $cmd .= ' -threads 0 -vf "scale=-2:1080" -movflags +faststart "'.$fileinfo["filename"].'.480p.'.$fileinfo["extension"].'" ';
        exec($cmd);
        break;
	case "360p":
        $cmd  = 'ffmpeg -i "'.$file.'" ';
        $cmd .= ' -threads 0 -vf "scale=-2:1080" -movflags +faststart "'.$fileinfo["filename"].'.360p.'.$fileinfo["extension"].'" ';
        exec($cmd);
        break;
	case "240p":
        $cmd  = 'ffmpeg -i "'.$file.'" ';
        $cmd .= ' -threads 0 -vf "scale=-2:1080" -movflags +faststart "'.$fileinfo["filename"].'.240p.'.$fileinfo["extension"].'" ';
        exec($cmd);
        break;
	case "1080p.mp4":
        $cmd  = 'ffmpeg -i "'.$file.'" ';
        $cmd .= ' -threads 0 -vf "scale=-2:1080" -movflags +faststart "'.$fileinfo["filename"].'.1080p.mp4" ';
        exec($cmd);
        break;
	case "720p.mp4":
        $cmd  = 'ffmpeg -i "'.$file.'" ';
        $cmd .= ' -threads 0 -vf "scale=-2:1080" -movflags +faststart "'.$fileinfo["filename"].'.720p.mp4" ';
        exec($cmd);
        break;
	case "480p.mp4":
        $cmd  = 'ffmpeg -i "'.$file.'" ';
        $cmd .= ' -threads 0 -vf "scale=-2:1080" -movflags +faststart "'.$fileinfo["filename"].'.480p.mp4" ';
        exec($cmd);
        break;
	case "360p.mp4":
        $cmd  = 'ffmpeg -i "'.$file.'" ';
        $cmd .= ' -threads 0 -vf "scale=-2:1080" -movflags +faststart "'.$fileinfo["filename"].'.360p.mp4" ';
        exec($cmd);
        break;
	case "240p.mp4":
        $cmd  = 'ffmpeg -i "'.$file.'" ';
        $cmd .= ' -threads 0 -vf "scale=-2:1080" -movflags +faststart "'.$fileinfo["filename"].'.240p.mp4" ';
        exec($cmd);
        break;
    case "hls":
        @mkdir($fileinfo["filename"]);
        @mkdir($fileinfo["filename"]."/stream");
        $cmd  = 'ffmpeg -i "'.$file.'" ';
        $cmd .= ' -threads 0 -f hls -hls_time 10 -hls_playlist_type vod -hls_flags independent_segments -hls_segment_type mpegts -hls_segment_filename "'.$fileinfo["filename"].'/stream/v%05d.ts" -var_stream_map "v:0,a:0" "'.$fileinfo["filename"].'/hls.m3u8"';
        exec($cmd);
        $m3u8content = file_get_contents($fileinfo["filename"]."/hls.m3u8");
        $m3u8content = preg_replace("@^v@mi", "stream/v", $m3u8content);
        file_put_contents($fileinfo["filename"]."/hls.m3u8", $m3u8content);
        break;
    case "hlsipfs":
        @mkdir($fileinfo["filename"]);
        @mkdir($fileinfo["filename"]."/stream");
        $cmd  = 'ffmpeg -i "'.$file.'" ';
        $cmd .= ' -threads 0 -f hls -hls_time 10 -hls_playlist_type vod -hls_flags independent_segments -hls_segment_type mpegts -hls_segment_filename "'.$fileinfo["filename"].'/stream/v%05d.ts" -var_stream_map "v:0,a:0" "'.$fileinfo["filename"].'/hls.m3u8"';
        exec($cmd);
        $m3u8content = file_get_contents($fileinfo["filename"]."/hls.m3u8");
        $files = scandir($fileinfo["filename"]."/stream/");
        foreach ($files as $file) {
            if (substr($file,0,1) == ".") continue;
            unset($a);
            exec('ipfs add "'.$rootdir.'/'.$fileinfo["filename"]."/stream/".$file.'"', $a, $a2);
            if (!preg_match ("@added (?P<ipfs>[A-Za-z0-9]+) (?P<file>v[0-9]+\.ts)@", implode($a), $m)) die("Problem ".PHP_EOL);
            print_r($m);
            $m3u8content = str_replace($m["file"], $m["ipfs"], $m3u8content);
        }
        file_put_contents($fileinfo["filename"]."/hlsipfs.m3u8", $m3u8content);
        break;
    case "poster":
        $cmd  = 'ffmpeg -i "'.$file.'" ';
        $cmd .= ' -threads 0 -ss 01:00 -frames:v 1 "'.$fileinfo["filename"].'.poster.jpg"';
		$cmd .= ' -ss 01:00 -vf "scale=-2:360" -frames:v 1 "'.$fileinfo["filename"].'.poster.360p.jpg"';
        exec($cmd);
        break;
    default:
        echo('Unknown Conversion "'.$covert.'". Try:'.PHP_EOL);
		app::show_help();
}


class System {

    const OS_UNKNOWN = 1;
    const OS_WIN = 2;
    const OS_LINUX = 3;
    const OS_OSX = 4;

    /**
     * @return int
     */
    static public function getOS() {
        switch (true) {
            case stristr(PHP_OS, 'DAR'): return self::OS_OSX;
            case stristr(PHP_OS, 'WIN'): return self::OS_WIN;
            case stristr(PHP_OS, 'LINUX'): return self::OS_LINUX;
            default : return self::OS_UNKNOWN;
        }
    }

}

class app {
	public static function show_help() {
		echo('Example:'.PHP_EOL);
		echo('ffmpegconvert <File> <convert>'.PHP_EOL);
		echo(PHP_EOL);
		echo('Conversions:'.PHP_EOL);
		echo('[*] hls'.PHP_EOL);
        echo('[*] hlsipfs'.PHP_EOL);
        echo('[*] web        Konvertiert ein Video in mehrer webbasierte mp4 Formate'.PHP_EOL);
        echo('[*] webipfs'.PHP_EOL);
		echo('[*] 1080p      Konvertiert ein Video in ein 1080p Video'.PHP_EOL);
		echo('[*] 720p       Konvertiert ein Video in ein 720p Video'.PHP_EOL);
		echo('[*] 480p       Konvertiert ein Video in ein 480p Video'.PHP_EOL);
		echo('[*] 360p       Konvertiert ein Video in ein 360p Video'.PHP_EOL);
		echo('[*] 240p       Konvertiert ein Video in ein 240p Video'.PHP_EOL);
		echo('[*] 1080p.mp4  Konvertiert ein Video in ein 1080p mp4 Video'.PHP_EOL);
		echo('[*] 720p.mp4   Konvertiert ein Video in ein 720p mp4 Video'.PHP_EOL);
		echo('[*] 480p.mp4   Konvertiert ein Video in ein 480p mp4 Video'.PHP_EOL);
		echo('[*] 360p.mp4   Konvertiert ein Video in ein 360p mp4 Video'.PHP_EOL);
		echo('[*] 240p.mp4   Konvertiert ein Video in ein 240p mp4 Video'.PHP_EOL);
		exit();
	}
	
	public static function self_update() {
		copy("",__FILE__);
	}
}
