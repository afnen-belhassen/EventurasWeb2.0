<?php

namespace App\Command;

use App\Entity\Partner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;

#[AsCommand(
    name: 'app:add-sample-partners',
    description: 'Adds sample partners with images and website URLs',
)]
class AddSamplePartnersCommand extends Command
{
    private $entityManager;
    private $filesystem;
    private $httpClient;
    private $uploadDir;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->filesystem = new Filesystem();
        $this->httpClient = HttpClient::create();
        $this->uploadDir = 'public/uploads/partners';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Ensure upload directory exists
        if (!$this->filesystem->exists($this->uploadDir)) {
            $this->filesystem->mkdir($this->uploadDir, 0777);
        }

        $partners = [
            [
                'name' => 'TechCorp Solutions',
                'description' => 'TechCorp Solutions is a leading provider of innovative technology solutions for businesses of all sizes. We specialize in cloud computing, cybersecurity, and digital transformation services.',
                'website' => 'https://www.techcorp-solutions.com',
                'email' => 'contact@techcorp-solutions.com',
                'phone' => '+1 (555) 123-4567',
                'address' => '123 Tech Boulevard, Silicon Valley, CA 94025',
                'imageUrl' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
                'videoPath' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ],
            [
                'name' => 'Green Earth Initiative',
                'description' => 'Green Earth Initiative is dedicated to environmental conservation and sustainable development. We work with communities worldwide to implement eco-friendly practices and protect natural resources.',
                'website' => 'https://www.greenearth-initiative.org',
                'email' => 'info@greenearth-initiative.org',
                'phone' => '+1 (555) 987-6543',
                'address' => '456 Nature Way, Portland, OR 97201',
                'imageUrl' => 'https://images.unsplash.com/photo-1472214103451-9374bd1c798e?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
                'videoPath' => 'https://vimeo.com/824804225',
            ],
            [
                'name' => 'Global Education Foundation',
                'description' => 'The Global Education Foundation is committed to making quality education accessible to everyone. We support educational programs in underserved communities and provide scholarships to deserving students.',
                'website' => 'https://www.globaleducationfoundation.org',
                'email' => 'info@globaleducationfoundation.org',
                'phone' => '+1 (555) 234-5678',
                'address' => '789 Education Avenue, Boston, MA 02108',
                'imageUrl' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
                'videoPath' => 'https://www.youtube.com/watch?v=9bZkp7q19f0',
            ],
            [
                'name' => 'HealthCare Innovations',
                'description' => 'HealthCare Innovations is at the forefront of medical technology advancement. We develop cutting-edge healthcare solutions that improve patient outcomes and streamline medical procedures.',
                'website' => 'https://www.healthcare-innovations.com',
                'email' => 'contact@healthcare-innovations.com',
                'phone' => '+1 (555) 345-6789',
                'address' => '321 Medical Center Drive, Chicago, IL 60601',
                'imageUrl' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
                'videoPath' => 'https://vimeo.com/824804225',
            ],
            [
                'name' => 'Creative Arts Alliance',
                'description' => 'The Creative Arts Alliance promotes artistic expression and cultural exchange. We support artists, organize exhibitions, and provide platforms for creative professionals to showcase their work.',
                'website' => 'https://www.creativeartsalliance.org',
                'email' => 'info@creativeartsalliance.org',
                'phone' => '+1 (555) 456-7890',
                'address' => '555 Art Street, New York, NY 10001',
                'imageUrl' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
                'videoPath' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ],
        ];

        $io->progressStart(count($partners));

        foreach ($partners as $partnerData) {
            $partner = new Partner();
            $partner->setName($partnerData['name']);
            $partner->setDescription($partnerData['description']);
            $partner->setWebsite($partnerData['website']);
            $partner->setEmail($partnerData['email']);
            $partner->setPhone($partnerData['phone']);
            $partner->setAddress($partnerData['address']);
            $partner->setVideoPath($partnerData['videoPath']);
            
            // Download and save image
            try {
                $response = $this->httpClient->request('GET', $partnerData['imageUrl']);
                $imageContent = $response->getContent();
                $imageName = strtolower(str_replace(' ', '-', $partnerData['name'])) . '.jpg';
                $imagePath = $this->uploadDir . '/' . $imageName;
                
                file_put_contents($imagePath, $imageContent);
                $partner->setImagePath($imageName);
            } catch (\Exception $e) {
                $io->warning("Failed to download image for {$partnerData['name']}: {$e->getMessage()}");
            }
            
            $this->entityManager->persist($partner);
            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->success('Successfully added ' . count($partners) . ' sample partners with images and website URLs.');

        return Command::SUCCESS;
    }
} 