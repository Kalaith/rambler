<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class GeminiService
{
    private string $apiKey;
    private string $endpoint;

    public function __construct()
    {
        $this->apiKey = $_ENV['GEMINI_API_KEY'] ?? '';
        $this->endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

        if (empty($this->apiKey)) {
            throw new RuntimeException('GEMINI_API_KEY is not configured');
        }
    }

    public function generateStructuredExtraction(string $text): array
    {
        $prompt = "You are 'The Rambler' AI. Your goal is to extract useful kernels of knowledge from raw, unstructured rambling.
        Be gentle, neutral, and supportive. Do not judge or conclude; simply distill.
        Return a JSON object with:
        - summary: A short, gentle, and objective summary of the main points.
        - topics: An array of detected topics.
        - questions: An array of questions the user is asking themselves (gentle curiosity).
        - ideas: An array of specific ideas or flashes of insight.

        Text to process:
        $text";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json'
            ]
        ];

        $ch = curl_init($this->endpoint . '?key=' . $this->apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        // Handle SSL certificate issues on local dev
        if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("CURL Error ($errno): $error");
        }

        if ($httpCode !== 200) {
            throw new RuntimeException("Failed to communicate with Gemini API (HTTP $httpCode): " . ($response ?: 'Empty response'));
        }

        $result = json_decode($response, true);
        $content = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        
        return json_decode($content, true);
    }
}
