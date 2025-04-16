<?php

namespace App\Command;

use App\Repository\PartnershipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:clear-partnerships',
    description: 'Clear all partnerships from the database and remove associated files',
)]
class ClearPartnershipsCommand extends Command
{
    private $entityManager;
    private $partnershipRepository;
    private $filesystem;
    private $projectDir;

    public function __construct(
        EntityManagerInterface $entityManager,
        PartnershipRepository $partnershipRepository,
        Filesystem $filesystem,
        ParameterBagInterface $params
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->partnershipRepository = $partnershipRepository;
        $this->filesystem = $filesystem;
        $this->projectDir = $params->get('kernel.project_dir');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Ask for confirmation
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            'Are you sure you want to delete ALL partnerships? This action cannot be undone. (y/N) ',
            false
        );
        
        if (!$helper->ask($input, $output, $question)) {
            $io->warning('Operation cancelled.');
            return Command::SUCCESS;
        }
        
        // Get all partnerships
        $partnerships = $this->partnershipRepository->findAll();
        $count = count($partnerships);
        
        if ($count === 0) {
            $io->success('No partnerships found to delete.');
            return Command::SUCCESS;
        }
        
        $io->note("Found {$count} partnerships to delete.");
        
        // Delete contract files
        $contractsDir = $this->projectDir . '/public/uploads/organizer/contracts/';
        $signedContractsDir = $this->projectDir . '/public/uploads/organizer/signed_contracts/';
        
        $deletedFiles = 0;
        
        // Delete contract files
        if ($this->filesystem->exists($contractsDir)) {
            $files = glob($contractsDir . 'contract_*.pdf');
            foreach ($files as $file) {
                $this->filesystem->remove($file);
                $deletedFiles++;
            }
        }
        
        // Delete signed contract files
        if ($this->filesystem->exists($signedContractsDir)) {
            $files = glob($signedContractsDir . 'signed_contract_*.pdf');
            foreach ($files as $file) {
                $this->filesystem->remove($file);
                $deletedFiles++;
            }
        }
        
        // Delete partnerships from database
        foreach ($partnerships as $partnership) {
            $this->entityManager->remove($partnership);
        }
        
        $this->entityManager->flush();
        
        $io->success("Successfully deleted {$count} partnerships and {$deletedFiles} associated files.");
        
        return Command::SUCCESS;
    }
} 