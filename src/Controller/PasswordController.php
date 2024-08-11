<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Entity\ResetPassword;
use OpenApi\Attributes as OA;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[OA\Tag(name: 'password')]
class ResetPasswordController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordEncoder,
        private EntityManagerInterface $manager,
        private UserRepository $userRepository,
        private MailerInterface $mailer
    ) {}

    /**
     * Send Reset Password Request
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[OA\RequestBody(
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property('email', type: 'string')
            ],
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Successful sending request',
        content: new OA\JsonContent(
            type: 'object',
            example: '{
                "message": "Un email contenant la procédure pour réinitialiser votre mot de passe vous a été envoyé.",
                "token": "jE2OTA4MjU5MzIsImV4cCI6MzMyMjY4MjU5MzIsInJvbGVzIjpbIlJPTEVfVVNF"
            }'
        )
    )]
    #[Route(path: '/resetPassword', name: 'app_resetPassword_index', methods: ['POST'])]
    public function index(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $data = $request->toArray();
        $email = $data["email"];
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse([
                "message" => "Aucun utilisateur n'est associé à cet email.",
            ], Response::HTTP_BAD_REQUEST);
        }

        $reset_password = new ResetPassword();
        $reset_password->setUser($user);
        $reset_password->setToken(uniqid());
        $this->manager->persist($reset_password);
        $this->manager->flush();

        $token = $reset_password->getToken();
        $content = "Bonjour " . $user->getUsername() . "<br/>Vous avez demandé à réinitialiser votre mot de passe.<br/><br/>";
        $content .= "Merci de copier et coller ce code dans l'application pour mettre à jour votre mot de passe : $token";

        // $mail = new Mail($this->mailer);
        // $mail->send($user->getEmail(), $user->getUsername(), 'Réinitialiser votre mot de passe sur TrendyNailBeauty', $content);
        // $token = $reset_password->getToken();

        return new JsonResponse([
            "message" => "Un email contenant la procédure pour réinitialiser votre mot de passe vous a été envoyé.",
            "token" => $token
        ], Response::HTTP_CREATED);
    }

    /**
     * Verify Password Token
     */
    #[OA\RequestBody(
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(properties: [
                    new OA\Property(property: 'token', type: 'text')
                ])
            ),
        ]
    )]
    #[
        OA\Response(
            response: 201,
            description: 'Successful verify token for password',
            content: new OA\JsonContent(
                type: 'object',
                example: '{
                    "message": "Vous pouvez maintenant choisir un nouveau mot de passe"
                }'
            )
        )
    ]
    #[Route(path: '/resetPassword/token', name: 'app_resetPassword_token', methods: ['POST'])]
    public function token(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $token = $request->get('token');
        $reset_password = $this->manager->getRepository(ResetPassword::class)->findOneBy(['token' => $token]);
        if (!$reset_password) {
            return new JsonResponse("Aucun utilisateur correspondant", Response::HTTP_BAD_REQUEST, [], true);
        }
        $now = new \DateTime();
        if ($now > $reset_password->getCreatedAt()->modify('+ 3 hour')) {
            return new JsonResponse(Response::HTTP_UNAUTHORIZED);
        }
        return new JsonResponse([
            "message" => "Vous pouvez maintenant choisir un nouveau mot de passe",
            "token" => $token
        ], Response::HTTP_OK, []);
    }


    /**
     * Reset Password with Token
     */
    #[OA\RequestBody(
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(properties: [
                    new OA\Property(property: 'newPassword', type: 'text',)
                ])
            ),
        ]
    )]
    #[
        OA\Response(
            response: 201,
            description: 'Successful reseted password',
            content: new OA\JsonContent(
                type: 'object',
                example: '{
                    "message": "Mot de passe modifié."
                }'
            )
        )
    ]
    #[Route(path: '/resetPassword/{token}', name: 'app_resetPassword_reset', methods: ['POST'])]
    public function reset(Request $request, $token): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $reset_password = $this->manager->getRepository(ResetPassword::class)->findOneBy(['token' => $token]);
        if (!$reset_password) {
            return new JsonResponse("Aucun utilisateur correspondant", Response::HTTP_BAD_REQUEST, [], true);
        }
        $user = $reset_password->getUser();
        $now = new \DateTime();
        if ($now > $reset_password->getCreatedAt()->modify('+ 3 hour')) {
            return new JsonResponse(Response::HTTP_UNAUTHORIZED);
        }
        $new_pwd = $request->get('newPassword');
        $password = $this->passwordEncoder->hashPassword($user, $new_pwd);
        $user->setPassword($password);
        $this->manager->persist($user);
        $this->manager->flush();
        return new JsonResponse(["message" => "Mot de passe modifié"], Response::HTTP_OK, []);
    }

    /**
     * Update Password
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[OA\RequestBody(
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(properties: [
                    new OA\Property(property: 'oldPassword', type: 'text',),
                    new OA\Property(property: 'newPassword', type: 'text',),
                    new OA\Property(property: 'confirmPassword', type: 'text',),
                ])
            ),
        ]
    )]
    #[
        OA\Response(
            response: 201,
            description: 'Successful updated password',
            content: new OA\JsonContent(
                type: 'object',
                example: '{
                    "success": true,
                    "message": "Votre mot de passe à été réinialisé."
                }'
            )
        )
    ]
    #[IsGranted('ROLE_USER')]
    #[Route(path: '/resetPassword/update', name: 'app_resetPassword_update', methods: ['POST'])]
    public function update(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $oldPassword    = $request->request->get('oldPassword');
        $newPassword    = $request->request->get('newPassword');
        $confirmPassword = $request->request->get('confirmPassword');
        if ($newPassword == null || $confirmPassword == null || $oldPassword == null || $newPassword != $confirmPassword) {
            return new JsonResponse(['success' => false, 'message' => 'Erreur dans la mise à jour de votre mot de passe']);
        }
        if ($this->passwordEncoder->isPasswordValid($user, $oldPassword)) {
            $user->setPassword($this->passwordEncoder->hashPassword($user, $newPassword));
            $this->manager->persist($user);
            $this->manager->flush();
            return new JsonResponse(['success' => true, 'message' => 'Votre mot de passe à été réinialisé']);
        } else {
            return new JsonResponse(['success' => false, 'message' => 'Erreur dans la mise à jour de votre mot de passe']);
        }
    }
}
