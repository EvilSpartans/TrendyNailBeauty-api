<?php

namespace App\Classe;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class Mail
{

    public function __construct(private MailerInterface $mailer)
    {
    }

    public function send($to, $name, $subject, $content, $from = null)
    {
        try {
            $email = (new TemplatedEmail())
                ->from('alicia@alicia.com')
                ->to($to)
                ->subject($subject)
                ->htmlTemplate('mails/index.html.twig', [
                    'subject' => $subject,
                    'name' => $name,
                    'content' => $content
                ])
                ->context([
                    'subject' => $subject,
                    'name' => $name,
                    'content' => $content,
                    'from' => $from
                ]);

            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }
    }

    public function blackList()
    {
        $list =
            [
                "ericjonesmyemail@gmail.com", "hacker@facme.es", "info@datalist2023.com", "dedra.mackey@yahoo.com"
            ];

        return $list;
    }
}
