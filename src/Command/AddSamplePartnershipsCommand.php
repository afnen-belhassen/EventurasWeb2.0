<?php

namespace App\Command;

use App\Entity\Partnership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-sample-partnerships',
    description: 'Adds sample partnerships',
)]
class AddSamplePartnershipsCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $partnerships = [
            [
                'name' => 'TechCorp Solutions Partnership',
                'description' => 'Strategic partnership with TechCorp Solutions to provide innovative technology solutions for our events. This partnership will enhance our digital infrastructure and provide cutting-edge tech support for our attendees.',
                'email' => 'partnerships@techcorp-solutions.com',
                'phone' => '+1 (555) 123-4567',
                'address' => '123 Tech Boulevard, Silicon Valley, CA 94025',
                'status' => 'active',
            ],
            [
                'name' => 'Green Earth Initiative Collaboration',
                'description' => 'Environmental partnership with Green Earth Initiative to promote sustainability in our events. This collaboration will help us implement eco-friendly practices and reduce our carbon footprint.',
                'email' => 'collaborations@greenearth-initiative.org',
                'phone' => '+1 (555) 987-6543',
                'address' => '456 Nature Way, Portland, OR 97201',
                'status' => 'pending',
            ],
            [
                'name' => 'Global Education Foundation Alliance',
                'description' => 'Educational partnership with Global Education Foundation to provide learning opportunities at our events. This alliance will bring expert speakers and workshops to enhance the educational value of our conferences.',
                'email' => 'alliances@globaleducationfoundation.org',
                'phone' => '+1 (555) 234-5678',
                'address' => '789 Education Avenue, Boston, MA 02108',
                'status' => 'active',
            ],
            [
                'name' => 'HealthCare Innovations Sponsorship',
                'description' => 'Healthcare sponsorship partnership with HealthCare Innovations to promote health and wellness at our events. This sponsorship will provide health monitoring services and wellness workshops for our attendees.',
                'email' => 'sponsorships@healthcare-innovations.com',
                'phone' => '+1 (555) 345-6789',
                'address' => '321 Medical Center Drive, Chicago, IL 60601',
                'status' => 'active',
            ],
            [
                'name' => 'Creative Arts Alliance Joint Venture',
                'description' => 'Arts and culture partnership with Creative Arts Alliance to bring artistic elements to our events. This joint venture will enhance the cultural experience of our attendees through art exhibitions and performances.',
                'email' => 'ventures@creativeartsalliance.org',
                'phone' => '+1 (555) 456-7890',
                'address' => '555 Art Street, New York, NY 10001',
                'status' => 'pending',
            ],
        ];

        $io->progressStart(count($partnerships));

        foreach ($partnerships as $partnershipData) {
            $partnership = new Partnership();
            $partnership->setName($partnershipData['name']);
            $partnership->setDescription($partnershipData['description']);
            $partnership->setEmail($partnershipData['email']);
            $partnership->setPhone($partnershipData['phone']);
            $partnership->setAddress($partnershipData['address']);
            $partnership->setStatus($partnershipData['status']);
            
            $this->entityManager->persist($partnership);
            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->success('Successfully added ' . count($partnerships) . ' sample partnerships.');

        return Command::SUCCESS;
    }
} 