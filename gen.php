<?php
$time_start = microtime(true); // From http://stackoverflow.com/a/9288945
require_once('inc/shared.lib.php');
require_once('inc/config.inc.php');
if (isset($api)) {
    $api->enable();
} else {
    $api = new api;
}

if ($GLOBALS['debug']) {
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(-1);
    log_file('debug mode enabled', 0);
} else {
    ini_set('display_errors', 0);
}

if (php_sapi_name() != "cli" && !$GLOBALS['dev'] && !$GLOBALS['debug'] && !@$api->check()) {
    echo 'Only for cronjob';
    exit;
}

set_time_limit(300);
date_default_timezone_set('UTC');

$rand_delay = mt_rand(0, 40);
if ($GLOBALS['dev'] | @$argv[1] == 1 | @$api->check()) { // $argv[1] is first arg of command-line, value 1 = skip delay and hash check
  $rand_delay = 0; // for dev
}

if ($GLOBALS['dev']) {
    log_file('Development mode enabled', 0);
}

if (@$api->check()) {
    log_file('MANUAL REQUESTED UPDATE', 0);
}

$utc_time_now = time();
log_file('Time now: '. date('r', $utc_time_now), 0);
log_file('Delay '.$rand_delay."s", 10);
log_file('Resume at '. date('r', $utc_time_now + $rand_delay), 10);
sleep($rand_delay);
log_file('Processing at '. date('r', time()), 10);

date_default_timezone_set('Asia/Tokyo');

$pre_hash = file_get_contents($GLOBALS['root'].'hash.txt');
$html_input = file_get_contents($HTM_INPUT);
$input_hash = md5($html_input);

if ($pre_hash == $input_hash && !$GLOBALS['debug'] && !$GLOBALS['dev'] && $argv[1] != 1) {
    log_file("Same hash, stop processing", 0);
    $api->msg_input("Nothing added.");

    log_file("Jobs done", 0);
    log_file("Finished at ".utc_time_now(), 0);

    $time_end = microtime(true);

    $execution_time = round($time_end - $time_start - $rand_delay, 4);

    //execution time of the script
    log_file('Total Execution Time: '.$execution_time.'s', -1);

    return;
    exit;
}

file_put_contents($GLOBALS['root'].'hash.txt', $input_hash);
chmod($GLOBALS['root'].'hash.txt', 0666);

require_once('inc/simple_html_dom.php'); // Using PHP Simple HTML DOM Parser from https://sourceforge.net/projects/simplehtmldom/ under MIT License

$dom = str_get_html($html_input);

$array = array();


$i = 0;
$i_img = 0;
$out_time = '';
$loop = '';
foreach ($dom->find('div.box-bottom li') as $node) { // Time / Data
    $out_time .= trim(strip_tags($dom->find('div.box-bottom li', $i)), " ");
    $i ++;
}

$time = str_replace('  ', '', explode('個別ページ', $out_time));
//log_file(var_export($time, true), 0);

