<section class="flash-messages">
    <div class="container">
        <?php if ($this->session->has_flash('info')): ?>
            <div class="alert alert-info">
                <?= $this->session->get_flash('info') ?>
            </div>
        <?php endif; ?>
        <?php if ($this->session->has_flash('error')): ?>
            <div class="alert alert-warning">
                <?= $this->session->get_flash('error') ?>
            </div>
        <?php endif; ?>

        <?php if ($this->session->has_flash('success')): ?>
            <div class="alert alert-success">
                <?= $this->session->get_flash('success') ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="py-md-5 bg-green-90 my-carpools">
    <div class="container outfit">
        <h1 class="mb-4 er-text-green">Mes covoiturages</h1>

        <ul class="nav nav-tabs" id="carpoolsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="driver-tab" data-bs-target="#driver" type="button"
                        data-bs-toggle="tab">
                    <i class="fas fa-road"></i> Ceux que je conduis
                    <span class="badge bg-chinese ms-1"><?= count($driver_carpools) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="passenger-tab" data-bs-target="#passenger" type="button"
                        data-bs-toggle="tab">
                    <i class="fas fa-suitcase-rolling"></i> Ceux auxquels je participe
                    <span class="badge bg-green ms-1"><?= count($passenger_carpools) ?></span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="carpoolsTabContent">
            <div id="driver" class="tab-pane fade show active" role="tabpanel">
                <?php if (empty($driver_carpools)): ?>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Vous n'avez pas créé de covoiturage en tant que conducteur.
                        <a href="#" class="alert-link">Créer votre premier trajet</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                            <tr>
                                <th class="bg-green-70">ID</th>
                                <th class="bg-green-70">Trajet</th>
                                <th class="bg-green-70">Date & Heure</th>
                                <th class="bg-green-70">Statut</th>
                                <th class="bg-green-70">Passagers</th>
                                <th class="bg-green-70">Places Restantes</th>
                                <th class="bg-green-70">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($driver_carpools as $carpool): ?>
                                <tr>
                                    <td><?= $carpool['id'] ?></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <small class="text-muted">Depart</small>
                                            <strong> <?= sanitize($carpool['lieu_depart']) ?> </strong>
                                            <small class="text-muted mt-1">Arrivee</small>
                                            <strong> <?= sanitize($carpool['lieu_arrivee']) ?> </strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <small class="text-muted">Date</small>
                                            <strong> <?= sanitize($carpool['date_formatee']) ?> </strong>
                                            <small class="text-muted mt-1">Heure</small>
                                            <strong> <?= sanitize($carpool['heure_formatee']) ?> </strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary rounded-pill"> <?= $carpool['statut'] ?> </span>
                                    </td>

                                    <td>
                                        <?= $carpool['nb_passagers'] ?> passagers
                                    </td>

                                    <td>
                                        <?= $carpool['places_restantes'] . '/' . $carpool['nb_places'] ?> Places
                                        restantes
                                    </td>

                                    <td>
                                        <div class="btn-group btn-group-sm gap-1" role="group">
                                            <a href="<?= url('/carpool/details', [
                                                'covoiturage' => $carpool['id'],
                                                'nb_passagers' => 1
                                            ]) ?>" class="btn btn-bg-main rounded-start-pill" title="Voir les details">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <?php if ($carpool['can_start']): ?>
                                                <a href="<?= url('/carpool/start', [
                                                    'covoiturage' => $carpool['id']
                                                ]) ?>" class="btn btn-bg-green-2" title="Demarrer le trajet">
                                                    <i class="fas fa-play"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($carpool['can_end']): ?>
                                                <a href="<?= url('/carpool/end', [
                                                    'covoiturage' => $carpool['id']
                                                ]) ?>" class="btn btn-danger" title="Terminer le trajet">
                                                    <i class="fas fa-stop"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($carpool['can_cancel']): ?>
                                                <a href="<?= url('/carpool/end', [
                                                    'covoiturage' => $carpool['id']
                                                ]) ?>" class="btn btn-outline-danger rounded-end-pill" title="Terminer le trajet">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div id="passenger" class="tab-pane fade show" role="tabpanel">
                <?php if (empty($passenger_carpools)): ?>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Vous n'avez pas participe a un covoiturage.
                        <a href="<?= url('/carpool') ?>" class="alert-link">Rechercher un trajet</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                            <tr>
                                <th class="bg-green-70">ID</th>
                                <th class="bg-green-70">Trajet</th>
                                <th class="bg-green-70">Date & Heure</th>
                                <th class="bg-green-70">Conducteur</th>
                                <th class="bg-green-70">Covoiturage</th>
                                <th class="bg-green-70">Reservations</th>
                                <th class="bg-green-70">Places</th>
                                <th class="bg-green-70">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($passenger_carpools as $carpool): ?>
                                <tr>
                                    <td><?= $carpool['id'] ?></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <small class="text-muted">Depart</small>
                                            <strong> <?= sanitize($carpool['lieu_depart']) ?> </strong>
                                            <small class="text-muted mt-1">Arrivee</small>
                                            <strong> <?= sanitize($carpool['lieu_arrivee']) ?> </strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <small class="text-muted">Date</small>
                                            <strong> <?= sanitize($carpool['date_formatee']) ?> </strong>
                                            <small class="text-muted mt-1">Heure</small>
                                            <strong> <?= sanitize($carpool['heure_formatee']) ?> </strong>
                                        </div>
                                    </td>

                                    <td>
                                        <?= $carpool['conducteur']['nom'] .' '. $carpool['conducteur']['prenom'] ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-primary rounded-pill"> <?= $carpool['statut_c'] ?> </span>
                                    </td>

                                    <td>
                                        <span class="badge er-bg-success rounded-pill"> <?= $carpool['statut_r'] ?> </span>
                                    </td>

                                    <td>
                                        <?= $carpool['nb_places_reservees'] ?> Place(s)
                                    </td>

                                    <td>
                                        <div class="btn-group btn-group-sm gap-1" role="group">
                                            <a href="<?= url('/carpool/details', [
                                                'covoiturage' => $carpool['id'],
                                                'nb_passagers' => 1
                                            ]) ?>" class="btn btn-bg-main rounded-start-pill" title="Voir les details">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <?php if ($carpool['can_validate']): ?>
                                                <a href="#" class="btn btn-success" title="Valider le trajet">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($carpool['can_review']): ?>
                                                <a href="#" class="btn btn-warning" title="Laisser un avis">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const carpoolsTab = document.getElementById('carpoolsTab');
        const activeTab = localStorage.getItem('activeCarpoolsTab');

        if (activeTab) {
            const tab = new bootstrap.Tab(document.querySelector(`[data-bs-target="${activeTab}"]`));
            tab.show();
        }

        carpoolsTab.addEventListener('shown.bs.tab', function(event) {
            localStorage.setItem('activeCarpoolsTab',event.target.getAttribute('data-bs-target'));
        });
    })
</script>
