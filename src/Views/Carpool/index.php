<!-- Inclusion de formulaire de recherche des trajets -->
<?php $this->renderView("/partials/searchCarpoolForm") ?>

<section class="carpools-results py-5 my-5" id="carpools-results">
    <div class="container">

        <div class="row">
            <div class="col-md-3 col-sm-12 outfit">
                <h5 class="mb-4">Filtres</h5>
                <div class="mb-4">
                    <form action="#" method="GET" id="filtersForm">
                        <!-- Champs caches pour maintenir la recherche -->
                        <?php if (!empty($searchParams)): ?>
                            <input type="hidden" name="lieu_depart"
                                   value="<?= sanitize($searchParams['lieu_depart']) ?>">
                            <input type="hidden" name="lieu_arrivee"
                                   value="<?= sanitize($searchParams['lieu_arrivee']) ?>">
                            <input type="hidden" name="date_depart" value="<?= sanitize($searchParams['date_depart'])
                            ?>">
                            <input type="hidden" name="nb_passagers"
                                   value="<?= sanitize($searchParams['nb_passagers']) ?>">
                        <?php endif; ?>


                        <div class="form-check form-switch mb-3">
                            <label class="form-check-label" for="is_ecologic">Trajets Ecologic </label>
                            <input class="form-check-input" type="checkbox" role="switch" id="is_ecologic"
                                   name="is_ecologic"
                                <?= !empty($searchParams) ? ($searchParams['is_ecologic'] === 'on' ? 'checked' : '') : '' ?>>
                            <output for="is_ecologic" class="rangeOutput" aria-hidden="true"></output>
                        </div>

                        <div class="mb-3">
                            <label for="duree_max" class="form-label">Duree max</label>
                            <input type="range" class="form-range" name="duree_max" min="0" max="24" step="1"
                                   id="duree_max" value="<?= $_GET['duree_max'] ?? 0 ?>">
                            <output for="duree_max" class="rangeOutput" aria-hidden="true"></output>
                        </div>

                        <div class="mb-3">
                            <label for="prix_max" class="form-label">Prix max</label>
                            <input type="range" class="form-range" name="prix_max" min="0" max="300" step="5"
                                   id="prix_max" value="<?= $_GET['prix_max'] ?? 0 ?>">
                            <output for="prix_max" class="rangeOutput" aria-hidden="true"></output>
                        </div>

                        <div class="mb-3">
                            <label for="note_min" class="form-label">Note</label>
                            <input type="range" class="form-range" name="note_min" min="0" max="5" step="1"
                                   id="note_min" value="<?= $_GET['note_min'] ?? 0 ?>">
                            <output for="note_min" class="rangeOutput" aria-hidden="true"></output>
                        </div>
                        <a href="<?= $this->build_clear_filters_url() ?>" class="btn btn-sm btn-bg-main w-100 rounded-pill"> Effacer les
                            filtres</a>
                    </form>
                </div>

                <?php if (!empty($activeFilters)): ?>
                    <div class="mb-3">
                        <h5 class="text-muted me-2">Filtres actifs: </h5>
                        <ul class="list-group">

                            <?php foreach ($activeFilters as $filter): ?>

                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <?= $filter['label'] ?>
                                    </div>

                                    <a href="<?= $this->build_remove_filter_url($filter['name']) ?>" class="badge btn-bg-main rounded-pill p-1 ms-2">
                                        <i class="fas fa-times"></i>
                                    </a>

                                </li>
                            <?php endforeach; ?>
                        </ul>

                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-9 col-sm-12 outfit">
                <?php if (!empty($searchParams)): ?>
                    <?php if (empty($carpools)): ?>
                        <div class="empty-message">
                            <i class="fas fa-car-side fa-3x text-muted mb-3"></i>
                            <h3>Aucun covoiturage disponible</h3>
                            <!-- Affichage de la prochaine de disponible -->
                            <?php if ($nextAvailableDate): ?>
                                <div class=" alert bg-chinese my-3">
                                    <p>
                                        Prochaine date prevu pour ce trajet
                                        <strong><?= date('d/m/Y', strtotime($nextAvailableDate)) ?></strong>
                                        <input type="hidden" name="" id="next-date" value="<?= $nextAvailableDate ?>">
                                    </p>
                                    <div class="mb-3">
                                        <button class="btn btn-bg-green-2" id="next-carpool">Reserver sur ce trajet
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- Si le tableau des covoiturages n'est pas vide, je vais afficher les covoiturages disponibles -->
                        <h3 class="text-center fw-500 outfit mb-3"><?= count($carpools) ?> Covoiturages trouve(s)</h3>

                        <?php foreach ($carpools as $carpool): ?>
                            <div class="card card-carpools outfit mb-3">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8 col-sm-12">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="driver-avatar me-3">
                                                    <img src="<?= $carpool['conducteur']['photo'] ?? assets('img/avatar-default.png') ?>"
                                                         alt="" width="64px" class="rounded-circle">
                                                </div>
                                                <div class="driver-info">
                                                    <h5><?= $carpool['conducteur']['prenom'] . ' ' . $carpool['conducteur']['nom'] ?></h5>
                                                    <div class="text-warning">
                                                        <small>
                                                            <?= $carpool['conducteur']['note'] ? "{$carpool['conducteur']['note'] }/5" : 'Pas encore 
                                                            note'; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="carpool-info">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="fs-14">
                                                        <strong><?= date('d/m/Y', strtotime($carpool['date_depart'])) ?></strong>
                                                        <span class="text-muted"> <?= date('H:i', strtotime
                                                            ($carpool['heure_depart'])) ?></span>
                                                    </div>
                                                    <div class="fs-14 text-end">
                                                        <strong><?= date('d/m/Y', strtotime($carpool['date_arrivee']))
                                                            ?></strong>
                                                        <span class="text-muted"> <?= date('H:i', strtotime
                                                            ($carpool['heure_arrivee'])) ?></span>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate">
                                                        <i class="fas fa-map-marker-alt er-text-main"></i>
                                                        <?= sanitize($carpool['lieu_depart']) ?>
                                                    </div>
                                                    <div class="mx-3">
                                                        <i class="fas fa-arrow-right text-muted"></i>
                                                    </div>
                                                    <div class="text-truncate text-end">
                                                        <i class="fas fa-flag-checkered text-info"></i>
                                                        <?= sanitize($carpool['lieu_arrivee']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-12 text-end">
                                            <div class="mb-2">
                                                <?php if ($carpool['is_ecologic']): ?>
                                                    <span class="badge bg-main-color px-3 rounded-pill">
                                                        <i class="fas fa-leaf"></i> Ecologique
                                                    </span>
                                                <?php endif; ?>

                                                <span class="badge bg-info px-3 rounded-pill">
                                                    <?= $carpool['places_restantes'] ?> Places restantes
                                                </span>
                                            </div>
                                            <div class="price h4 er-text-main mb-2">
                                                <?= $carpool['prix_personne'] ?> Credits
                                            </div>

                                            <div class="vehicle-info text-muted small mb-2">
                                                <?= $carpool['vehicule']['marque'] ?>
                                                <?= $carpool['vehicule']['modele'] ?>
                                            </div>

                                            <a href="#" class="btn btn-small btn-bg-main rounded-pill px-4">Voir les
                                                details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <input type="hidden" name="" id="autocomplete-url" value="<?= url('carpool/autocomplete') ?>">
</section>

<script src="<?= assets('/js/autocomplete.js') ?>"></script>
