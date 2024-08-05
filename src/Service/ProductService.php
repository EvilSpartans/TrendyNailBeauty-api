<?php

namespace App\Service;

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

    public function getFilteredProducts(Request $request)
    {
        $filterDto = new ProductFilterDto($request);

        $errors = $this->validator->validate($filterDto);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new \Symfony\Component\HttpFoundation\JsonResponse(['errors' => $errorMessages], \Symfony\Component\HttpFoundation\JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->productRepository->findByFilters(
            $filterDto->category,
            $filterDto->name,
            $filterDto->onSale,
            $filterDto->minPrice,
            $filterDto->maxPrice,
            $filterDto->sortBy
        );
    }
}
