<?php

namespace App\Controller;

use App\Entity\User;
use OpenApi\Attributes as OA;
use App\Repository\UserRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

#[OA\Tag(name: 'Auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private UserRepository $repo
    ) {
    }

    #[Security]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property('username', type: 'string'),
                new OA\Property('password', type: 'string'),
            ],
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'object',
            properties: [new OA\Property('token', type: 'string')],
        )
    )]
    #[Route('/api/login', methods: ['POST'])]
    public function login(){}

    /**
     * Register a User
     *
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[OA\RequestBody(
        content: new OA\JsonContent(
            type: 'object',
            ref: new Model(type: User::class),
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Successful created',
        content: new OA\JsonContent(
            type: 'object',
            example: '{
                "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2OTA4MjU5MzIsImV4cCI6MzMyMjY4MjU5MzIsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJ1c2VybmFtZSI6ImFsZXhpc21vcnNlZGRkenNzZG5zc2ZyY3Nzc2Rkc3NyZHNzc2Zxc2RkcXI2MkBob3RtYWlsLmNvbSJ9.d6sONiGNjQuxn7pJgPJj0P8KlUKtYJruVJagihzYwPRHtr-apwRnxwFK3xrlra6yxHYHGl8Nhzmcb6ZBbkaKEUPx9v73gM7mvktx2ksYotMcnmbo6FuY5tQ9iNYjI5sD6j6gMQf6bKCfNRpzOPsCA2-5gWvjw9abbW4AAMhWCJ-aEQSWGL1kXzhwDKg4ZEDBgCZVcNCFkajGzDTP4Kc5NiNUVUYffkpc4QGcrcY2ZvX_ruwWai87umS6lKyoH2w7PR69YMkisIs7YUh929e8TUFHrH8s9Tg8N0461la_D81ohDX5CeYrOLyS7GgjmwY6M3s4qwbLwTj_wkLnEB_B5w",
                "user": {
                    "id": 5754,
                    "email": "test@hotmail.com",
                    "username": " moral"
                }
            }'
        )
    )]
    #[Route('/api/register', methods: ['POST'])]
    public function create(Request $request, JWTTokenManagerInterface $JWTManager): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->repo->save($user, true);

        $results = ['token' => $JWTManager->create($user), 'data' => $user];
        $data = $this->serializer->serialize($results, 'json', ['groups' => ['getUsers']]);

        return new JsonResponse($data, \Symfony\Component\HttpFoundation\Response::HTTP_CREATED, [], true);
    }

    /**
     * Verify Token
     */
    #[OA\RequestBody(
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property('token', type: 'string')
            ],
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful created',
        content: new OA\JsonContent(
            type: 'object',
            example: '{ "message" => "Token et utilisateur valides" }'
        )
    )]
    #[Route('/api/verifyToken', methods: ['POST'])]
    public function token(Request $request, JWTEncoderInterface $jWTEncoderInterface): \Symfony\Component\HttpFoundation\JsonResponse
    {
        try {
            $data = $request->toArray();
            $token = $data["token"];
            $decodedToken = $jWTEncoderInterface->decode($token);

            if (!isset($decodedToken['username'])) {
                return $this->json(['message' => 'Token invalide.'], 400);
            }

            $user = $this->repo->findOneBy(["username" => $decodedToken['username']]);

            if (!$user) {
                return $this->json(['message' => 'Utilisateur introuvable.'], 400);
            } else {
                return $this->json(['message' => 'Token et utilisateur valides.'], 200);
            }
        } catch (\Throwable $th) {
            return $this->json(['message' => 'Erreur lors de la vérification du token.'], 400);
        }
    }

    /**
     * Show Profile
     */
    #[
        OA\Response(
            response: 200,
            description: 'Successful response',
            content: new Model(type: User::class)
        )
    ]
    #[IsGranted('ROLE_USER')]
    #[Route('/api/profile', methods: ['GET'])]
    public function profile(): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $data = $this->serializer->serialize($this->getUser(), 'json', ['groups' => ['getUsers']]);
        return new JsonResponse($data, \Symfony\Component\HttpFoundation\Response::HTTP_OK, [], true);
    }
}
