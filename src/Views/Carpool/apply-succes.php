<section class="flash-messages">
    <div class="container">
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

<section class="py-md-5 bg-green-90">
    <div class="container outfit">

        <h1>Reservation de <?= $reservation['passager']['nom'] ?></h1>
    </div>
</section>

