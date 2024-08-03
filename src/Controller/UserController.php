<?php

namespace App\Controller;

use App\Entity\User;
use OpenApi\Attributes as OA;
use App\Repository\UserRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[OA\Tag(name: 'User')]
class UserController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private UserRepository $repo
    ) {
    }

    /**
     * Fetch All
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class))
        )
    )]
    #[Route('/api/users', name: 'app_user_index', methods: ['GET'])]
    public function index(): \Symfony\Component\HttpFoundation\JsonResponse
    {
        return $this->json([$this->repo->findAll()]);
    }

    /**
     * Show Object
     */
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: User::class))
    ]
    #[Route('/api/user/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): \Symfony\Component\HttpFoundation\JsonResponse
    {
        return $this->json([$user]);
    }


    /**
     * Update Object
     */
    #[OA\RequestBody(
        content: new OA\JsonContent(
            type: 'object',
            ref: new Model(type: User::class),
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Successful updated',
        content: new Model(type: User::class))
    ]
    #[Route('/api/user/{id}', name: 'app_user_update', methods: ['PUT'])]
    public function update(User $user, Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $user,
            'groups' => ['updateUser']
        ]);
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->repo->save($user, true);
        return $this->json($user);
    }

    /**
     * Delete Object
     */
    #[OA\Response(
        response: 204,
        description: 'Successful deleted',
    )]
    #[Route('/api/user/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function delete(User $user): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->repo->remove($user, true);
        return $this->json('', 204);
    }
}
