<?php

namespace App\Controller;

use App\Entity\Order;
use OpenApi\Attributes as OA;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[OA\Tag(name: 'Order')]
class OrderController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private UserRepository $userRepository,
        private ValidatorInterface $validator,
        private OrderRepository $repo
    ) {}

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
            items: new OA\Items(ref: new Model(type: Order::class))
        )
    )]
    #[Route('/api/orders', name: 'app_order_index', methods: ['GET'])]
    public function index(): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $data = $this->serializer->serialize($this->repo->findAll(), 'json', ['groups' => ['getOrders']]);
        return new JsonResponse($data, \Symfony\Component\HttpFoundation\Response::HTTP_OK, [], true);
    }

    /**
     * Fetch By User
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[IsGranted('ROLE_USER')]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Order::class))
        )
    )]
    #[Route('/api/orders/user', name: 'app_order_user', methods: ['GET'])]
    public function user(): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $orders = $this->repo->findByUser($this->getUser());
        $data = $this->serializer->serialize($orders, 'json', ['groups' => ['getOrders']]);

        return new JsonResponse($data, \Symfony\Component\HttpFoundation\Response::HTTP_OK, [], true);
    }

    /**
     * Show Object
     */
    #[
        OA\Response(
            response: 200,
            description: 'Successful response',
            content: new Model(type: Order::class)
        )
    ]
    #[Route('/api/order/{id}', name: 'app_order_show', methods: ['GET'])]
    public function show(Order $order): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $data = $this->serializer->serialize($order, 'json', ['groups' => ['getOrders']]);
        return new JsonResponse($data, \Symfony\Component\HttpFoundation\Response::HTTP_OK, [], true);
    }

    /**
     * Create Object
     *
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[IsGranted('ROLE_USER')]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            type: 'object',
            example: "{ \"id\": \"order 1\" }"
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
                    \"id\": \"order 1\"
                }
            ]"
            )
        )
    ]
    #[Route('/api/order/create', name: 'app_order_create', methods: ['POST'])]
    public function create(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $order = $this->serializer->deserialize($request->getContent(), Order::class, 'json', [
            'groups' => ['createOrder']
        ]);

        $errors = $this->validator->validate($order);
        if (count($errors) > 0) {
            return $this->json($errors, \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->repo->save($order, true);
        $data = $this->serializer->serialize($order, 'json', ['groups' => ['getOrders']]);

        return new JsonResponse($data, \Symfony\Component\HttpFoundation\Response::HTTP_OK, [], true);
    }

    /**
     * Update Object
     */
    #[IsGranted('ROLE_ADMIN')]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            type: 'object',
            example: '{
                "id": "2"
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
                    "id": "2"
                  }'
            )
        )
    ]
    #[Route('/api/order/{id}', name: 'app_order_update', methods: ['PUT'])]
    public function update(Order $order, Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $order = $this->serializer->deserialize($request->getContent(), Order::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $order,
            'groups' => ['updateOrder']
        ]);
        $errors = $this->validator->validate($order);
        if (count($errors) > 0) {
            return $this->json($errors, \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->repo->save($order, true);
        $data = $this->serializer->serialize($order, 'json', ['groups' => ['getOrders']]);
        
        return new JsonResponse($data, \Symfony\Component\HttpFoundation\Response::HTTP_OK, [], true);
    }

    /**
     * Delete Object
     */
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Response(
        response: 204,
        description: 'Successful deleted',
    )]
    #[Route('/api/order/{id}', name: 'app_order_delete', methods: ['DELETE'])]
    public function delete(Order $order): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->repo->remove($order, true);
        return $this->json('Item deleted', \Symfony\Component\HttpFoundation\Response::HTTP_NO_CONTENT);
    }
}
