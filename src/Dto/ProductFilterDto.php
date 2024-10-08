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

    #[Assert\Type('string')]
    public ?string $stock = null;

    #[Assert\PositiveOrZero(message: "The minimum price must be a positive number.")]
    public ?float $minPrice = null;

    #[Assert\PositiveOrZero(message: "The maximum price must be a positive number.")]
    public ?float $maxPrice = null;

    #[Assert\Type('string')]
    #[Assert\Choice(choices: ['created_at_asc', 'created_at_desc'], message: "The sort order must be 'created_at_asc' or 'created_at_desc'.")]
    public ?string $sortByCreatedAt = null;

    #[Assert\Type('string')]
    #[Assert\Choice(choices: ['price_asc', 'price_desc'], message: "The sort order must be 'price_asc' or 'price_desc'.")]
    public ?string $sortBy = null;

    public function __construct(Request $request)
    {
        $this->category = $request->query->get('category');
        $this->term = $request->query->get('term');
        $this->onSale = filter_var($request->query->get('onSale'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $this->stock = $request->query->get('stock');
        $this->minPrice = $request->query->get('minPrice') ? (float)$request->query->get('minPrice') : null;
        $this->maxPrice = $request->query->get('maxPrice') ? (float)$request->query->get('maxPrice') : null;
        $this->sortByCreatedAt = $request->query->get('sortByCreatedAt');
        $this->sortBy = $request->query->get('sortBy');
    }
}
