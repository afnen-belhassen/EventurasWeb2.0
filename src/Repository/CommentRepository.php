<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function findByPostOrderedByDate($post)
    {
        return $this->createQueryBuilder('c')
            ->where('c.post = :post')
            ->setParameter('post', $post)
            ->orderBy('c.created_at', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 