<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home_index', methods: ['GET'])]
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->redirectToRoute('app_doc_index');
    }

    #[Route('/api/doc', name: 'app_doc_index', methods: ['GET'])]
    public function doc(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('bundles/NelmioApiDocBundle/SwaggerUi/index.html.twig', []);
    }

}
