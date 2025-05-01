<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use setasign\Fpdi\Fpdi;
use Exception;
use Psr\Log\LoggerInterface;

class SignatureVerificationService
{
    private string $uploadDirectory;
    private LoggerInterface $logger;

    public function __construct(string $uploadDirectory, LoggerInterface $logger)
    {
        $this->uploadDirectory = $uploadDirectory;
        $this->logger = $logger;
    }

    public function verifySignature(string $filepath): bool
    {
        try {
            // Check if file exists and is readable
            if (!file_exists($filepath) || !is_readable($filepath)) {
                throw new Exception('Le fichier n\'existe pas ou n\'est pas accessible');
            }

            // Check file size (between 10KB and 10MB)
            $fileSize = filesize($filepath);
            if ($fileSize < 10240 || $fileSize > 10485760) {
                throw new Exception('La taille du fichier n\'est pas dans la plage acceptable (10KB - 10MB)');
            }

            // Check if file is a PDF
            $mimeType = mime_content_type($filepath);
            if ($mimeType !== 'application/pdf') {
                throw new Exception('Le fichier n\'est pas un PDF');
            }

            // Initialize FPDI
            $pdf = new Fpdi();
            
            // Get number of pages
            $pageCount = $pdf->setSourceFile($filepath);
            
            if ($pageCount === 0) {
                throw new Exception('Le PDF est vide ou corrompu');
            }
            
            // For now, we'll check if the file is a valid PDF and can be opened
            // Note: FPDI/FPDF doesn't provide direct signature verification
            // For proper signature verification, you would need to use a library like
            // itext7-php or call an external service
            
            // Import at least one page to verify PDF structure
            try {
                $pdf->importPage(1);
                $this->logger->info('PDF structure verified successfully');
                return true;
            } catch (Exception $e) {
                $this->logger->error('Invalid PDF structure: ' . $e->getMessage());
                return false;
            }
        } catch (Exception $e) {
            $this->logger->error('Signature verification error: ' . $e->getMessage());
            return false;
        }
    }

    private function isValidSignature(array $signature): bool
    {
        // This method is kept for future implementation
        // when proper signature verification is added
        return true;
    }

    public function saveContract(UploadedFile $file, int $partnershipId): string
    {
        try {
            // Ensure upload directory exists
            if (!file_exists($this->uploadDirectory)) {
                if (!mkdir($this->uploadDirectory, 0777, true)) {
                    throw new Exception('Impossible de créer le répertoire de téléchargement');
                }
            }

            // Generate unique filename
            $filename = sprintf(
                'contract_%d_%s.%s',
                $partnershipId,
                uniqid(),
                $file->guessExtension()
            );

            $filepath = $this->uploadDirectory . '/' . $filename;

            // Move the file to the upload directory
            $file->move($this->uploadDirectory, $filename);

            // Set proper permissions
            chmod($filepath, 0644);

            $this->logger->info('Contract saved successfully: ' . $filename);

            return $filename;
        } catch (Exception $e) {
            $this->logger->error('Failed to save contract: ' . $e->getMessage());
            throw new Exception('Échec de la sauvegarde du contrat: ' . $e->getMessage());
        }
    }
} 