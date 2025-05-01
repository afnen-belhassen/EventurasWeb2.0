<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PollVoteRepository;

#[ORM\Entity(repositoryClass: PollVoteRepository::class)]
class PollVote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Poll::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Poll $poll;

    #[ORM\Column(type: 'integer')]
    private int $optionIndex;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $votedAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $userId = null; // À adapter si tu veux une vraie relation avec User

    public function __construct(Poll $poll, int $optionIndex, ?int $userId = null)
    {
        $this->poll = $poll;
        $this->optionIndex = $optionIndex;
        $this->userId = $userId;
        $this->votedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getPoll(): Poll { return $this->poll; }

    public function getOptionIndex(): int { return $this->optionIndex; }

    public function getVotedAt(): \DateTimeImmutable { return $this->votedAt; }

    public function getUserId(): ?int { return $this->userId; }

    public function setUserId(?int $userId): void { $this->userId = $userId; }
}
