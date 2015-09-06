<?php

/**
 * @author tibo
 */

ini_set('include_path',
        ini_get('include_path') . PATH_SEPARATOR .
        __DIR__ . '/../src/');

spl_autoload_register(function($class) {
    $parts = explode('\\', $class);
   
    $parts[] = str_replace('_', DIRECTORY_SEPARATOR, array_pop($parts));

    $path = implode(DIRECTORY_SEPARATOR, $parts);
   
    $file = stream_resolve_include_path($path.'.php');
    if($file !== false) {
        require $file;
    }
});

require_once __DIR__ . "/../vendor/autoload.php";
