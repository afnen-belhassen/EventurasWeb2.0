<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join; 

class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findAllOrderedByDate()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllWithComments()
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.comments', 'c')
            ->addSelect('c')
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // src/Repository/PostRepository.php

    public function findByCriteria(string $search, string $category, string $sort): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p');

        if ('' !== trim($search)) {
            $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->like('p.title',   ':kw'),
                    $qb->expr()->like('p.content', ':kw')
                ))
                ->setParameter('kw', '%'.$search.'%');
        }

        if ('' !== $category) {
            $qb->andWhere('p.category = :cat')
            ->setParameter('cat', $category);
        }

        if ('likes' === $sort) {
            $qb->leftJoin(
                    'App\Entity\Like',
                    'l',
                    Join::WITH,
                    'l.postId = p.id'
                )
            ->addSelect('COUNT(l.id) AS HIDDEN likeCount')
            ->groupBy('p.id')
            ->orderBy('likeCount', 'DESC');
        } else {
            $qb->orderBy('p.created_at', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

} 