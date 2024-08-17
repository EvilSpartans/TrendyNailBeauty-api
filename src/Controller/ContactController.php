<?php

namespace App\Controller;

use App\Entity\Contact;
use OpenApi\Attributes as OA;
use App\Repository\ContactRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[OA\Tag(name: 'contact')]
class ContactController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private ContactRepository $repo
    ) {
    }

    /**
     * Fetch All
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Contact::class))
        )
    )]
    #[Route('/api/contacts', name: 'api_contact_index', methods: ['GET'])]
    public function index(): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $data = $this->serializer->serialize($this->repo->findAll(), 'json', ['groups' => ['getContacts']]);
        return new \Symfony\Component\HttpFoundation\JsonResponse($data, \Symfony\Component\HttpFoundation\Response::HTTP_OK, [], true);
    }

    /**
     * Create Object
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[OA\RequestBody(
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property('email', type: 'string'),
                new OA\Property('subject', type: 'string'),
                new OA\Property('content', type: 'string'),
            ],
        )
    )]
    #[
        OA\Response(
            response: 201,
            description: 'Successful created',
            content: new OA\JsonContent(
                type: 'object',
                example: '
                    {
                        "id": 1,
                        "email": "contact@jobissim.com",
                        "subject": "Job opportunity",
                        "content": "Hello this is my message content"
                    }'
            )
        )
    ]
    #[Route(path: '/api/contact/create', name: 'api_contact_create', methods: ['POST'])]
    public function create(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $contact = $this->serializer->deserialize($request->getContent(), Contact::class, 'json', ['groups' => ['createContact']]);
        $errors = $this->validator->validate($contact);
        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->repo->save($contact, true);
        return $this->json($contact, 201);
    }

    /**
     * Delete Object
     */
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Response(
        response: 204,
        description: 'Successful deleted',
    )]
    #[Route('/api/contact/{id}', name: 'app_contact_delete', methods: ['DELETE'])]
    public function delete(Contact $contact): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->repo->remove($contact, true);
        return $this->json('Item deleted', \Symfony\Component\HttpFoundation\Response::HTTP_NO_CONTENT);
    }

}