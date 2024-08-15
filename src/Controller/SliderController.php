<?php

namespace App\Controller;

use App\Entity\Slider;
use OpenApi\Attributes as OA;
use App\Repository\SliderRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[OA\Tag(name: 'Slider')]
class SliderController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private SliderRepository $repo
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
            items: new OA\Items(ref: new Model(type: Slider::class))
        )
    )]
    #[Route('/api/sliders', name: 'app_slider_index', methods: ['GET'])]
    public function index(): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $data = $this->serializer->serialize($this->repo->findAll(), 'json', ['groups' => ['getSliders']]);
        return new JsonResponse($data, \Symfony\Component\HttpFoundation\Response::HTTP_OK, [], true);
    }

    /**
     * Show Object
     */
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Slider::class))
    ]
    #[Route('/api/slider/{id}', name: 'app_slider_show', methods: ['GET'])]
    public function show(Slider $slider): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $data = $this->serializer->serialize($slider, 'json', ['groups' => ['getSliders']]);
        return new JsonResponse($data, \Symfony\Component\HttpFoundation\Response::HTTP_OK, [], true);
    }

    /**
     * Create Object
     *
     * @param Slider $slider
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[IsGranted('ROLE_ADMIN')]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            type: 'object',
            example: "{ \"name\": \"Slider 1\" }"
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
                    \"name\": \"Slider 1\"
                }
            ]"
            )
        )
    ]
    #[Route('/api/slider/create', name: 'app_slider_create', methods: ['POST'])]
    public function create(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $slider = $this->serializer->deserialize($request->getContent(), Slider::class, 'json', [
            'groups' => ['createSlider']
        ]);
        $errors = $this->validator->validate($slider);
        if (count($errors) > 0) {
            return $this->json($errors, \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->repo->save($slider, true);
        return $this->json($slider, \Symfony\Component\HttpFoundation\Response::HTTP_CREATED);
    }

    /**
     * Update Object
     */
    #[IsGranted('ROLE_ADMIN')]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            type: 'object',
            example: '{
                "title": "Vêtements"
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
                    "title": "Vêtements"
                  }'
            )
        )
    ]
    #[Route('/api/slider/{id}', name: 'app_slider_update', methods: ['PUT'])]
    public function update(Slider $slider, Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $slider = $this->serializer->deserialize($request->getContent(), Slider::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $slider,
            'groups' => ['updateSlider']
        ]);
        $errors = $this->validator->validate($slider);
        if (count($errors) > 0) {
            return $this->json($errors, \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->repo->save($slider, true);
        return $this->json($slider, \Symfony\Component\HttpFoundation\Response::HTTP_CREATED);
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
            content: new Model(type: Slider::class)
        )
    ]
    #[Route(path: '/api/slider/{id}/upload', name: 'app_slider_upload', methods: ['POST'])]
    public function upload(Slider $slider, Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json('Error while downloading', 400);
        }

        $slider->setImageFile($file);
        $this->repo->save($slider, true);
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
    #[Route('/api/slider/{id}', name: 'app_slider_delete', methods: ['DELETE'])]
    public function delete(Slider $slider): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->repo->remove($slider, true);
        return $this->json('Item deleted', \Symfony\Component\HttpFoundation\Response::HTTP_NO_CONTENT);
    }
}
