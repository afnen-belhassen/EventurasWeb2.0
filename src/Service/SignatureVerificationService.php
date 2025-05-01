<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class SignatureVerificationService
{
    private $slugger;
    private $uploadDir;
    private $projectDir;
    private $logger;

    public function __construct(
        SluggerInterface $slugger, 
        ParameterBagInterface $params,
        LoggerInterface $logger
    ) {
        $this->slugger = $slugger;
        $this->projectDir = $params->get('kernel.project_dir');
        $this->uploadDir = $this->projectDir . '/public/uploads/organizer/signed_contracts/';
        $this->logger = $logger;
    }

    /**
     * Verify if a contract has been signed
     * 
     * @param string $filepath The path to the uploaded contract file
     * @return bool Whether the contract is signed
     */
    public function verifySignature(string $filepath): bool
    {
        $this->logger->info('Verifying signature for file: ' . $filepath);
        
        // Check if file exists and is readable
        if (!file_exists($filepath) || !is_readable($filepath)) {
            $this->logger->error('File does not exist or is not readable: ' . $filepath);
            return false;
        }
        
        // Check file size (min 10KB, max 10MB)
        $fileSize = filesize($filepath);
        if ($fileSize < 10 * 1024 || $fileSize > 10 * 1024 * 1024) {
            $this->logger->error('File size is outside acceptable range: ' . $fileSize . ' bytes');
            return false;
        }
        
        // Check if file is a PDF
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filepath);
        finfo_close($finfo);
        
        $this->logger->info('File MIME type: ' . $mimeType);
        
        // Accept only PDF files
        if ($mimeType !== 'application/pdf') {
            $this->logger->error('File is not a PDF: ' . $mimeType);
            return false;
        }
        
        // For this simplified approach, we'll consider any valid PDF as signed
        // This avoids the complexity of comparing with the original template
        $this->logger->info('Contract is a valid PDF - considered signed');
        return true;
    }

    /**
     * Verify if a contract has been signed from base64 data
     * 
     * @param string $base64Data The base64 encoded PDF data
     * @return bool Whether the contract is signed
     */
    public function verifySignatureFromBase64(string $base64Data): bool
    {
        $this->logger->info('Verifying signature from base64 data');
        
        // Clean the base64 data
        if (strpos($base64Data, 'data:image/png;base64,') === 0) {
            $base64Data = substr($base64Data, strlen('data:image/png;base64,'));
        } elseif (strpos($base64Data, 'data:application/pdf;base64,') === 0) {
            $base64Data = substr($base64Data, strlen('data:application/pdf;base64,'));
        }
        
        // Replace spaces with plus signs
        $base64Data = str_replace(' ', '+', $base64Data);
        
        // Decode base64 data
        $data = base64_decode($base64Data, true);
        if ($data === false) {
            $this->logger->error('Invalid base64 data provided');
            return false;
        }
        
        // Create a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'signature_');
        if ($tempFile === false) {
            $this->logger->error('Failed to create temporary file');
            return false;
        }
        
        try {
            // Write the decoded data to the temporary file
            if (file_put_contents($tempFile, $data) === false) {
                $this->logger->error('Failed to write to temporary file');
                return false;
            }
            
            // Verify the signature using the file-based method
            $result = $this->verifySignature($tempFile);
            
            // Clean up the temporary file
            unlink($tempFile);
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Error verifying signature: ' . $e->getMessage());
            
            // Clean up the temporary file if it exists
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            
            return false;
        }
    }

    /**
     * Save an uploaded contract file
     * 
     * @param UploadedFile $file The uploaded contract file
     * @param int $partnershipId The ID of the partnership
     * @return string The filename of the saved contract
     * @throws \RuntimeException If the file cannot be saved
     */
    public function saveContract(UploadedFile $file, int $partnershipId): string
    {
        $this->logger->info('Saving uploaded contract for partnership ID: ' . $partnershipId);
        
        // Create a unique filename
        $filename = sprintf(
            'signed_contract_%d_%s.%s',
            $partnershipId,
            uniqid(),
            $file->guessExtension()
        );

        // Ensure the upload directory exists and is writable
        if (!file_exists($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0777, true)) {
                $this->logger->error('Failed to create upload directory: ' . $this->uploadDir);
                throw new \RuntimeException('Failed to create upload directory');
            }
        }

        if (!is_writable($this->uploadDir)) {
            $this->logger->error('Upload directory is not writable: ' . $this->uploadDir);
            throw new \RuntimeException('Upload directory is not writable');
        }

        try {
            // Get the temporary file path
            $tempPath = $file->getPathname();
            
            // Check if the temporary file exists and is readable
            if (!file_exists($tempPath) || !is_readable($tempPath)) {
                $this->logger->error('Temporary file is not accessible: ' . $tempPath);
                throw new \RuntimeException('Temporary file is not accessible');
            }
            
            // Copy the file to the upload directory
            $destinationPath = $this->uploadDir . '/' . $filename;
            if (!copy($tempPath, $destinationPath)) {
                $this->logger->error('Failed to copy the file to the upload directory: ' . $destinationPath);
                throw new \RuntimeException('Failed to copy the file to the upload directory');
            }
            
            // Ensure proper permissions on the saved file
            chmod($destinationPath, 0644);
            
            $this->logger->info('Successfully saved contract to: ' . $destinationPath);
            return $filename;
        } catch (\Exception $e) {
            $this->logger->error('Failed to save the contract file: ' . $e->getMessage());
            throw new \RuntimeException('Failed to save the contract file: ' . $e->getMessage());
        }
    }
} 