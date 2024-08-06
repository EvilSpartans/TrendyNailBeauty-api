<?php

namespace App\Dto;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class ProductFilterDto
{
    #[Assert\Type('string')]
    public ?string $category = null;

    #[Assert\Type('string')]
    #[Assert\Length(max: 255)]
    public ?string $term = null;

    #[Assert\Type('bool')]
    public ?bool $onSale = null;

    #[Assert\Type('bool')]
    public ?bool $stock = null;

    #[Assert\PositiveOrZero(message: "The minimum price must be a positive number.")]
    public ?float $minPrice = null;

    #[Assert\PositiveOrZero(message: "The maximum price must be a positive number.")]
    public ?float $maxPrice = null;

    #[Assert\Type('string')]
    #[Assert\Choice(choices: ['price_asc', 'price_desc'], message: "The sort order must be 'price_asc' or 'price_desc'.")]
    public ?string $sortBy = null;

    public function __construct(Request $request)
    {
        $this->category = $request->query->get('category');
        $this->term = $request->query->get('term');
        $this->onSale = filter_var($request->query->get('onSale'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $this->stock = filter_var($request->query->get('stock'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $this->minPrice = $request->query->get('minPrice') ? (float)$request->query->get('minPrice') : null;
        $this->maxPrice = $request->query->get('maxPrice') ? (float)$request->query->get('maxPrice') : null;
        $this->sortBy = $request->query->get('sortBy');
    }
}
