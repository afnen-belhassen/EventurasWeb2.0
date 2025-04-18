<?php

namespace App\Service;

use App\Entity\Partnership;
use App\Entity\Partner;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class ContractGeneratorService
{
    private $uploadDir;
    private $projectDir;
    private $logger;

    public function __construct(
        ParameterBagInterface $params,
        LoggerInterface $logger
    )
    {
        $this->projectDir = $params->get('kernel.project_dir');
        $this->uploadDir = $this->projectDir . '/public/uploads/organizer/contracts/';
        $this->logger = $logger;
    }

    /**
     * Generate a PDF contract for a partnership
     * 
     * @param Partnership $partnership The partnership entity
     * @param Partner $partner The partner entity
     * @return string The filename of the generated contract
     */
    public function generateContract(Partnership $partnership, Partner $partner): string
    {
        $this->logger->info('Generating contract for partnership ID: ' . $partnership->getId());
        
        // Create the directory if it doesn't exist
        if (!file_exists($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0777, true)) {
                $this->logger->error('Failed to create upload directory: ' . $this->uploadDir);
                throw new \RuntimeException('Failed to create upload directory');
            }
        }

        // Generate a unique filename
        $filename = 'contract_' . $partnership->getId() . '_' . uniqid() . '.pdf';
        $filepath = $this->uploadDir . $filename;

        // Create HTML content for the contract
        $html = $this->generateContractHtml($partnership, $partner);

        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('tempDir', sys_get_temp_dir());

        // Create Dompdf instance
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Save the PDF
        if (file_put_contents($filepath, $dompdf->output()) === false) {
            $this->logger->error('Failed to save contract to: ' . $filepath);
            throw new \RuntimeException('Failed to save contract file');
        }
        
        $this->logger->info('Successfully generated contract: ' . $filename);
        return $filename;
    }

    /**
     * Generate HTML content for the contract
     * 
     * @param Partnership $partnership The partnership entity
     * @param Partner $partner The partner entity
     * @return string The HTML content
     */
    private function generateContractHtml(Partnership $partnership, Partner $partner): string
    {
        $date = new \DateTime();
        $formattedDate = $date->format('F j, Y');

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Partnership Contract</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    margin: 40px;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                }
                .title {
                    font-size: 24px;
                    font-weight: bold;
                    margin-bottom: 20px;
                }
                .section {
                    margin-bottom: 20px;
                }
                .signature-section {
                    margin-top: 50px;
                }
                .signature-line {
                    border-top: 1px solid #000;
                    width: 200px;
                    margin-top: 50px;
                }
                .footer {
                    margin-top: 50px;
                    font-size: 12px;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="title">PARTNERSHIP AGREEMENT</div>
                <div>Date: {$formattedDate}</div>
            </div>
            
            <div class="section">
                <p>This Partnership Agreement (the "Agreement") is entered into as of {$formattedDate} by and between:</p>
                <p><strong>Eventuras</strong> ("Organizer")</p>
                <p>and</p>
                <p><strong>{$partner->getName()}</strong> ("Partner")</p>
            </div>
            
            <div class="section">
                <h3>1. Partnership Details</h3>
                <p><strong>Partner Name:</strong> {$partner->getName()}</p>
                <p><strong>Contract Type:</strong> {$partnership->getContracttype()}</p>
                <p><strong>Description:</strong> {$partnership->getDescription()}</p>
            </div>
            
            <div class="section">
                <h3>2. Terms and Conditions</h3>
                <p>This is a standard partnership agreement. The terms and conditions of this partnership include but are not limited to:</p>
                <ul>
                    <li>Both parties agree to collaborate in good faith</li>
                    <li>The partnership is non-exclusive</li>
                    <li>This agreement is valid for one year from the date of signing</li>
                    <li>Either party may terminate this agreement with 30 days written notice</li>
                </ul>
            </div>
            
            <div class="signature-section">
                <p><strong>For and on behalf of Eventuras:</strong></p>
                <div class="signature-line"></div>
                <p>Authorized Signature</p>
                <p>Date: _________________</p>
                
                <p style="margin-top: 30px;"><strong>For and on behalf of {$partner->getName()}:</strong></p>
                <div class="signature-line"></div>
                <p>Authorized Signature</p>
                <p>Date: _________________</p>
            </div>
            
            <div class="footer">
                <p>This document was generated by Eventuras on {$formattedDate}</p>
                <p>Partnership ID: {$partnership->getId()}</p>
            </div>
        </body>
        </html>
        HTML;
    }
} 