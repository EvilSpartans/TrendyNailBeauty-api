<?php

namespace App\Events;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class APIDocsListener
{
    public function __construct(
        private UrlGeneratorInterface $router,
    ) {
    }

    public function __invoke(RequestEvent $event)
    {
        $request = $event->getRequest();
        $path = rtrim($request->getPathInfo(), '/');

        if ($path === '/api') {
            $apiDocsPath = $this->router->generate('app.swagger_ui');
            $event->setResponse(new RedirectResponse($apiDocsPath));
        }
    }
}