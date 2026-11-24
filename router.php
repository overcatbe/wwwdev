<?php
// Copyright (C) 2026 overcatbe. GPL-3.0-or-later.
// Router for PHP's built-in server: stands in for the .htaccess front-controller rewrite.
// Existing files (assets, uploads, standalone scripts, directory indexes) are served as-is,
// everything else goes to the site's index.php.

$root = $_SERVER['DOCUMENT_ROOT'];
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$file = realpath($root . $path);

if ($path !== '/' && $file !== false && str_starts_with($file, $root)) {
    if (is_file($file) || (is_dir($file) && is_file($file . '/index.php'))) {
        return false;
    }
}

$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
$_SERVER['PHP_SELF']        = '/index.php';
chdir($root);
require $root . '/index.php';
