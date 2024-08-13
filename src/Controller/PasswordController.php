<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use App\Service\PasswordService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[OA\Tag(name: 'password')]
class PasswordController extends AbstractController
{
    public function __construct(private PasswordService $service) {}

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
    #[Route(path: '/api/resetPassword', name: 'app_resetPassword_index', methods: ['POST'])]
    public function index(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $data = $request->toArray();
        $response = $this->service->sendResetPasswordRequest($data["email"]);

        return new \Symfony\Component\HttpFoundation\JsonResponse($response->getData(), $response->getStatus());
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
    #[Route(path: '/api/resetPassword/token', name: 'app_resetPassword_token', methods: ['POST'])]
    public function token(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $response = $this->service->verifyToken($request->get('token'));

        return new \Symfony\Component\HttpFoundation\JsonResponse($response->getData(), $response->getStatus());
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
    #[Route(path: '/api/resetPassword/update', name: 'app_resetPassword_update', methods: ['POST'])]
    public function update(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $oldPassword = $request->request->get('oldPassword');
        $newPassword = $request->request->get('newPassword');
        $confirmPassword = $request->request->get('confirmPassword');
        $response = $this->service->updatePassword($this->getUser(), $oldPassword, $newPassword, $confirmPassword);

        return new \Symfony\Component\HttpFoundation\JsonResponse($response->getData(), $response->getStatus());
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
    #[Route(path: '/api/resetPassword/{token}', name: 'app_resetPassword_reset', methods: ['POST'])]
    public function reset(Request $request, $token): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $response = $this->service->resetPassword($token, $request->get('newPassword'));

        return new \Symfony\Component\HttpFoundation\JsonResponse($response->getData(), $response->getStatus());
    }
}
