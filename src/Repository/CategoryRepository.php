<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function save(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByFilters(
        $name = null,
        $mostProducts = null,
        $mostOnSale = null,
        $outOfStock = null
    ) {

        if (!$name && !$mostProducts && !$mostOnSale && !$outOfStock) {
            return $this->findAll();
        }
        
        $queryBuilder = $this->createQueryBuilder('c')
            ->leftJoin('c.products', 'p')
            ->addSelect('COUNT(p.id) as HIDDEN productCount');

        if ($name) {
            $queryBuilder->andWhere('c.name LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }

        if ($mostProducts) {
            $queryBuilder->groupBy('c.id')
                ->orderBy('productCount', 'DESC');
        }

        if ($mostOnSale) {
            $queryBuilder->andWhere('p.onSale = :onSale')
                ->setParameter('onSale', true)
                ->groupBy('c.id')
                ->orderBy('productCount', 'DESC');
        }

        if ($outOfStock) {
            $queryBuilder->andWhere('p.stock = 0')
                ->groupBy('c.id');
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
