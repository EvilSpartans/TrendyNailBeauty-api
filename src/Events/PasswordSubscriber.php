<?php

namespace App\Events;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class PasswordSubscriber implements EventSubscriberInterface
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher, private $environment)
    {
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        if ($this->environment === 'test') {
            return;
        }

        $entity = $args->getObject();

        if (!$entity instanceof User) {
            return;
        }

        $this->hashPassword($entity);
        $this->addUserRole($entity);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        if ($this->environment === 'test') {
            return;
        }
        
        $entity = $args->getObject();

        if (!$entity instanceof User) {
            return;
        }

        $this->hashPassword($entity);
    }

    public function hashPassword(User $user)
    {
        $plainPassword = $user->getPassword();
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
    }

    public function addUserRole(User $user)
    {
        $user->setRoles(['ROLE_USER']);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'prePersist',
            'preUpdate',
        ];
    }
}