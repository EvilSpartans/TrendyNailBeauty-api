<?php

namespace App\Events;

use App\Classe\Mail;
use App\Entity\Contact;
use Symfony\Component\Mailer\MailerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContactSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private MailerInterface $mailer
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            'postPersist',
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        if (!$this->isCreateContactRoute()) {
            return;
        }

        $entity = $args->getObject();

        if (!$entity instanceof Contact) {
            return;
        }

        $contact = $entity;

        // Envoi d'email
        $mail = new Mail($this->mailer);
        $to = "jobissim@jobissim.com";
        $name = "Jobissim";
        $content = $contact->getContent();
        $subject = $contact->getSubject();
        $from = $contact->getEmail();
        $mail->send($to, $name, $subject, $content, $from);
    }

    private function isCreateContactRoute(): bool
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        return $currentRequest && $currentRequest->attributes->get('_route') === 'api_contact_create';
    }
}
