<?php
// src/Repository/PollRepository.php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Poll;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Poll>
 *
 * @method Poll|null   find($id, $lockMode = null, $lockVersion = null)
 * @method Poll|null   findOneBy(array $criteria, array $orderBy = null)
 * @method Poll[]      findAll()
 * @method Poll[]      findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PollRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Poll::class);
    }

    /**
     * Retourne le sondage lié à un Post, ou null.
     */
    public function findOneByPost(Post $post): ?Poll
    {
        return $this->findOneBy(['post' => $post]);
    }

    /**
     * Récupère tous les sondages créés après une date donnée.
     *
     * @param \DateTimeInterface $since
     * @return Poll[]
     */
    public function findRecent(\DateTimeInterface $since): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.post.createdAt >= :since')
            ->setParameter('since', $since)
            ->orderBy('p.post.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