for ($i = 0; $i <= 19; $i++) {
    $array[$i] = array();

    //	$link = $dom->find('div.box-ttl h3 a',$i)->href; //no longer required
    if (!$GLOBALS['dev']) {
        $array[$i]['title'] = trim(strip_tags($dom->find('h3', $i), ''));// windows sucks
    $array[$i]['author'] = trim(strip_tags($dom->find('article p.name', $i), '<a>'), ' '); // fix the "windows does not support UTF-8 filename" shit by md5 everything
    } elseif ($GLOBALS['dev']) {
        $array[$i]['title'] = substr(md5(htmlspecialchars(trim(strip_tags($dom->find('h3', $i), ''), ' '), ENT_XML1, 'UTF-8')), 0, 5);// windows sucks
    $array[$i]['author'] = substr(md5(htmlspecialchars(trim(strip_tags($dom->find('article p.name', $i), '<a>'), ' '), ENT_XML1, 'UTF-8')), 0, 5); //md5 the "windows does not support UTF-8 filename" shit
    }
    //	$array[$i]['link'] = htmlspecialchars( $link, ENT_XML1, 'UTF-8').'/'; //no longer required


    $dateTimeObject = \DateTime::createFromFormat('Y/m/d H:i', $time[$i]);
    $time[$i] = $dateTimeObject->format('U');
    $array[$i]['time'] = date('r', $time[$i]);
    if ($array[$i]['title'] == '' | $array[$i]['title'] == ' ') {
        $array[$i]['title'] = 'EMPTY_'.$array[$i]['time'];
    } // Just in case of empty blog title

    $header = "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">
<html lang=\"en\">
<head>
<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">
<title>{$array[$i]['title']}</title>
<style>
@font-face {
  font-family: 'Noto Sans Japanese';
  font-style: normal;
  font-weight: 200;
  src: url(//fonts.gstatic.com/ea/notosansjapanese/v6/NotoSansJP-Light.woff2) format('woff2'),
    url(//fonts.gstatic.com/ea/notosansjapanese/v6/NotoSansJP-Light.woff) format('woff'),
      url(//fonts.gstatic.com/ea/notosansjapanese/v6/NotoSansJP-Light.otf) format('opentype');
      }

      body {background-color: powderblue;}
      * {font-family: \"Noto Sans Japanese\", sans-serif; font-weight: 200; }
      </style>
      </head>
      <body>
      <h1>{$array[$i]['title']}</h1><br>
      <h4>{$array[$i]['author']}</h4><br>
      <h4>{$array[$i]['time']}</h4>
      <p>
";	//For HTML

    $content = strip_tags($dom->find('div.box-article', $i), '<img><br><div><b>');
    $replace_search = ['<br />','</div>','<div class="box-article">','<div id="AppleMailSignature">','<div class="gmail_msg">','<br class="gmail_msg">','src="','<div>', '<br> <br>'];
    $replace_str = ['<br>','<br>','<br>','<br>','<br>','<br>','style="max-width: 100%; height: auto;" src="','','<br><br>'];
    $content = str_replace($replace_search, $replace_str, $content);

    // boring formatting stuff, prevent abusive new lines, remove problematic <div> tags, resize photos

    $footer = "
        </p>
        </body>
        </html>"; // footer, as you can see

    $array[$i]['content'] = $header.$content.$footer; // assemble a full html page
        $array[$i]['content_only'] = $content; // for text only version

        $dom_article = $dom->find('div.box-article', $i); // NEVER var_dump this, it is full DOM, browser will exploded
        foreach ($dom_article->find('img') as $node_article) { // full external URL of image, DO NOT var_dump $node_article also
          $array[$i]['img'][$i_img] = $node_article->src;
            $i_img ++;
        }
}


      $date_now = date(DATE_RSS);
      umask(0);

      for ($i = 0; $i <= 19; $i++) {
          $time = date_create($array[$i]['time']);
          $year = date_format($time, 'Y');
          $month = date_format($time, 'M');
          $date = date_format($time, 'd');
          $location_title = str_replace('\\', '', $array[$i]['title']); // remove illegal filename
        $location_title = str_replace('/', '', $location_title); // remove illegal filename
        $location = $root_out.$year.'/'.$month.'/'.$date.'/'.$array[$i]['author'].'/'.$location_title.'/';
          if ($GLOBALS['dev']) {
              $location = str_replace('/', '\\', $location);
          }

          //echo $location;

          if (!file_exists($location)) {
              log_file($location.' created', 0);
              //		log_file($array[$i]['content_only'], 10); // DEBUG ONLY
              $api->msg_input('ADDED: '.$location);
              mkdir($location, 0777, true);

              file_put_contents($location.'full.html', $array[$i]['content']);

              $txt = $array[$i]['title']."\n";
              $txt .= $array[$i]['author']."\n";
              $txt .= $array[$i]['time']."\n";
              $txt .= '================================================================='."\n";
              $txt .= html_entity_decode(strip_tags($array[$i]['content_only'], '<br>'), ENT_QUOTES|ENT_HTML5, "UTF-8");
              $txt_replace_search = ['<br>', '<br/>', '&nbsp', 'body {background-color: powderblue;}'];
              $txt_replace_str = ["\n","\n",' ',''];
              $txt = ltrim(str_replace($txt_replace_search, $txt_replace_str, $txt));


              file_put_contents($location.'text.txt', "\xEF\xBB\xBF" . $txt);


              $img_order = 0;

              if (isset($array[$i]['img'])) {
                  download_to($array[$i]['img'], $location);
                  foreach ($array[$i]['img'] as $img) {
                      $filename = basename($img);
                      rename($location.$filename, $location.$img_order.'_'.basename($img));
                      $img_order ++;
                  }
              }
          } elseif (file_exists($location)) {
              log_file($location.' existed', 10);
          }
      }


      log_file("Jobs done", 0);
      date_default_timezone_set('UTC');
      $date_time = new DateTime();
      $date_time = $date_time->setTimezone(new DateTimeZone('UTC'));
      $time_now = $date_time->format('r');
      log_file("Finished at ".$time_now, 0);

      $time_end = microtime(true);

      $execution_time = round($time_end - $time_start - $rand_delay, 4);

      //execution time of the script
      log_file('Total Execution Time: '.$execution_time.'s', -1);
      if ($GLOBALS['debug']) {
          unset($dom);
          unset($dateTimeObject);
          //echo '<pre>' . print_r($array[0]) . '</pre>';
      }
