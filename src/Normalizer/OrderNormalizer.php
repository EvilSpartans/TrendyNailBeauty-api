<?php

namespace App\Normalizer;

use App\Repository\ProductRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class OrderNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private ProductRepository $repo,
        private Security $security,
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface&DenormalizerInterface $normalizer,
    ) {
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['productIds'] = array_map(fn($product) => $product->getId(), $object->getProducts()->toArray());

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof \App\Entity\Order;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        $order = $this->normalizer->denormalize($data, $type, $format, $context);
        $user = $this->security->getUser();

        if (!empty($data['productIds'])) {
            foreach ($data['productIds'] as $productId) {
                $product = $this->repo->find($productId);
                if ($product) {
                    $order->addProduct($product);
                }
            }
        }

        if ($user) {
            $order->setUser($user);
        }

        return $order;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return $type == \App\Entity\Order::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            \App\Entity\Order::class => true,
        ];
    }
}
