<section class="er-hb py-md-5 bg-green-80">
    <div class="container py-5 my-md-5">
        <div class="row er-hb-wrapper">
            <div class="col-md-6">
                <div class="hbt-container d-flex justify-content-center align-items-center h-100">
                    <h1 class="playwrite-au-tas text-center lh-lg">
                        <span class="er-text-main">Covoiturage</span> Partout & Pour Tous
                    </h1>
                </div>
            </div>
            <div class="col-md-6">
                <div class="hbi-container rounded-5 overflow-hidden">
                    <img src="<?= assets('img/home-preview.jpg') ?>" alt="Banniere d'accueil" class="img-fluid rounded-5">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Inclusion de formulaire de recherche des trajets -->
<?php $this->renderView("/partials/searchCarpoolForm") ?>

<section class="er-partners py-md-5 bg-green-80">
    <div class="container py-5 my-md-5">
        <div class="row er-partners-wrapper">
            <div class="col-md-6 col-sm-12">
                <div class="grid-container rounded-3 p-3">
                    <img src="<?= assets('img/partners/partner-1.jpg') ?>" alt="" class="img-fluid grid-item grid-item-1">
                    <img src="<?= assets('img/partners/partner-2.jpg') ?>" alt="" class="img-fluid grid-item grid-item-2">
                    <img src="<?= assets('img/partners/partner-3.jpg') ?>" alt="" class="img-fluid grid-item grid-item-3">
                    <img src="<?= assets('img/partners/partner-4.jpg') ?>" alt="" class="img-fluid grid-item grid-item-4">
                </div>
            </div>
            <div class="col-md-6 col-sm-12">
                <div class="er-ad d-flex justify-content-center align-items-center h-100">
                    <div class="er--ad-content">
                        <p class="h3 outfit fw-400 text-center mb-4"><?= APP_NAME ?>, c'est plus de
                            <span class="er-text-main fw-600">400 partenaires</span> a travers la France
                        </p>
                        <p class="h5 outfit fw-400 text-center">Covoiturez en toute circonstance, partout & pour tout. 😇</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="er-testimonials py-md-5">
    <div class="container py-5 my-md-5">
        <div class="testimonials-wrapper outfit">
            <div class="heading-section text-center">
                <h3 class="fw-600 sub-heading text-uppercase d-block">Il en parlent</h3>
                <h5 class="fw-600">Avis & Suggestions</h5>
            </div>

            <div class="carousel-testimonial owl-carousel">
                <?php if (!empty($notices)): ?>
                    <?php foreach ($notices as $notice): ?>
                        <div class="item">
                            <div class="testimonial-box d-block text-center">
                                <div class="user-img">
                                    <img src="<?= $notice->photo ?? assets('img/avatar-default.png') ?>" alt="" class="img-fluid">
                                </div>
                                <div class="">
                                    <span class="quote"><i class="fa fa-quote-left"></i></span>
                                    <p><?= $notice->commentaire ?></p>
                                    <p class="user-name"><?= sanitize($notice->nom . ' ' . $notice->prenom) ?></p>
                                    <apsn class="user-note badge rounded-pill bg-chinese mt-3">Note <?= $notice->note ?>/5</apsn>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <h1>Aucun avis pour le moment</h1>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>



<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"
    integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="<?= assets('/js/main.js') ?>"></script>
    <script src="<?= assets('/js/autocomplete.js') ?>"></script>