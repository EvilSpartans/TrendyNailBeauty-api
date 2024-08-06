<?php

namespace App\Dto;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class CategoryFilterDto
{
    #[Assert\Type('string')]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\Type('bool')]
    public ?bool $mostProducts = null;

    #[Assert\Type('bool')]
    public ?bool $mostOnSale = null;

    #[Assert\Type('bool')]
    public ?bool $outOfStock = null;

    public function __construct(Request $request)
    {
        $this->name = $request->query->get('name');
        $this->mostProducts = filter_var($request->query->get('mostProducts'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $this->mostOnSale = filter_var($request->query->get('mostOnSale'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $this->outOfStock = filter_var($request->query->get('outOfStock'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE); 
    }
}
