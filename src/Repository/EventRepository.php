<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
        // src/Repository/EventRepository.php
public function searchByName(string $query): array
{
    return $this->createQueryBuilder('e')
        ->where('e.title LIKE :query')
        ->setParameter('query', '%'.$query.'%')
        ->orderBy('e.date_event', 'ASC')
        ->getQuery()
        ->getResult();
}
    public function findByCriteria(string $criteria): array
    {
         $qb = $this->createQueryBuilder('e')
        ->andWhere('e.status = :status')
        ->setParameter('status', 'Accepté');
        $now = new \DateTime();

        switch ($criteria) {
            case 'passed':
                $qb->andWhere('e.date_event < :now')
                   ->setParameter('now', $now)
                   ->orderBy('e.date_event', 'DESC');
                break;

            case 'upcoming':
                $qb->andWhere('e.date_event >= :now')
                   ->setParameter('now', $now)
                   ->orderBy('e.date_event', 'ASC');
                break;

            case 'price_asc':
                $qb->orderBy('e.prix', 'ASC');
                break;

            case 'price_desc':
                $qb->orderBy('e.prix', 'DESC');
                break;

            default:
                // par défaut, on renvoie tous, triés par date_event croissante
                $qb->orderBy('e.date_event', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }
    // src/Repository/EventRepository.php

    public function getEventsCountByCategory(): array
{
    return $this->createQueryBuilder('e')
        ->select('c.name as category, COUNT(e.id_event) as count')
        ->join('e.category', 'c')
        ->groupBy('c.category_id')  // Changed from c.name to c.category_id
        ->addGroupBy('c.name')      // Added to maintain consistency with select
        ->getQuery()
        ->getResult();
}
}
