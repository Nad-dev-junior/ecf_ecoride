<section class="flash-messages">
    <div class="container">
        <?php if ($this->session->has_flash('error')): ?>
            <div class="alert alert-warning">
                <?= $this->session->get_flash('error') ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="my-md-5">
    <div class="container outfit">
        <div class="justify-content-center align-items-center mb-4">
            <?php if ($canParticipate && !empty($carpool)): ?>
                <form action="<?= url('carpool/apply') ?>" method="post" id="confirm-form">
                    <input type="hidden" name="nb_passagers" value="<?= $canUserParticipate['seats'] ?>">
                    <input type="hidden" name="covoiturage" value="<?= $carpool['id'] ?>">

                    <div class="checkbox-container mb-3 bg-green-90 p-3">
                        <input type="checkbox" class="form-check-input" id="confirm-box" name="confirm">
                        <label for="confirm-box" class="form-check-label">
                            <strong>Je confirme ma participation</strong><br>
                            <small class="text-muted">
                                Je comprends que <strong><?= $canUserParticipate['totalCoast'] ?> credits</strong>
                                seront debités de mon solde
                                <br>
                                Je serai remboursé uniquement si le conducteur annule le trajet
                            </small>
                        </label>

                        <div class="invalid-feedback">
                            Vous devez confirmer votre participation
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                        <a href="<?= $_SERVER['HTTP_REFERER'] ?? url('carpool') ?>"
                           class="btn btn-bg-chinese btn-sm rounded-pill px-3 outfit fs-16">
                            <i class="fas fa-arrow-left pe-2"></i> Retour aux recherches
                        </a>

                        <button type="submit" class="btn btn-bg-green-2 btn-sm rounded-pill px-3 outfit fs-16">
                            Participer pour <?= $canUserParticipate['totalCoast'] ?> credits
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>


        <!--        Afficher les info du trajet-->
        <?php if (!empty($carpool)): ?>
            <div class="row">
                <div class="col-md-8 col-sm-12">
                    <div class="card mb-4">
                        <div class="card-header bg-green er-text-light">
                            <h5 class="mb-0">
                                <i class="fas fa-route me-2"></i> Details du trajet
                                <?php if ($carpool['general']['ecologique']): ?>
                                    <span class="badge bg-chinese rounded-pill py-2 fs-10">
                                        <span class="fas fa-leaf"></span> Ecologique
                                    </span>
                                <?php endif; ?>
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div class="timeline-item departure mb-4">
                                    <div class="timeline-icon d-flex gap-2 align-items-center mb-2">
                                        <i class="fas fa-map-marker-alt fs-16"></i>
                                        <h5 class="er-text-chinese mb-0">Depart</h5>
                                    </div>

                                    <div class="timeline-content">
                                        <p class="mb-1">
                                            <strong><?= sanitize($carpool['depart']['lieu']) ?></strong>
                                        </p>
                                        <p class="text-muted mb-0">
                                            <i class="far fa-calendar me-2"></i> <?= $carpool['depart']['date_formatee'] ?>
                                            <i class="far fa-clock ms-2"></i> <?= $carpool['depart']['heure_formatee'] ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="timeline-connection">
                                    <div class="connection-line"></div>
                                    <div class="connection-info">
                                        <div class="badge bg-main-color rounded-pill fs-16">
                                            <i class="fas fa-clock me-2"></i> <?= $carpool['general']['duree']['affichage'] ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-item arrival mb-4">
                                    <div class="timeline-icon d-flex gap-2 align-items-center mb-2">
                                        <i class="fas fa-flag-checkered fs-16"></i>
                                        <h5 class="er-text-chinese mb-0">Arrivee</h5>
                                    </div>

                                    <div class="timeline-content">
                                        <p class="mb-1">
                                            <strong><?= sanitize($carpool['arrivee']['lieu']) ?></strong>
                                        </p>
                                        <p class="text-muted mb-0">
                                            <i class="far fa-calendar me-2"></i> <?= $carpool['arrivee']['date_formatee'] ?>
                                            <i class="far fa-clock ms-2"></i> <?= $carpool['arrivee']['heure_formatee'] ?>
                                        </p>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="card mb-4">
                        <div class="card-header bg-green er-text-light">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Informations Pratiques
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>
                                    <i class="fab fa-bitcoin me-2"></i>
                                    Prix/Personne
                                </span>
                                <span class="er-text-main">
                                    <?= $carpool['general']['tarif'] ?> Credits
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>
                                    <i class="fas fa-chair me-2"></i>
                                    Places Disponibles
                                </span>
                                <span class="er-text-main">

                                    <?= $carpool['general']['places_restantes'] ?>/<?= $carpool['general']['places_totals'] ?>
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>
                                    <i class="fas fa-clock me-2"></i>
                                    Duree du trajet
                                </span>
                                <span class="er-text-main">
                                    <?= $carpool['general']['duree']['affichage'] ?>
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>
                                    <i class="fas fa-spinner me-2"></i>
                                    Statut
                                </span>
                                <div>
                                    <?php if ($carpool['general']['statut'] === 'prevu'): ?>
                                        <span class="badge fs-14 bg-main-color rounded-pill px-3 py-1">Programme</span>
                                    <?php elseif ($carpool['general']['statut'] === 'en cours'): ?>
                                        <span class="badge fs-14 bg-info rounded-pill px-3 py-1">En cours</span>
                                    <?php elseif ($carpool['general']['statut'] === 'termine'): ?>
                                        <span class="badge fs-14 bg-danger rounded-pill px-3 py-1">Termine</span>
                                    <?php else: ?>
                                        <span class="badge fs-14 bg-chinese rounded-pill px-3 py-1">
                                            <?= $carpool['general']['statut'] ?>
                                        </span>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 col-sm-12">
                    <div class="card mb-4">
                        <div class="card-header bg-green er-text-light">
                            <h5>
                                <i class="fas fa-user-cog me-2"></i> Chauffeur
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="text-center mb-3">
                                <img src="<?= $carpool['conducteur']['photo'] ?? assets('img/avatar-default.png') ?>"
                                     alt="Photo de profil de <?= sanitize($carpool['conducteur']['nom'] ?? $carpool['conducteur']['pseudo']) ?>"
                                     class="rounded-circle img-fluid img-thumbnail mb-3"
                                >
                                <h4><?= sanitize($carpool['conducteur']['nom'] . ' ' . $carpool['conducteur']['prenom'] ??
                                        $carpool['conducteur']['pseudo']) ?></h4>

                                <div class="rating mb-2">
                                    <?= display_rate_stars($carpool['conducteur']['note']) ?>
                                    <span class="text-muted ms-2">
                                        <?= $carpool['conducteur']['note'] . "/5" ?> sur <?= $carpool['conducteur']['nb_avis'] . "avis." ?>
                                    </span>
                                </div>

                                <div class="text-muted">
                                    <i class="fas fa-calendar-alt me-1"></i> Membre depuis
                                    <strong>
                                        <?= $carpool['conducteur']['membre_depuis'] ?>
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">

                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>
                                <i class="fas fa-users me-2"></i> Passagers
                            </h5>
                        </div>
                    </div>

                </div>
            </div>

            <div class="row">

            </div>
        <?php endif; ?>
    </div>
</section>


<script src="<?= assets('js/confirm-booking.js') ?>"></script>
