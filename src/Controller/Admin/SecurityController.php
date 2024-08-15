<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin');
        }
        
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@EasyAdmin/page/login.html.twig', [
            // Parameters usually defined in Symfony login forms
            'error' => $error,
            'last_username' => $lastUsername,

            // Parameters to customize the login form
            'translation_domain' => 'admin',
            'favicon_path' => '/favicon-admin.svg',
            'page_title' => '<img src="images/logo.png" class="mb-5" width="100%">',
            'csrf_token_intention' => 'authenticate',

            'target_path' => $this->generateUrl('admin'),

            'username_label' => 'Pseudo',
            'password_label' => 'Mot de passe',
            'sign_in_label' => 'Connexion',


            'forgot_password_enabled' => false,
            'forgot_password_label' => 'Mot de passe oublié ?',

            'remember_me_enabled' => true,
            'remember_me_parameter' => '_remember_me',
            'remember_me_checked' => true,
            'remember_me_label' => 'Se souvenir de moi',
        ]);
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
    }
}
