<?php

function slug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
}

function pad_int($int, $padding = 0) {
    return sprintf('%0' . $padding . 'd', $int);
}

function random_sleep() {
    // Stall to prevent hammering the server and make requests look more natural
    // sleep(rand(1, 4));
    sleep(1);
}

function get_data_from_csv($filename, $index = 0, $delimiter = ',') {

    if (!file_exists($filename)) die("$filename does not exist.");

    $file = fopen($filename, 'r');

    $csv = null;
    while (($line = fgetcsv($file, 0, $delimiter)) !== FALSE) {
        @$csv[$line[$index]] = $line;
    }

    fclose($file);

    return $csv;
}

function utf8_split($str, $len = 1)
{
  $arr = array();
  $strLen = mb_strlen($str, 'UTF-8');
  for ($i = 0; $i < $strLen; $i++)
  {
    $arr[] = mb_substr($str, $i, $len, 'UTF-8');
  }
  return $arr;
}

function utf8_ord($u) {
    $k = mb_convert_encoding($u, 'UCS-2LE', 'UTF-8');
    $k1 = ord(substr($k, 0, 1));
    $k2 = ord(substr($k, 1, 1));
    return $k2 * 256 + $k1; 
}

function double_quote($text) {
    return '"' . $text . '"';
}

function html_sanitize($text) {
    // Decode HTML entities, replace multiple UTF-8 spaces with a single space and trim the result 
    return trim(preg_replace('/\s+/', ' ', str_replace("\xC2\xA0", ' ', html_entity_decode($text))));
}

function csv_sanitize($text) {
    return csv_escape_double_quotes(html_sanitize($text));
}

function csv_escape_double_quotes($text) {
    return str_replace('"', '""', $text);
}
    
function write($filename, $content) {
    make_directory_structure($filename);
    return file_put_contents($filename, $content);
}

function read($filename) {
    return file_get_contents($filename);
}

function make_directory_structure($filename) {
    // Make directory structure
    @mkdir(dirname($filename), 0777, true);
}


function cached_url_does_not_exist($filename) {
    return !file_exists($filename);
}

function cached_file_does_not_exist($filename) {
    return !file_exists($filename);
}

function cache_file($filename, $contents = false) {

    make_directory_structure($filename);

    if (file_exists($filename)) {
        $contents = file_get_contents($filename);
    } else {
        file_put_contents($filename, $contents);
    }
    
    return $contents;
}

function cache_url($filename, $url = false) {

    make_directory_structure($filename);

    if (file_exists($filename)) {
        $contents = file_get_contents($filename);
    } else {
        $contents = get_url($url);
        file_put_contents($filename, $contents);
    }
    
    return $contents;
}

function get_url($url) {

    random_sleep();

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Accept: application/json, text/plain, */*",
        "Cache-Control: no-cache",
        "Connection: keep-alive",
        //"Host: www.kanshudo.com",
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:58.0) Gecko/20100101 Firefox/58.0"
      ),
    ));
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
        return "cURL Error #:" . $err;
    } else {
        return $response;
    }
}