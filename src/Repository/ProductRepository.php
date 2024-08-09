<?php

namespace App\Repository;

use App\Dto\ProductFilterDto;
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

    public function findByFilters(ProductFilterDto $filter)
    {
        $queryBuilder = $this->createQueryBuilder('p');

        if ($filter->category !== null) {
            $queryBuilder->leftJoin('p.category', 'c')
                ->addSelect('c')
                ->andWhere('c.name LIKE :category')
                ->setParameter('category', '%' . $filter->category . '%');
        }

        if ($filter->term !== null) {
            $queryBuilder->andWhere('p.name LIKE :term OR p.slug LIKE :term OR p.description LIKE :term')
                ->setParameter('term', '%' . $filter->term . '%');
        }

        if ($filter->onSale !== null) {
            $queryBuilder->andWhere('p.onSale = :onSale')
                ->setParameter('onSale', $filter->onSale);
        }

        if ($filter->stock !== null) {
            $queryBuilder->andWhere('p.stock = :stock')
                ->setParameter('stock', $filter->stock);
        }

        if ($filter->minPrice !== null) {
            $queryBuilder->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $filter->minPrice);
        }

        if ($filter->maxPrice !== null) {
            $queryBuilder->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $filter->maxPrice);
        }

        if ($filter->sortBy !== null) {
            switch ($filter->sortBy) {
                case 'price_asc':
                    $queryBuilder->addOrderBy('p.price', 'ASC');
                    break;
                case 'price_desc':
                    $queryBuilder->addOrderBy('p.price', 'DESC');
                    break;
            }
        }

        if ($filter->sortByCreatedAt !== null) {
            switch ($filter->sortByCreatedAt) {
                case 'created_at_asc':
                    $queryBuilder->addOrderBy('p.createdAt', 'ASC');
                    break;
                case 'created_at_desc':
                    $queryBuilder->addOrderBy('p.createdAt', 'DESC');
                    break;
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }

}
