<?php

return (function () {
    $root = dirname(__DIR__);

    $namespace = [];
    $autoload = [];

    $files = [];

    spl_autoload_register(function ($class) use ($namespace, $files, $root) {
        foreach ($namespace as $prefix => $path) {
            if (strpos(strtolower($class), strtolower($prefix)) !== 0) continue;

            $filePath = realpath($root . '/' . rtrim($path, '/') . '/' . ltrim(str_replace('\\', '/', substr($class, strlen($prefix))), '/') . '.php');

            if (file_exists($filePath)) {
                require_once $filePath;
            }
        }

        return false;
    });
})();
