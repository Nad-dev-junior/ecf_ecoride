<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Ecoride'?></title>
    <?php if (!empty($css)): ?>
        <?php foreach ($css as $link): ?>
            <?= $link ?>
        <?php endforeach; ?>
    <?php endif; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
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
                    
                    <?php
                        if ($isLoggedIn && isset($currentUser)): ?>
                            <li class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle" id="profileNavbar"
                                    data-bs-toggle="dropdown" role="button">
                                    <img src="<?= $user->photo ?? assets('img/avatar-default.png') ?>" width="36" alt="">
                                    <?= sanitize($user->pseudo ?? null) ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end px-1">
                                    <li>
                                        <a href="<?= url('profile', ['pseudo' => $user->pseudo, 'nom' => $user->nom ?? ''])
                                                    ?>"
                                            class="dropdown-item">Mon Profil</a>
                                    </li>
                                    <?php if ($isDriver) : ?>
                                        <li>
                                            <a href="#" class="dropdown-item">Proposer Un trajet</a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($currentUser['adminInfo']['name'] === "Administrateur") : ?>
                                        <li>
                                            <a href="#" class="dropdown-item">Ajouter Un nouvel Employe</a>
                                        </li>
                                        <li>
                                            <a href="#" class="dropdown-item">Tableau de Bord</a>
                                        </li>
                                    <?php endif; ?>
                                    <li>
                                        <a href="<?= url('/logout') ?>" class="dropdown-item text-danger">Deconnexion</a>
                                    </li>
                                </ul>
                            </li>
                            <?php else: ?>
                    <li class="nav-item">
                        <a href="<?= url('login') ?>" class="nav-link px-3 ms-3 er-text-dark ">Connexion</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= url('register') ?>"
                           class="nav-link er-subscribe  px-3 ms-3 er-text-dark ">Inscription</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
