<?php

namespace App\Service;

use App\Classe\ResponseData;
use App\Dto\ProductFilterDto;
use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductService
{

    public function __construct(
        private ProductRepository $productRepository,
        private NormalizerInterface $normalizer,
        private PaginatorInterface $paginator,
        private ValidatorInterface $validator
    ) {
    }

    public function getFilteredProducts(Request $request): ResponseData
    {
        $filterDto = new ProductFilterDto($request);

        $errors = $this->validator->validate($filterDto);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new ResponseData(['errors' => $errorMessages], \Symfony\Component\HttpFoundation\JsonResponse::HTTP_BAD_REQUEST);
        }

        $query = $this->productRepository->findByFilters($filterDto);

        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            9
        );

        $currentPage = $pagination->getCurrentPageNumber();
        $totalPages = ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage());

        $products = [];
        foreach ($pagination->getItems() as $product) {
            $productData = $this->normalizer->normalize($product, null, ['groups' => ['getProducts']]);
            $products[] = $productData;
        }

        $response = [
            'products' => $products,
            'page' => $currentPage,
            'countPage' => $totalPages
        ];

        return new ResponseData($response, \Symfony\Component\HttpFoundation\JsonResponse::HTTP_OK);
    }
}
