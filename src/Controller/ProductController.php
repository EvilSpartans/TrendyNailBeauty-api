<?php

namespace App\Controller;

use App\Entity\Product;
use OpenApi\Attributes as OA;
use App\Service\ProductService;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[OA\Tag(name: 'Product')]
class ProductController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private ProductRepository $repo
    ) {
    }

    /**
     * Fetch All
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[OA\Parameter(
        name: "term",
        in: "query",
        description: "Filter by term",
        schema: new OA\Schema(type: "text")
    )]
    #[OA\Parameter(
        name: "category",
        in: "query",
        description: "Filter by category name",
        schema: new OA\Schema(type: "text")
    )]
    #[OA\Parameter(
        name: "onSale",
        in: "query",
        description: "Filter by onSale",
        schema: new OA\Schema(type: "boolean")
    )]
    #[OA\Parameter(
        name: "stock",
        in: "query",
        description: "Filter by stock",
        schema: new OA\Schema(type: "boolean")
    )]
    #[OA\Parameter(
        name: "minPrice",
        in: "query",
        description: "Filter by minPrice",
        schema: new OA\Schema(type: "float")
    )]
    #[OA\Parameter(
        name: "maxPrice",
        in: "query",
        description: "Filter by maxPrice",
        schema: new OA\Schema(type: "float")
    )]
    #[OA\Parameter(
        name: "sortBy",
        in: "query",
        description: "Order by price (price_asc)",
        schema: new OA\Schema(type: "text")
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Product::class))
        )
    )]
    #[Route('/api/products', name: 'app_product_index', methods: ['GET'])]
    public function index(Request $request, ProductService $service): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $responseData = $service->getFilteredProducts($request);
        return $this->json($responseData->getData()['products'], $responseData->getStatus());
    }

    /**
     * Show Object
     */
    #[
        OA\Response(
            response: 200,
            description: 'Successful response',
            content: new Model(type: Product::class)
        )
    ]
    #[Route('/api/product/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): \Symfony\Component\HttpFoundation\JsonResponse
    {
        return $this->json($product);
    }

    /**
     * Create Object
     *
     * @param Product $product
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[IsGranted('ROLE_ADMIN')]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property('name', type: 'string'),
                new OA\Property('description', type: 'string'),
                new OA\Property('categoryId', type: 'string'),
            ],
        )
    )]
    #[
        OA\Response(
            response: 201,
            description: 'Successful created',
            content: new Model(type: Product::class)
        )
    ]
    #[Route('/api/product/create', name: 'app_product_create', methods: ['POST'])]
    public function create(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $product = $this->serializer->deserialize($request->getContent(), Product::class, 'json', [
            'groups' => ['createProduct']
        ]);
        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->repo->save($product, true);
        return $this->json($product, 201);
    }

    /**
     * Update Object
     */
    #[IsGranted('ROLE_ADMIN')]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property('name', type: 'string'),
                new OA\Property('description', type: 'string'),
                new OA\Property('categoryId', type: 'string'),
            ],
        )
    )]
    #[
        OA\Response(
            response: 201,
            description: 'Successful updated',
            content: new Model(type: Product::class)
        )
    ]
    #[Route('/api/product/{id}', name: 'app_product_update', methods: ['PUT'])]
    public function update(Product $product, Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $product = $this->serializer->deserialize($request->getContent(), Product::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $product,
            'groups' => ['updateProduct']
        ]);
        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->repo->save($product, true);
        return $this->json($product);
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
            content: new Model(type: Product::class)
        )
    ]
    #[Route(path: '/api/product/{id}/upload', name: 'app_product_upload', methods: ['POST'])]
    public function upload(Product $product, Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json('Error while downloading', 400);
        }

        $product->setImageFile($file);
        $this->repo->save($product, true);
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
    #[Route('/api/product/{id}', name: 'app_product_delete', methods: ['DELETE'])]
    public function delete(Product $product): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->repo->remove($product, true);
        return $this->json('', 204);
    }
}
