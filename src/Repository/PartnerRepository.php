<?php

namespace App\Repository;

use App\Entity\Partner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Partner>
 *
 * @method Partner|null find($id, $lockMode = null, $lockVersion = null)
 * @method Partner|null findOneBy(array $criteria, array $orderBy = null)
 * @method Partner[]    findAll()
 * @method Partner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PartnerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Partner::class);
    }

    public function save(Partner $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Partner $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllSorted(string $sortBy = 'name', string $direction = 'asc'): array
    {
        $qb = $this->createQueryBuilder('p');

        // Validate sort field
        if (!in_array($sortBy, ['name', 'rating'])) {
            $sortBy = 'name';
        }

        // Validate direction
        $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';

        // Join with partnerships to ensure they are loaded
        $qb->leftJoin('p.partnerships', 'partnerships')
           ->addSelect('partnerships');

        $qb->orderBy('p.' . $sortBy, $direction);

        return $qb->getQuery()->getResult();
    }
} 