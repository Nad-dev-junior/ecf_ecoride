<section class="flash-messages">
    <div class="container">
        <?php if ($this->session->has_flash('error')): ?>
            <div class="alert alert-danger">
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
<section class="flash-messages">
    <div class="container">
        <?php if ($this->session->has_flash('error')): ?>
            <div class="alert alert-danger">
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

<section class="er-pci py-5 er-text-light bg-chinese">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-3 col-sm-12">
                <div class="profile-image">
                    <img src="<?= $user->photo ?? assets('img/avatar-default.png') ?>"
                        alt="Image de profil de <?= sanitize($user->prenom ?? null) ?>" class="img-fluid">
                </div>
            </div>
            <div class="col-md-9 col-sm-12">
                <div class="row ms-5 mb-3 outfit">
                    <div class="col-md-8 col-sm-12">
                        <h3>A propos de <?= sanitize($user->pseudo ?? null) ?></h3>
                        <h6 class="er-text-green fst-italic">(
                            <?= $isDriver ? 'Chauffeur | ' : '' ?>
                            <?= $isPassenger ? 'Passager' : '' ?>
                            )
                        </h6>

                        <div class="user-info mb-3">
                            Pseudo: <span class="mb-3 text-white-50 fw-300"><?= sanitize($user->pseudo ?? null) ?>
                            </span>
                            <br>
                            Email: <span class="mb-3 text-white-50 fw-300"><?= sanitize($user->email ?? null) ?></span>
                            <br>
                            Inscrit le: <span class="mb-3 text-white-50 fw-300"><?= sanitize($user->date_creation ?? null) ?></span>
                            <br>
                        </div>

                        <div class="er-pse">
                            <?php if ($isDriver): ?>
                                <a href="<?= url('add-car') ?>" class="btn btn-bg-green-2 rounded-pill me-1 px-4 fw-400"
                                    title="Ajouter un vehicule">
                                    <i class="fas fa-car"></i>
                                </a>
                                <button type="button" class="btn btn-bg-green-2 rounded-pill me-1 px-4 fw-400"
                                    data-bs-target="#OptionsModal"
                                    data-bs-toggle="modal"
                                    title="Ajouter une preference">
                                    <i class="fas fa-sliders-h"></i>
                                </button>
                            <?php endif; ?>
                            <?php if (!$isDriver): ?>
                                <a href="<?= url('become-partner') ?>" class="btn btn-bg-green-2 rounded-pill me-1 px-4 fw-400">
                                    Devenir Partenaire
                                </a>
                            <?php endif; ?>
                            <a href="<?= url('carpool') ?>" class="btn btn-bg-green-2 rounded-pill me-1 px-4 fw-400">
                                Touver un covoiturage
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <ul class="list-group mb-3">
                            <li class="list-group-item px-0 d-flex bg-chinese er-text-light border-0
                            justify-content-between align-items-center">
                                <div class="ms-2 me-auto">
                                    <div>Nombre de vehicules</div>
                                </div>
                                <span class="badge fw-300 bg-green rounded-pill">03</span>
                            </li>
                            <li class="list-group-item px-0 d-flex bg-chinese er-text-light border-0
                            justify-content-between align-items-center">
                                <div class="ms-2 me-auto">
                                    <div>Covoiturages effectues</div>
                                </div>
                                <span class="badge fw-300 bg-green rounded-pill">93</span>
                            </li>
                            <li class="list-group-item px-0 d-flex bg-chinese er-text-light border-0
                            justify-content-between align-items-center">
                                <div class="ms-2 me-auto">
                                    <div>Moyenne generale</div>
                                </div>
                                <span class="badge fw-300 bg-green rounded-pill">3.9/5</span>
                            </li>
                        </ul>
                        <?php if ($isDriver): ?>
                            <a href="#" class="btn btn-bg-green-2 rounded-pill px-4 me-1 fw-400">
                                Proposer Un Trajet <i class="fas fa-road"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="er-modal">
    <div class="container">
        <div class="modal fade" id="OptionsModal" tabindex="-1" aria-labelledby="forOptions" aria-hidden="true"
            data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="forOptions">Ajouter Une Preference</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="<?= url('add-preference/handle') ?>" method="post" class="er-form">
                            <div class="row align-items-center">
                                <div class="mb-3 col-sm-9">
                                    <input type="text"
                                        class="form-control px-4 border-0 outfit
                                           <?= isset($errors['preference']) ? 'is-invalid' : '' ?>
                                           fw-300 rounded-pill fs-16"
                                        id="property" name="property"
                                        <?= sanitize($oldData['property'] ?? '') ?>
                                        placeholder="Entrez une preference">
                                    <?php if (isset($errors['preference'])): ?>
                                        <div class="invalid-feedback ps-4"><?= $errors['preference'] ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3 col-sm-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input er-check-input bg-green-80" type="checkbox"
                                            role="switch"
                                            id="value" name="value"
                                            <?= (isset($oldData['value']) && $oldData['value'] === 'oui') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="value"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-bg-green-2 outfit fw-500 rounded-pill fs-18 w-100">Ajouter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>