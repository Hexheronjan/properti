<?php

namespace App\Services;

use App\Models\SprDocument;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class EmateraiService
{
    protected $apiUrl;
    protected $apiKey;
    protected $secretKey;

    public function __construct()
    {
        $this->apiUrl = config('services.ematerai.api_url', 'https://api.ematerai.com/v1');
        $this->apiKey = config('services.ematerai.api_key');
        $this->secretKey = config('services.ematerai.secret_key');
    }

    /**
     * Stamp document with E-Materai
     */
    public function stampDocument(SprDocument $spr)
    {
        try {
            // Get the PDF file path
            $pdfPath = $spr->document_file;
            
            if (!$pdfPath || !Storage::exists($pdfPath)) {
                throw new \Exception('Document file not found');
            }

            // Read the PDF file
            $pdfContent = Storage::get($pdfPath);

            // Prepare the request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'multipart/form-data',
            ])->attach('document', $pdfContent, 'spr_document.pdf')
                ->post($this->apiUrl . '/stamp', [
                    'document_name' => 'SPR-' . $spr->spr_number . '.pdf',
                    'signer_name' => $spr->user->name,
                    'signer_email' => $spr->user->email,
                    'position' => [
                        'page' => 1,
                        'x' => 100,
                        'y' => 100,
                        'width' => 200,
                        'height' => 100,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();

                // Update SPR document with E-Materai data
                $spr->update([
                    'ematerai_status' => 'stamped',
                    'ematerai_id' => $data['id'] ?? null,
                    'ematerai_url' => $data['document_url'] ?? null,
                    'ematerai_qr_code' => $data['qr_code'] ?? null,
                ]);

                // Download the stamped document
                if (isset($data['document_url'])) {
                    $this->downloadStampedDocument($spr, $data['document_url']);
                }

                return true;
            }

            // Log error
            $spr->update([
                'ematerai_status' => 'failed',
            ]);

            throw new \Exception('Failed to stamp document: ' . $response->body());

        } catch (\Exception $e) {
            $spr->update([
                'ematerai_status' => 'failed',
            ]);
            
            throw $e;
        }
    }

    /**
     * Download stamped document
     */
    protected function downloadStampedDocument(SprDocument $spr, $documentUrl)
    {
        try {
            $response = Http::get($documentUrl);
            
            if ($response->successful()) {
                $fileName = 'spr_stamped_' . $spr->spr_number . '.pdf';
                $filePath = 'documents/spr/' . $fileName;
                
                Storage::put($filePath, $response->body());
                
                // Update document file path
                $spr->update([
                    'document_file' => $filePath,
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't throw - document is still stamped
            logger()->error('Failed to download stamped document: ' . $e->getMessage());
        }
    }

    /**
     * Check E-Materai status
     */
    public function checkStatus(SprDocument $spr)
    {
        if (!$spr->ematerai_id) {
            return ['status' => 'not_started'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->apiUrl . '/status/' . $spr->ematerai_id);

            if ($response->successful()) {
                return $response->json();
            }

            return ['status' => 'error', 'message' => $response->body()];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Verify E-Materai document
     */
    public function verifyDocument(SprDocument $spr)
    {
        if (!$spr->ematerai_id) {
            return ['valid' => false, 'message' => 'No E-Materai ID found'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->apiUrl . '/verify', [
                'document_id' => $spr->ematerai_id,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['valid' => false, 'message' => $response->body()];
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Mock stamping for development/testing
     */
    public function mockStampDocument(SprDocument $spr)
    {
        // Simulate E-Materai stamping
        $spr->update([
            'ematerai_status' => 'stamped',
            'ematerai_id' => 'MOCK-' . $spr->spr_number,
            'ematerai_url' => 'https://mock-ematerai.com/document/' . $spr->spr_number,
            'ematerai_qr_code' => 'https://mock-ematerai.com/qr/' . $spr->spr_number,
        ]);

        return true;
    }
}
