<?php
    // Prevent output
    ob_start();

    // Settings
    require_once '../.settings.php';
    ini_set('include_path', sprintf('%s:%s', ini_get('include_path'), $conf['CODE_PATH']));
?>
