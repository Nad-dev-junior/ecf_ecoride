<section class="er-fyw bg-chinese">
    <div class="container py-5">
        <div class="er-fyw-wrapper">
            <div class="row">
                <div class="col-md-12">
                    <h3 class="fs-24 text-center mb-4 outfit">
                        Quelle est votre prohaine destination ?
                    </h3>
                </div>
                <div class="col-12">
                    <div class="er-fyw-form-container d-flex align-items-center px-2">
                        <form action="<?= url('carpool/search') ?>" method="get" class="row w-100 outfit er-fyw-form"
                              id="search-form">
                            <div class="col-md-10 col-sm-12 input-container bg-light rounded-start-pill rounded-end-0 py-1">
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 er-v-end-divider address-autocomplete">
                                                <input type="text" placeholder="Depart" name="lieu_depart"
                                                       value="<?= sanitize($_GET['lieu_depart'] ?? '') ?>"
                                                       autocomplete="off" id="lieu_depart"
                                                       class="form-control rounded-pill border-0 bg-transparent w-100 py-2 px-3">
                                                <div class="autocomplete-results" id="depart-results"></div>
                                            </div>
                                            <div class="col-md-6 col-sm-12 er-v-end-divider address-autocomplete">
                                                <input type="text" placeholder="Destination" name="lieu_arrivee"
                                                       value="<?= sanitize($_GET['lieu_arrivee'] ?? '') ?>"
                                                       autocomplete="off" id="lieu_arrivee"
                                                       class="form-control rounded-pill border-0 bg-transparent w-100 py-2 px-3">
                                                <div class="autocomplete-results" id="arrivee-results"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="row">
                                            <div class="col-md-7 col-sm-12 er-v-end-divider">
                                                <input type="date" placeholder="Date de depart" name="date_depart"
                                                       value="<?= sanitize($_GET['date_depart'] ?? '') ?>"
                                                       autocomplete="off" id="date-depart"
                                                       class="form-control rounded-pill border-0 bg-transparent w-100 py-2 px-3">
                                            </div>
                                            <div class="col-md-5 col-sm-12">
                                                <input type="number" placeholder="Nb.Passager" name="nb_passagers"
                                                       value="<?= sanitize($_GET['nb_passagers'] ?? '') ?>"
                                                       autocomplete="off"
                                                       class="form-control rounded-pill border-0 bg-transparent w-100 py-2 px-3">
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="submit" value="Rechercher"
                                   class="btn btn-bg-green-2 col-md-2 col-sm-12 rounded-start-0 rounded-end-pill">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
