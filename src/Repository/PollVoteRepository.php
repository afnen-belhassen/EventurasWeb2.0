<?php
// src/Repository/PollVoteRepository.php

namespace App\Repository;

use App\Entity\Poll;
use App\Entity\PollVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;

/**
 * @extends ServiceEntityRepository<PollVote>
 *
 * @method PollVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method PollVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method PollVote[]    findAll()
 * @method PollVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PollVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PollVote::class);
    }

    /**
     * Compte le nombre de votes par option pour un sondage donné.
     *
     * @param Poll $poll
     * @return int[]  Tableau des totaux, clé = index d’option, valeur = nombre de votes
     */
    public function countVotesByOption(Poll $poll): array
    {
        $qb = $this->createQueryBuilder('v')
            ->select('v.optionIndex AS idx, COUNT(v.id) AS cnt')
            ->andWhere('v.poll = :poll')
            ->setParameter('poll', $poll)
            ->groupBy('v.optionIndex');

        $rows = $qb->getQuery()->getArrayResult();

        // Initialise tout à zéro
        $counts = array_fill(0, count($poll->getOptions()), 0);
        foreach ($rows as $row) {
            $counts[(int)$row['idx']] = (int)$row['cnt'];
        }

        return $counts;
    }

    /**
     * Récupère les votes d’un sondage, triés par date de vote.
     *
     * @param Poll $poll
     * @param int|null $limit
     * @param int|null $offset
     * @return PollVote[]
     */
    public function findByPoll(Poll $poll, ?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->createQueryBuilder('v')
            ->andWhere('v.poll = :poll')
            ->setParameter('poll', $poll)
            ->orderBy('v.votedAt', 'DESC');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }
        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * (Optionnel) Vérifie si un user a déjà voté cette option.
     *
     * @param Poll $poll
     * @param int $optionIndex
     * @param int|null $userId
     * @return bool
     */
    public function hasUserVoted(Poll $poll, int $optionIndex, ?int $userId): bool
    {
        $qb = $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->andWhere('v.poll = :poll')
            ->andWhere('v.userId = :uid')
            ->setParameters(new ArrayCollection([
                new Parameter('poll', $poll),
                new Parameter('uid', $userId),
            ]));

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }
}
