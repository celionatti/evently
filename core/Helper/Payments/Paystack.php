<?php

declare(strict_types=1);

namespace Trees\Helper\Payments;

use Exception;

/**
 * =========================================
 * *****************************************
 * ========== Trees Paystack Class =========
 * *****************************************
 * =========================================
 */

class Paystack
{
    private string $secretKey;
    private string $publicKey;
    private string $baseUrl = "https://api.paystack.co";

    public function __construct(string $secretKey, string $publicKey)
    {
        $this->secretKey = $secretKey;
        $this->publicKey = $publicKey;
    }

    /**
     * Initialize a Paystack payment transaction
     *
     * @param float $amount The amount to charge (in the currency's base unit, e.g. Naira)
     * @param string $email Customer's email address
     * @param string $reference Unique transaction reference
     * @param string $callbackUrl URL to redirect to after payment
     * @return array
     * @throws Exception
     */
    public function initializePayment(float $amount, string $email, string $reference, string $callbackUrl): array
    {
        $amountInKobo = $amount * 100; // Convert to kobo

        $url = $this->baseUrl . '/transaction/initialize';
        $fields = [
            'email' => $email,
            'amount' => $amountInKobo,
            'reference' => $reference,
            'callback_url' => $callbackUrl
        ];

        $response = $this->makeRequest($url, 'POST', $fields);

        if (!$response['status']) {
            throw new Exception('Payment initialization failed: ' . ($response['message'] ?? 'Unknown error'));
        }

        return $response['data'];
    }

    /**
     * Verify a Paystack payment transaction
     *
     * @param string $reference Transaction reference to verify
     * @return array
     * @throws Exception
     */
    public function verifyPayment(string $reference): array
    {
        $url = $this->baseUrl . '/transaction/verify/' . rawurlencode($reference);

        $response = $this->makeRequest($url, 'GET');

        if (!$response['status']) {
            throw new Exception('Payment verification failed: ' . ($response['message'] ?? 'Unknown error'));
        }

        return $response['data'];
    }

    /**
     * Make HTTP request to Paystack API
     *
     * @param string $url API endpoint
     * @param string $method HTTP method (GET, POST)
     * @param array $data Request payload for POST requests
     * @return array
     * @throws Exception
     */
    private function makeRequest(string $url, string $method = 'GET', array $data = []): array
    {
        $headers = [
            'Authorization: Bearer ' . $this->secretKey,
            'Cache-Control: no-cache',
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('CURL Error: ' . $error);
        }

        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from Paystack');
        }

        return $result;
    }

    /**
     * Get the public key
     *
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }
}