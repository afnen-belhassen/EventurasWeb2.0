<?php

namespace App\Command;

use App\Entity\Partner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-random-ratings',
    description: 'Adds random ratings to all partners',
)]
class AddRandomRatingsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Adding random ratings to partners');

        $partners = $this->entityManager->getRepository(Partner::class)->findAll();
        $count = 0;

        foreach ($partners as $partner) {
            // Generate random rating between 1 and 5
            $rating = round(rand(10, 50) / 10, 1);
            // Generate random number of ratings between 1 and 20
            $ratingCount = rand(1, 20);

            $partner->setRating($rating);
            $partner->setRatingCount($ratingCount);
            $count++;
        }

        $this->entityManager->flush();
        $io->success(sprintf('Successfully added random ratings to %d partners', $count));

        return Command::SUCCESS;
    }
} 