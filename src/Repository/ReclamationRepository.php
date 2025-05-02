<?php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @extends ServiceEntityRepository<Reclamation>
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

    //    /**
    //     * @return Reclamation[] Returns an array of Reclamation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reclamation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
/* 
    public function getAverageResponseTime(): float
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT AVG(
                CASE 
                    WHEN r.created_at IS NOT NULL 
                    THEN TIMESTAMPDIFF(DAY, r.created_at, NOW())
                    ELSE 0
                END
            ) as avg_days
            FROM reclamations r
        ';
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        
        return (float)$result->fetchOne() ?: 0.0;
    }

    public function getMonthlyStatistics(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT 
                EXTRACT(MONTH FROM r.created_at) as month,
                COUNT(r.id) as count
            FROM reclamations r
            GROUP BY month
            ORDER BY month
        ';
        
        $stmt = $conn->prepare($sql);
        $results = $stmt->executeQuery()->fetchAllAssociative();

        $labels = [];
        $data = [];

        foreach ($results as $result) {
            $labels[] = date('M', mktime(0, 0, 0, $result['month'], 1));
            $data[] = $result['count'];
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    public function getTypeDistribution(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT 
                r.subject as type,
                COUNT(r.id) as count
            FROM reclamations r
            GROUP BY type
        ';
        
        $stmt = $conn->prepare($sql);
        $results = $stmt->executeQuery()->fetchAllAssociative();

        $labels = [];
        $data = [];

        foreach ($results as $result) {
            $labels[] = $result['type'];
            $data[] = $result['count'];
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    } */

    public function getMonthlyReclamations(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(id) AS count
            FROM reclamations
            GROUP BY month
            ORDER BY month ASC
        ";
    
        $stmt = $conn->prepare($sql);
        $results = $stmt->executeQuery()->fetchAllAssociative();
    
        $labels = array_column($results, 'month');
        $data = array_column($results, 'count');
    
        return ['labels' => $labels, 'data' => $data];
    }
    
    
    

    public function getAverageResolutionTime(): float
    {
        $allCount = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    
        $resolved = $this->createQueryBuilder('r')
            ->where('r.closed_at IS NOT NULL')
            ->getQuery()
            ->getResult();
    
        if (count($resolved) === 0 || $allCount == 0) {
            return -1; // Use a sentinel value
        }
    
        $totalDays = 0;
        foreach ($resolved as $r) {
            $interval = $r->getCreatedAt()->diff($r->getClosedAt());
            $totalDays += $interval->days;
        }
    
        return $totalDays / $allCount;
    }
    
    
    

public function getRatingStats(): array
{
    $qb = $this->createQueryBuilder('r')
        ->select('r.rating, COUNT(r.id) as count')
        ->where('r.isRated = true')
        ->groupBy('r.rating');

    $results = $qb->getQuery()->getResult();

    $distribution = [];
    $sum = 0;
    $total = 0;

    foreach ($results as $row) {
        $distribution[$row['rating']] = (int) $row['count'];
        $sum += $row['rating'] * $row['count'];
        $total += $row['count'];
    }

    return [
        'average' => $total > 0 ? $sum / $total : 0,
        'distribution' => $distribution
    ];
}


}
