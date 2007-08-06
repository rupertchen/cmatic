<?php
header('Content-Type: text/html; charset=utf-8');

$cssInternal = sprintf('%s/%s', $conf['WWW_PATH'], 'css/internal.css');
$jsGlobal = sprintf('%s/%s', $conf['WWW_PATH'], 'js/global.js');
$jsFormatting = sprintf('%s/%s', $conf['WWW_PATH'], 'js/formatting.js');
$jsField = sprintf('%s/%s', $conf['WWW_PATH'], 'js/fields.js');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <title><?php echo $GLOBALS[HTML_TITLE]; ?></title>
  <link rel="stylesheet" type="text/css" href="<?php echo $cssInternal; ?>"/>
  <script type="text/javascript" src="<?php echo $jsGlobal; ?>"></script>
  <script type="text/javascript" src="<?php echo $jsFormatting; ?>"></script>
  <script type="text/javascript" src="<?php echo $jsField; ?>"></script>
</head>
<body class="<?php echo $GLOBALS[BODY_CLASS]; ?>">
