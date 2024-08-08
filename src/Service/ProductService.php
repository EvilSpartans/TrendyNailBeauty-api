<?php

namespace App\Service;

use App\Classe\ResponseData;
use App\Dto\ProductFilterDto;
use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class ProductService
{

    public function __construct(
        private ProductRepository $productRepository,
        private NormalizerInterface $normalizer,
        private PaginatorInterface $paginator,
        private ValidatorInterface $validator,
        private CacheInterface $cache
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

        $cacheKey = sprintf(
            'filtered_products_%s_%s_%s_%s_%s_%s_%s_%s',
            $filterDto->category,
            $filterDto->term,
            $filterDto->onSale ? '1' : '0',
            $filterDto->stock ? '1' : '0',
            $filterDto->minPrice,
            $filterDto->maxPrice,
            $filterDto->sortBy,
            $filterDto->sortByCreatedAt
        );
        
        $data = $this->cache->get($cacheKey, function () use ($filterDto, $request) {
            $query = $this->productRepository->findByFilters(
                $filterDto->category,
                $filterDto->term,
                $filterDto->onSale,
                $filterDto->stock,
                $filterDto->minPrice,
                $filterDto->maxPrice,
                $filterDto->sortBy,
                $filterDto->sortByCreatedAt
            );

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

            return [
                'products' => $products,
                'page' => $currentPage,
                'countPage' => $totalPages
            ];
        });

        return new ResponseData($data, \Symfony\Component\HttpFoundation\JsonResponse::HTTP_OK);
    }
}
