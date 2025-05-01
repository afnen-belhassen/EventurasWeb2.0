<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PollRepository;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: PollRepository::class)]
class Poll
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    // Chaque sondage est lié à un Post
    #[ORM\OneToOne(targetEntity: Post::class, inversedBy: 'poll')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Post $post;

    // JSON stockant les options, par exemple ["Red","Blue","Green"]
    #[ORM\Column(type: 'json')]
    private array $options = [];

    public function __construct(Post $post, array $options)
    {
        $this->post    = $post;
        $this->options = $options;
    }

    public function getId(): ?int { return $this->id; }
    public function getPost(): Post { return $this->post; }
    public function getOptions(): array { return $this->options; }
    public function create(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        // … création et persist du $post …
        $em->persist($post);
        $em->flush(); // on a l’ID du post

        // puis, si on a des options de sondage
        $json = $request->request->get('poll_options');
        if ($json) {
            $options = json_decode($json, true);
            if (is_array($options) && count($options) >= 2) {
                $poll = new Poll($post, $options);
                $post->setPoll($poll);
                $em->persist($poll);
                $em->flush();
            }
        }

        return $this->redirectToRoute('app_forum');
    }
}
