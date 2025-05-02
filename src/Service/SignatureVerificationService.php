<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Exception;
use Psr\Log\LoggerInterface;

class SignatureVerificationService
{
    private string $uploadDirectory;
    private LoggerInterface $logger;
    private string $originalContractsDirectory;

    public function __construct(
        string $uploadDirectory,
        LoggerInterface $logger,
        string $originalContractsDirectory
    ) {
        $this->uploadDirectory = $uploadDirectory;
        $this->logger = $logger;
        $this->originalContractsDirectory = $originalContractsDirectory;
    }

    public function verifySignature(string $filepath, int $partnershipId): bool
    {
        try {
            // Log the start of verification
            $this->logger->info('Starting contract verification', [
                'filepath' => $filepath,
                'partnershipId' => $partnershipId,
                'uploadDirectory' => $this->uploadDirectory,
                'originalContractsDirectory' => $this->originalContractsDirectory
            ]);

            // Basic file validation
            if (!file_exists($filepath) || !is_readable($filepath)) {
                $this->logger->error('File not found or not readable', ['filepath' => $filepath]);
                throw new Exception('Le fichier n\'existe pas ou n\'est pas accessible');
            }

            $fileSize = filesize($filepath);
            $this->logger->info('Uploaded file size', ['size' => $fileSize]);
            
            if ($fileSize < 10240 || $fileSize > 10485760) {
                $this->logger->error('File size out of range', ['size' => $fileSize]);
                throw new Exception('La taille du fichier n\'est pas dans la plage acceptable (10KB - 10MB)');
            }

            $mimeType = mime_content_type($filepath);
            $this->logger->info('File MIME type', ['mimeType' => $mimeType]);
            
            if ($mimeType !== 'application/pdf') {
                $this->logger->error('Invalid file type', ['mimeType' => $mimeType]);
                throw new Exception('Le fichier n\'est pas un PDF');
            }

            // Find the original contract
            $originalContractPattern = $this->originalContractsDirectory . '/contract_' . $partnershipId . '_*.pdf';
            $this->logger->info('Looking for original contract', [
                'pattern' => $originalContractPattern,
                'directory' => $this->originalContractsDirectory
            ]);
            
            $originalContracts = glob($originalContractPattern);
            $this->logger->info('Found original contracts', [
                'count' => count($originalContracts),
                'files' => $originalContracts,
                'pattern' => $originalContractPattern
            ]);
            
            if (empty($originalContracts)) {
                $this->logger->error('No original contract found', [
                    'partnershipId' => $partnershipId,
                    'pattern' => $originalContractPattern,
                    'directory' => $this->originalContractsDirectory
                ]);
                throw new Exception('Original contract not found for partnership ID: ' . $partnershipId);
            }

            // Get the most recent original contract
            $originalContract = $originalContracts[count($originalContracts) - 1];
            $originalSize = filesize($originalContract);
            
            $this->logger->info('Comparing file sizes', [
                'originalSize' => $originalSize,
                'uploadedSize' => $fileSize,
                'originalFile' => $originalContract,
                'uploadedFile' => $filepath,
                'sizeDifference' => $fileSize - $originalSize
            ]);

            // Compare sizes
            if ($fileSize > $originalSize) {
                $this->logger->info('Verification successful - Uploaded contract is larger than original', [
                    'originalSize' => $originalSize,
                    'uploadedSize' => $fileSize,
                    'difference' => $fileSize - $originalSize
                ]);
                return true;
            }

            // Log detailed size information
            $this->logger->error('Verification failed - Size comparison details', [
                'originalSize' => $originalSize,
                'uploadedSize' => $fileSize,
                'sizeDifference' => $fileSize - $originalSize,
                'originalFile' => $originalContract,
                'uploadedFile' => $filepath
            ]);

            throw new Exception(sprintf(
                'Contract verification failed. The uploaded file (%d bytes) must be larger than the original contract (%d bytes) to be considered signed.',
                $fileSize,
                $originalSize
            ));
        } catch (Exception $e) {
            $this->logger->error('Verification error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function saveContract(UploadedFile $file, int $partnershipId): string
    {
        try {
            if (!file_exists($this->uploadDirectory)) {
                if (!mkdir($this->uploadDirectory, 0777, true)) {
                    throw new Exception('Impossible de créer le répertoire de téléchargement');
                }
            }

            $filename = sprintf(
                'contract_%d_%s.%s',
                $partnershipId,
                uniqid(),
                $file->guessExtension()
            );

            $filepath = $this->uploadDirectory . '/' . $filename;
            $file->move($this->uploadDirectory, $filename);
            chmod($filepath, 0644);

            $this->logger->info('Contract saved successfully', [
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => filesize($filepath)
            ]);
            return $filename;
        } catch (Exception $e) {
            $this->logger->error('Failed to save contract', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('Échec de la sauvegarde du contrat: ' . $e->getMessage());
        }
    }
} 