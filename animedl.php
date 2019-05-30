<?php
$source_name = strtolower(trim(@$argv[1])); // Source to download from
$program_name = strtolower(trim(@$argv[2])); // Name of program you wish to download
$custom_program_title = strtolower(trim(@$argv[3])); // Downloaded program files use this name if set

ini_set('display_errors', 1);
error_reporting(-1);

require_once 'vendor/autoload.php';
require_once 'util.php';
require_once 'simple_html_dom.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

$sources[] = ['name' => 'animejpnsub.ezyro.com', 'url' => 'http://animejpnsub.ezyro.com'];
// $sources[] = ['name' => 'daiweeb.org', 'url' => 'https://www.daiweeb.org/terakoya'];

// Find an exact match in an array from a specified key
function array_search_key_value_match($array, $key, $value) {
    $array_key = array_search($value, array_column($array, $key));
    return (is_int($array_key) ? $array_key : null);
}

// Get the specified source and warn if it does not exist
$source = @$sources[array_search_key_value_match($sources, 'name', $source_name)];
if (!$source) {
    echo "Unknown source: $source_name\n\nValid sources are:\n";

    foreach ($sources as $source) {
        echo $source['name'] . "\n";
    }

    return;
}

// Pull program listing from source or use cache if it exists
$json_file = 'cache/' . $source['name'] . '.json';
if (cached_file_does_not_exist($json_file)) {
    write($json_file, get_program_list($source));
};

$program_list = json_decode(cache_file($json_file), true);

// Display a listing of available programs if no program name was supplied
if (!$program_name) {
    echo "The following programs are available to download:\n";

    foreach ($program_list as $program) {
        echo $program['title_en'];
        if (isset($program['title_ja'])) echo ' — ' . $program['title_ja'];
        echo "\n";
    }

    die();
}

// Check the program we are after exists and warn if not found
if (!isset($program_list[$program_name])) {
    echo "Program '$program_name' was not found. Run command without specifying a program name to see a listing of available programs. \n";
    die();
}

$video_links = $program_list[$program_name]['video_links'];
$japanese_program_name = $program_list[$program_name]['title_ja'];
$english_program_name = $program_list[$program_name]['title_en'];
$url_list = '';
$shell_script = "#!/bin/sh\n";
$i = 0;

foreach ($video_links as $video_id => $video_link) {
    $i++;

    $media_urls = get_media_urls($source['url'], $video_link);
    foreach($media_urls as $key => $media_url) {
        
        if ($custom_program_title) {
            $episode_name = $custom_program_title;
        } else {
            $episode_name = $japanese_program_name;
        }

        // Work out how to name the file
        if (is_numeric($video_id)) {
            // $episode_name = 'E' . pad_int($video_id, 2) . ' ' . $episode_name;
            $episode_name = '第' . pad_int($video_id, 2) . '話 ' . $episode_name;
        } else if ($video_id == 'movie') {
            $episode_name = $episode_name;
        } else {
            $episode_name = $video_id . ' ' . $episode_name;
        }

        $file_name = 'downloads/' . $japanese_program_name . '/' .$episode_name;

        if ($key == 'subtitle_ja_url') {
            if (!file_exists($file_name . '.ja.vtt')) {
                write($file_name . '.ja.vtt', strip_html(get_page_source($media_url)));
            }
        } else if ($key == 'subtitle_en_url') {
            if (!file_exists($file_name . '.en.vtt')) {
                write($file_name . '.en.vtt', strip_html(get_page_source($media_url)));
            }
        } else if ($key == 'video_url') {
            $shell_script .= 'wget -c ' . $media_url . ' -O "' . $episode_name . ".mp4\"\n";
        }
    }
}

// Save shell script, run it and then delete it
$shell_script_path = 'downloads/' . $japanese_program_name . '/';
$shell_script_name = 'download-' . slug($english_program_name) . '-mp4.sh';
write($shell_script_path . $shell_script_name, $shell_script);
chdir($shell_script_path);
exec('./' . $shell_script_name);
unlink($shell_script_name);

echo "Finished downloading video and subtitles for \"$english_program_name : $japanese_program_name\"\n";

function get_program_list($source) {
    $url = $source['url'];
    $html_file = 'cache/' . $source['name'] . '.html';

    if (cached_file_does_not_exist($html_file)) {
        cache_file($html_file, get_page_source($url));
    } 

    $html_source = cache_file($html_file);
    $html = str_get_html($html_source);

    switch ($source['name']) {
        case 'animejpnsub.ezyro.com':
            return parse_animejpnsub_ezyro_com($html);
            break;

        case 'daiweeb.org':
            return parse_daiweeb_org($html);
            break;
        
        default:
            echo "Unable to process " . $source['name'] . "\n";
            die();
            break;
    }
}

function parse_animejpnsub_ezyro_com($html) {
    
    foreach ($html->find('tr') as $row) {
        $array['title_en'] = $row->find('td', 1)->find('div', 0)->plaintext;
        $array['title_ja'] = $row->find('td', 1)->find('div', 1)->plaintext;

        foreach ($row->find('td', 2)->find('a') as $video_link) {
            $array['video_links'][$video_link->innertext] = (string) $video_link->href;
        }

        $program_list[strtolower($array['title_en'])] = $array;
        $array = null;
    }

    return json_encode($program_list, JSON_PRETTY_PRINT);
}

function parse_daiweeb_org($html) {
    
    foreach ($html->find('li') as $row) {
        $array['title_en'] = $row->find('a', 0)->plaintext;
        $array['video_links'][] = $row->find('a', 0)->href;

        $program_list[strtolower($array['title_en'])] = $array;
        $array = null;
    }

    return json_encode($program_list, JSON_PRETTY_PRINT);
}

function get_media_urls($url, $video_href) {
    $media_url = $url . '/' . html_entity_decode($video_href);
    $html_file = 'cache/' . slug($video_href) . '.html';

    if (cached_file_does_not_exist($html_file)) {
        cache_file($html_file, get_page_source($media_url));
    } 

    $html_source = cache_file($html_file);
    $html = str_get_html($html_source);

    $re = '/title_id = "(.+?)".+?video_id = "(.+?)".+?video_link = "(.+?)"/m';
    $str = $html->find('script', 4)->innertext;
    preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);

    $subtitle_url = $url . '/vtt/' . $matches[0][1] . '/' . $matches[0][1] . '-' . $matches[0][2] . '.';
    $urls['subtitle_ja_url'] = $subtitle_url . 'ja.vtt';
    $urls['subtitle_en_url'] = $subtitle_url . 'en.vtt';
    $urls['video_url'] = $matches[0][3];
    $media_urls = $urls;

    return $media_urls;
}

function get_page_source($url) {
    $host = 'http://localhost:4444/wd/hub'; // Selenium server default
    $driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
    $driver->get($url);
    $page_source = $driver->getPageSource();
    $driver->quit();
    return $page_source;
}

function strip_html($string) {
    return strip_tags(html_entity_decode($string));
}