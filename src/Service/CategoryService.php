<?php

namespace App\Service;

use App\Classe\ResponseData;
use App\Dto\CategoryFilterDto;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryService
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private ValidatorInterface $validator
    ) {
    }

    public function getFilteredCategories(Request $request): ResponseData
    {
        $filterDto = new CategoryFilterDto($request);

        $errors = $this->validator->validate($filterDto);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new ResponseData(['errors' => $errorMessages], \Symfony\Component\HttpFoundation\JsonResponse::HTTP_BAD_REQUEST);
        }

        $categories = $this->categoryRepository->findByFilters(
            $filterDto->name,
            $filterDto->mostProducts,
            $filterDto->mostOnSale,
            $filterDto->outOfStock 
        );

        return new ResponseData(['categories' => $categories], \Symfony\Component\HttpFoundation\JsonResponse::HTTP_OK);
    }
}