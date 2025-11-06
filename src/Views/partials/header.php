<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Ecoride'?></title>

    <link rel="stylesheet" href="<?= assets('/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= assets('/css/style.css') ?>">
</head>
<body>
<header class="er-header" id="er-header">
    <nav class="navbar navbar-expand-lg" data-bs-theme="light">
        <div class="container">
            <a href="#" class="navbar-brand">
                <img src="<?= assets('/img/logo_ecoride.png') ?>" height="80" alt="Logo de EcoRide">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav outfit fs-18 fw-500 me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a href="<?= url('/') ?>" class="nav-link px-3 ms-3 er-text-dark active" aria-current="page">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link px-3 ms-3 er-text-dark ">Covoiturages</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link  px-3 ms-3 er-text-dark">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= url('login') ?>" class="nav-link px-3 ms-3 er-text-dark ">Connexion</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= url('register') ?>"
                           class="nav-link er-subscribe bg-chinese px-3 ms-3 er-text-dark ">Inscription</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header><?php
