<?php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    //    /**
    //     * @return Ticket[] Returns an array of Ticket objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Ticket
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findTicketsByUserId(int $userId): array
{
    return $this->createQueryBuilder('t')
        ->join('t.reservation', 'r')  // Using the relationship
        ->join('r.user', 'u')         // Join through reservation to user
        ->where('u.id = :userId')
        ->setParameter('userId', $userId)
        ->orderBy('t.ticket_id', 'DESC')
        ->getQuery()
        ->getResult();
}

public function findReservedSeatNumbers(int $eventId): array
{
    $result = $this->createQueryBuilder('t')
        ->select('t.seat_number') // Using database column name
        ->join('t.reservation', 'r')
        ->join('r.event', 'e')
        ->where('e.id = :eventId')
        ->andWhere('r.status = :status')
        ->setParameter('eventId', $eventId)
        ->setParameter('status', 'reserved')
        ->getQuery()
        ->getScalarResult();

    return array_column($result, 'seat_number');
}
}
