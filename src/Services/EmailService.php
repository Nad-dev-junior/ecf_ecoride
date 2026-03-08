<?php

namespace Ecoride\Ecoride\Services;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class EmailService
{

    private Mailer $mailer;
    private string $fromName;
    private string $fromEmail;

    public function __construct() {
        $dsn = $_ENV['MAILER_DSN'] ?? "smtp://localhost:1025";
        $this->mailer = new Mailer(Transport::fromDsn($dsn));
        $this->fromEmail = $_ENV['MAILER_FROM_EMAIL'] ?? 'no-reply@ecoride.test';
        $this->fromName = $_ENV['MAILER_FROM_NAME'] ?? "Ecoride";
    }

    public function send_carpool_ended_notification(
        string $toEmail,
        string $passengerName,
        string $departure,
        string $arrival,
        string $validationToken
    ): bool
    {
        $validationUrl = BASE_URL . "/carpool/validation?token=$validationToken";
        $subject = "Votre covoiturage a demarré.";
        $message = <<<html
            <h2>Bonjour $passengerName</h2>
            <p>Votre covoiturage vient de se terminé.</p>
            <div>
                <p><strong>Itineraire : </strong> $departure pour $arrival</p>
                <p><strong>Merci de valider que tout s'est bien passe</p>
            </div>
            
            <div>
                <a href="$validationUrl">Tout s'est bien passé</a>
                <br>
                <small>
                    <a href="$validationUrl?problem=1">Signaler un probleme</a>
                </small>
            </div>

            <p><strong>IMPORTANT !!!</strong> Votre validation permet au chauffeur de recevoir ses credits !</p>
            <hr>
            <small>Cet email a été envoyé automatiquement par EcoRide.</small>
        html;

        return $this->send_email($toEmail, $subject, $message);

    }

    public function send_carpool_started_notification(
        string $toEmail,
        string $passengerName,
        string $departure,
        string $arrival,
        string $date,
        string $time
    ): bool {
        $subject = "Votre covoiturage a demarré.";
        $message = <<<html
            <h2>Bonjour $passengerName</h2>
            <p>Votre covoiturage a été démarré par le conducteur.</p>
            <div>
                <p><strong>Itineraire : </strong> $departure pour $arrival</p>
                <p><strong>Date & Heure : </strong> $date à $time</p>
            </div>

            <p>Presentez-vous au point de rendez-vous dans 15 minutes au plus tard!</p>
            <hr>
            <small>Cet email a été envoyé automatiquement par EcoRide.</small>
        html;

        return $this->send_email($toEmail, $subject, $message);
    }


    private function send_email(string $to, string $subject, string $message): bool
    {
        // Pour le projet, on va logger
        error_log("Email envoyé a $to - Objet: $subject");

        // En production, utiliser symfony mailer
        try {
            $email = (new Email())
                ->from("{$this->fromName} <{$this->fromEmail}>")
                ->to($to)
                ->subject($subject)
                ->html($message);

            $this->mailer->send($email);

            return true;
        }catch (\Throwable $e) {
            error_log("[EmailService] {$e->getMessage()}");
            return false;
        }
    }

}