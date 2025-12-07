<section class="flash-messages">
    <div class="container">
        <?php if ($this->session->has_flash('error')): ?>
            <div class="alert alert-danger">
                <?= $this->session->get_flash('error') ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<div class="register-form py-5 my-5">
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-sm-12 mx-auto">
                <div class="form-container outfit">
                    <h3 class="text-center fw-600 fs-main-title er-text-dark mb-5">
                        Ajouter Un Vehicule
                    </h3>
                    <form action="<?= url('/add-car/handle') ?>" method="post" class="er-form">
                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-3">
                                <label for="license_plate" class="form-label fs-16 ps-4 fw-300 er-text-dark">
                                    Immatriculation:*</label>
                                <input type="text" id="license_plate" name="license_plate"
                                       class="form-control px-4 fw-300 fs-16 rounded-pill
                                          <?= isset($errors['immatriculation']) ? ' is-invalid' : '' ?> "
                                       value="<?= sanitize($oldData['immatriculation'] ?? '') ?>">

                                <?php if (isset($errors['immatriculation'])): ?>
                                    <div class="invalid-feedback ps-4"><?= $errors['immatriculation'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-3">
                                <label for="license_plate_date" class="form-label fs-16 ps-4 fw-300 er-text-dark">
                                    Premiere Immatriculation:*</label>
                                <input type="date" id="license_plate_date" name="license_plate_date"
                                       class="form-control px-4 fw-300 fs-16 rounded-pill
                                          <?= isset($errors['date_premiere_immatriculation']) ? ' is-invalid' : '' ?> "
                                       value="<?= sanitize($oldData['date_premiere_immatriculation'] ?? '') ?>">

                                <?php if (isset($errors['date_premiere_immatriculation'])): ?>
                                    <div class="invalid-feedback ps-4"><?= $errors['date_premiere_immatriculation'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 col-sm-12 mb-3">
                                <label for="brand" class="form-label fs-16 ps-4 fw-300 er-text-dark">
                                    Marque:*</label>
                                <input type="text" id="brand" name="brand"
                                       class="form-control px-4 fw-300 fs-16 rounded-pill
                                          <?= isset($errors['marque']) ? ' is-invalid' : '' ?> "
                                       value="<?= sanitize($oldData['marque'] ?? '') ?>">

                                <?php if (isset($errors['marque'])): ?>
                                    <div class="invalid-feedback ps-4"><?= $errors['marque'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 col-sm-12 mb-3">
                                <label for="model" class="form-label fs-16 ps-4 fw-300 er-text-dark">
                                    Modele:*</label>
                                <input type="text" id="model" name="model"
                                       class="form-control px-4 fw-300 fs-16 rounded-pill
                                          <?= isset($errors['modele']) ? ' is-invalid' : '' ?> "
                                       value="<?= sanitize($oldData['modele'] ?? '') ?>">

                                <?php if (isset($errors['modele'])): ?>
                                    <div class="invalid-feedback ps-4"><?= $errors['modele'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 col-sm-12 mb-3">
                                <label for="color" class="form-label fs-16 ps-4 fw-300 er-text-dark">
                                    Couleur:*</label>
                                <input type="text" id="color" name="color"
                                       class="form-control px-4 fw-300 fs-16 rounded-pill
                                          <?= isset($errors['couleur']) ? ' is-invalid' : '' ?> "
                                       value="<?= sanitize($oldData['couleur'] ?? '') ?>">

                                <?php if (isset($errors['couleur'])): ?>
                                    <div class="invalid-feedback ps-4"><?= $errors['couleur'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="row align-items-center">
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <input type="number" name="seats" id="seats"
                                           class="form-control px-4 fw-300 fs-16 rounded-pill
                                          <?= isset($errors['nb_places']) ? ' is-invalid' : '' ?> "
                                           placeholder="Nb. Places"
                                           value="<?= sanitize($oldData['nb_places'] ?? '') ?>">

                                    <?php if (isset($errors['nb_places'])): ?>
                                        <div class="invalid-feedback ps-4"><?= $errors['nb_places'] ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-9 col-sm-6 mb-3">
                                    <div class="row align-items-center justify-content-center">
                                        <div class="col-md-4 col-sm-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                       id="energie" name="energie">
                                                <label class="form-check-label" for="energie">Electrique*
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                       id="animal" name="animal">
                                                <label class="form-check-label" for="animal">Animaux:
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                       id="smoker" name="smoker">
                                                <label class="form-check-label" for="smoker">Fumeur:
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 mt-5 row">
                            <div class="col-md-6 col-sm-8 mx-auto">
                                <input type="submit" value="Ajouter Vehicule"
                                       class="btn btn-bg-chinese fs-18 rounded-pill
                                fw-500 w-100">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>