<?php
    // Prevent output
    ob_start();

    // Settings
    require_once '../.settings';
    ini_set('include_path', sprintf('%s:%s', ini_get('include_path'), CODE_PATH));
?>
