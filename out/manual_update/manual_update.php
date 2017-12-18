<?php
error_reporting(1);
require_once('../../inc/shared.lib.php');
function isValid()
{
    try {
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = ['secret'   => $recaptcha_secret,
                 'response' => @$_POST['g-recaptcha-response'],
                 'remoteip' => $_SERVER['REMOTE_ADDR']];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return json_decode($result)->success;
    } catch (Exception $e) {
        return null;
    }
}

if ($_POST['submit'] == 'submit' || @$_GET['pass'] == $backdoor_pass) {
    if (isValid() || @$_GET['pass'] == $backdoor_pass) {
        $api = new api;
        $api->enable();
        require($GLOBALS['root'].'/gen.php');
    } else {
        $error = 'Something went wrong. :(<br>Are you robot?<br>Go back and check the box, maybe?';
    }
} else {
    $error = 'Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta charset="utf-8">
  <title>Manual update</title>

  <!-- Mobile Specific Metas
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- FONT
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">

  <!-- CSS
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/skeleton.css">

  <!-- Favicon
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link rel="icon" type="image/png" href="images/favicon.png">
  <!-- Piwik -->
<script type="text/javascript">
  var _paq = _paq || [];
  _paq.push(['setDocumentTitle', document.title+' [<?php echo $GLOBALS['uid']; ?>]']);
  /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="//k46.akb48.work/piwik/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', '1']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Piwik Code -->
</head>
<body>

  <!-- Primary Page Layout
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <div class="container">
    <div class="row">
      <div class="column" style="margin-top: 15%">
        <h4>Manual update</h4>
          <pre><?php
if (!isset($error)) {
    print_r($api->msg_output());
} else {
    echo $error;
}
          ?></pre>
        <?php
        if (isset($api)) {
            ?><pre>Ref id: [<?php echo $GLOBALS['uid']; ?>]</pre>
        <?php
        } ?>
      </div>
    </div>
  </div>

<!-- End Document
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
<!--
Proudly provided by Skeleton: http://getskeleton.com/
Save me lots of headache, highly recommended.
-->
</body>
</html>
