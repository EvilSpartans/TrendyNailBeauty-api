<?php

namespace App\Normalizer;

use App\Repository\CategoryRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ProductNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private CategoryRepository $repo,
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface&DenormalizerInterface $normalizer,
    ) {
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['categoryId'] = $object->getCategory() ? $object->getCategory()->getId() : null;

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof \App\Entity\Product;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        $product = $this->normalizer->denormalize($data, $type, $format, $context);
        if (empty($data['categoryId'])) {
            return $product;
        }

        $category = $this->repo->find($data['categoryId']);
        if ($category) {
            $product->setCategory($category);
        }

        return $product;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return $type == \App\Entity\Product::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            \App\Entity\Product::class => true,
        ];
    }
}
