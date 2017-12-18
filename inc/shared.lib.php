<?php
require_once('config.inc.php');
$GLOBALS['uid'] = md5(uniqid('', true));

class api
{
    private $api_state;

    public function enable()
    {
        $this->api_state = true;
    }

    public function check()
    {
        if ($this->api_state) {
            return true;
        }

        return false;
    }

    public function msg_input($msg)
    {
        $GLOBALS['api_msg'] .= $msg."\n";
    }

    public function msg_output()
    {
        if (!isset($GLOBALS['api_msg'])) {
            return 'Nothing added';
        }
        return $GLOBALS['api_msg'];
    }
}

function utc_time_now()
{
    $date = new DateTime('NOW');
    $date->setTimezone(new DateTimeZone('UTC'));
    $time_now = $date->format(DateTime::RSS);

    return (string)$time_now;
}

function log_file($msg, $flag)
{ // $flag: -1=flush to log 0=notice 1=warning 2=error

    $api = new api;

    if (!isset($GLOBALS['msg'])) {
        $time_now = utc_time_now();
        $GLOBALS['msg'] = "[{$GLOBALS['uid']}] {$time_now}\n";
    }

    $date = new DateTime('NOW');
    $date->setTimezone(new DateTimeZone('UTC'));
    $time_now = $date->format('H:i:s').substr((string)microtime(), 1, 4);

    switch ($flag) {
    case -1:
      $level = '[FLUSH]';
      break;
    case 0:
      $level = '[INFO]';
      break;
    case 1:
      $level = '[WARNING]';
      break;
    case 2:
      $level = '[ERROR]';
      break;
    case 10:
      $level = '[DEBUG]';
      break;
    default:
      $level = '[UNDEFINED]';
      break;
  }

    if ($flag!=10 | $GLOBALS['debug']) {
        $GLOBALS['msg'] .= "[{$time_now}]{$level}{$msg}\n";
    }

    if ($flag==-1) { // flush to log file and not api
        file_put_contents($GLOBALS['root'].'gen.php.log', $GLOBALS['msg']."\n", FILE_APPEND);
        chmod($GLOBALS['root'].'gen.php.log', 0777);
        if ($GLOBALS['debug']||$GLOBALS['dev']) {
            if (php_sapi_name() == "cli") {
                echo $GLOBALS['msg'];
            } else {
                echo '<pre>'.$GLOBALS['msg'].'</pre>';
            }
        }
    }
}

function download_to($url, $location)
{
    require_once($GLOBALS['root'].'vendor/autoload.php');
    $curl = new Zebra_cURL();
    if ($GLOBALS['dev']) { //Fix Windows file location
        $location = str_replace('/', '\\', $location);
        $location .= '\\';
    }
    return $curl->download($url, $location);
}
