<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByFilters(
        $category = null,
        $name = null,
        $onSale = null,
        $minPrice = null,
        $maxPrice = null,
        $sortBy = null
    ) {
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c');

        if ($category) {
            $queryBuilder->andWhere('c.name = :category')
                ->setParameter('category', $category);
        }

        if ($name) {
            $queryBuilder->andWhere('p.name LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }

        if ($onSale) {
            $queryBuilder->andWhere('p.onSale = :onSale')
                ->setParameter('onSale', $onSale);
        }

        if ($minPrice) {
            $queryBuilder->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice) {
            $queryBuilder->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        if ($sortBy) {
            if ($sortBy === 'price_asc') {
                $queryBuilder->orderBy('p.price', 'ASC');
            } elseif ($sortBy === 'price_desc') {
                $queryBuilder->orderBy('p.price', 'DESC');
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
