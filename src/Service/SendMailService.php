<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class SendMailService {

    //Par defaut Symfony 6 envoie le mail a la table phpmyadmin : messanger_messages
    //CONFIGURATION
    //config -> packages -> messenger.yaml
    //Commenter la ligne suivante : # Symfony\Component\Mailer\Messenger\SendEmailMessage: async

    private $mailerInterface;
    public function __construct(MailerInterface $mailerInterface)
    {
        $this->mailerInterface = $mailerInterface;
    }
    //Expediteur + destinataire + sujet + template twig + tableau options
    public function send(string $from, string $to, string $subject, string $template, array $context) : void {
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate("email/$template.html.twig")
            ->context($context);

            //envoi = fonction recursive qui s'appele elle meme
            $this->mailerInterface->send($email);
        
    }
}