<?php

namespace App\Controller;

use App\Entity\Order;
use OpenApi\Attributes as OA;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
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
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Order::class))
        )
    )]
    #[Route('/api/orders', name: 'app_order_index', methods: ['GET'])]
    public function index(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $user = $this->userRepository->findOneBy(['id' => $request->get('userId')]);
        $orders = $this->repo->findByUser($user);
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
        return $this->json($order, \Symfony\Component\HttpFoundation\Response::HTTP_CREATED);
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
        return $this->json($order, \Symfony\Component\HttpFoundation\Response::HTTP_CREATED);
    }

    /**
     * Retrieve Valid Checkout
     */
    #[Route('/order/sucess/{id}', name: 'app_order_success')]
    public function sucess(Order $order): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $order->setStatus("Valid");
        $this->repo->save($order, true);

        return $this->json([
            "status" => "Opération validée"
        ], \Symfony\Component\HttpFoundation\Response::HTTP_CREATED);
    }

    /**
     * Retrieve Rejected Checkout
     */
    #[Route('/order/error/{id}', name: 'app_order_error')]
    public function error(Order $order): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $order->setStatus("Rejected");
        $this->repo->save($order, true);

        return $this->json([
            "status" => "Opération rejettée"
        ], \Symfony\Component\HttpFoundation\Response::HTTP_CREATED);
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
