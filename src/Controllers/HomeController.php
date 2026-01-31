<?php
namespace Ecoride\Ecoride\Controllers;
use Ecoride\Ecoride\Core\Controller;

class HomeController extends Controller
{

    public function index(): void
    {
        $owlCarouselBaseCSS = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" integrity="sha512-tS3S5qG0BlhnQROyJXvNjeEM4UpMXHrQfTGmbQ1gKmelCxlSEBUaxhRBj/EFTzpbP4RVSrpEikbmdJobCvhE3g==" crossorigin="anonymous" referrerpolicy="no-referrer" />';
        $owlCarouselThemeCss = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" integrity="sha512-sMXtMNL1zRzolHYKEujM2AqCLUR9F2C4/05cdbxjjLSRvMQIciEPCQZo++nk7go3BtSuK9kfa/s+a4f4i5pLkw==" crossorigin="anonymous" referrerpolicy="no-referrer" />';
        $this->renderView('home/index', [
            'title' => "Accueil | " . APP_NAME,
            'css' => [
                $owlCarouselBaseCSS,
                $owlCarouselThemeCss
            ],
            'notices' => $this->userModel->get_notices()
        ]);

    }

}