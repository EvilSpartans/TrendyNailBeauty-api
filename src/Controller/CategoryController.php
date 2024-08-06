<?php

namespace App\Controller;

use App\Entity\Category;
use OpenApi\Attributes as OA;
use App\Service\CategoryService;
use App\Repository\CategoryRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[OA\Tag(name: 'Category')]
class CategoryController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private CategoryRepository $repo
    ) {
    }

    /**
     * Fetch All
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[OA\Parameter(
        name: "name",
        in: "query",
        description: "Filter by name",
        schema: new OA\Schema(type: "text")
    )]
    #[OA\Parameter(
        name: "mostProducts",
        in: "query",
        description: "Filter by mostProducts",
        schema: new OA\Schema(type: "boolean")
    )]
    #[OA\Parameter(
        name: "mostOnSale",
        in: "query",
        description: "Filter by mostOnSale",
        schema: new OA\Schema(type: "boolean")
    )]
    #[OA\Parameter(
        name: "outOfStock",
        in: "query",
        description: "Filter by outOfStock",
        schema: new OA\Schema(type: "boolean")
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Category::class))
        )
    )]
    #[Route('/api/categories', name: 'app_category_index', methods: ['GET'])]
    public function index(Request $request, CategoryService $service): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $responseData = $service->getFilteredCategories($request);
        $data = $this->serializer->serialize($responseData->getData()['categories'], 'json', ['groups' => ['getCategories']]);

        return new JsonResponse($data, \Symfony\Component\HttpFoundation\Response::HTTP_OK, [], true);
    }

    /**
     * Show Object
     */
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Category::class))
    ]
    #[Route('/api/category/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category): \Symfony\Component\HttpFoundation\JsonResponse
    {
        return $this->json($category, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }

    /**
     * Create Object
     *
     * @param Category $category
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[IsGranted('ROLE_ADMIN')]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            type: 'object',
            example: "{ \"name\": \"Category 1\" }"
        )
    )]
    #[
        OA\Response(
            response: 201,
            description: 'Successful created',
            content: new OA\JsonContent(
                type: 'object',
                example: "[
                {
                    \"name\": \"Category 1\"
                }
            ]"
            )
        )
    ]
    #[Route('/api/category/create', name: 'app_category_create', methods: ['POST'])]
    public function create(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $category = $this->serializer->deserialize($request->getContent(), Category::class, 'json', [
            'groups' => ['createCategory']
        ]);
        $errors = $this->validator->validate($category);
        if (count($errors) > 0) {
            return $this->json($errors, \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->repo->save($category, true);
        return $this->json($category, \Symfony\Component\HttpFoundation\Response::HTTP_CREATED);
    }

    /**
     * Update Object
     */
    #[IsGranted('ROLE_ADMIN')]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            type: 'object',
            example: '{
                "name": "Vêtements"
              }'
        )
    )]
    #[
        OA\Response(
            response: 201,
            description: 'Successful updated',
            content: new OA\JsonContent(
                type: 'object',
                example: '{
                    "name": "Vêtements"
                  }'
            )
        )
    ]
    #[Route('/api/category/{id}', name: 'app_category_update', methods: ['PUT'])]
    public function update(Category $category, Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $category = $this->serializer->deserialize($request->getContent(), Category::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $category,
            'groups' => ['updateCategory']
        ]);
        $errors = $this->validator->validate($category);
        if (count($errors) > 0) {
            return $this->json($errors, \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->repo->save($category, true);
        return $this->json($category, \Symfony\Component\HttpFoundation\Response::HTTP_CREATED);
    }

    /**
     * Upload image
     */
    #[IsGranted('ROLE_ADMIN')]
    #[OA\RequestBody(
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(properties: [
                    new OA\Property(
                        property: 'file',
                        type: 'file',
                    ),
                ])
            ),
        ]
    )]
    #[
        OA\Response(
            response: 201,
            description: 'Successful uploaded',
            content: new Model(type: Category::class)
        )
    ]
    #[Route(path: '/api/category/{id}/upload', name: 'app_category_upload', methods: ['POST'])]
    public function upload(Category $category, Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json('Error while downloading', 400);
        }

        $category->setImageFile($file);
        $this->repo->save($category, true);
        return $this->json('Successful uploaded', 201);
    }

    /**
     * Delete Object
     */
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Response(
        response: 204,
        description: 'Successful deleted',
    )]
    #[Route('/api/category/{id}', name: 'app_category_delete', methods: ['DELETE'])]
    public function delete(Category $category): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->repo->remove($category, true);
        return $this->json('Item deleted', \Symfony\Component\HttpFoundation\Response::HTTP_NO_CONTENT);
    }
}
