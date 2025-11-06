<?php

function assets(string $path)
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? ''; 
    $basePath = str_replace(['public/index.php', 'index.php'], '', $scriptName);
    $basePath = rtrim($basePath, '/');
    return $basePath . '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = str_replace(['public/index.php', 'index.php'], '', $scriptName);
    $basePath = rtrim($basePath, '/');

    $path = trim($path, '/');
    return $basePath . ($path ? '/' . $path : '/');
}

