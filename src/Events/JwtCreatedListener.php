<?php

namespace App\Events;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JwtCreatedListener
{

    public function __construct(
        private NormalizerInterface $normalizer,
        private JWTTokenManagerInterface $JWTManager
    ) {
    }

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $results = ['token' => $this->JWTManager->create($user), 'data' => $user];
        $data = $this->normalizer->normalize($results, 'json', ['groups' => ['getUsers']]);
        $event->setData($data);
    }
}
