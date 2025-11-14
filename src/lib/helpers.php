<?php

function  redirect(string $path): void
{
    // Détection du protocole (http ou https)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

    // On retire la partie public/index.php ou index.php selon le cas
    $basePath = str_replace(['public/index.php', 'index.php'], '', $scriptName);
    $basePath = rtrim($basePath, '/');

    // Nettoyage du chemin cible
    $path = '/' . ltrim($path, '/');

    // Construction de l’URL finale complète
    $url = "$protocol://$host$basePath$path";

    // Redirection HTTP
    header("Location: $url");
    exit();
}

//  cette fonction permettra d'éviter des injections dans le code;
 function sanitize(mixed $value): string {
    return stripslashes(htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8'));
 }
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

