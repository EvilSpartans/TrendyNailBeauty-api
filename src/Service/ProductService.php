<?php

namespace App\Service;

use App\Classe\ResponseData;
use App\Dto\ProductFilterDto;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductService
{

    public function __construct(
        private ProductRepository $productRepository,
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

        $products = $this->productRepository->findByFilters(
            $filterDto->category,
            $filterDto->term,
            $filterDto->onSale,
            $filterDto->stock,
            $filterDto->minPrice,
            $filterDto->maxPrice,
            $filterDto->sortBy
        );

        return new ResponseData(['products' => $products], \Symfony\Component\HttpFoundation\JsonResponse::HTTP_OK);
    }
}
