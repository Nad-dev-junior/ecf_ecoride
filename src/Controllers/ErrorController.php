<?php 

function notfound(): void{
    header("HTTP/1.0 404 Not Found");
    include __DIR__ . "/../Views/errors/404.php";
    exit();
}


function showError(string $message = '' , string $title = 'Erreur'): void {
   $error_title = $title;
   $error_message = $message ;
   include __DIR__ . '/../Views/errors/error.php';
   exit();
}