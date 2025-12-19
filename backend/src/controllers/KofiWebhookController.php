<?php

declare(strict_types=1);

namespace App\Controllers;

use App\External\UserRepository;
use Throwable;

final class KofiWebhookController
{
    private string $verificationToken;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly \PDO $db
    ) {
        $this->verificationToken = $_ENV['KOFI_VERIFICATION_TOKEN'] ?? $_ENV['KO_FI_TOKEN'] ?? '';
    }

    public function handle(): void
    {
        try {
            $data = $_POST['data'] ?? null;
            if (!$data) {
                throw new \Exception('No data received');
            }

            $payment = json_decode($data, true);
            if (!$payment) {
                throw new \Exception('Invalid JSON data');
            }

            // Verify token
            if (($payment['verification_token'] ?? '') !== $this->verificationToken) {
                throw new \Exception('Invalid verification token');
            }

            $type = $payment['type'] ?? '';
            $email = $payment['email'] ?? '';

            // We only care about Subscription and Donation (which we can treat as one-off pro status for a month)
            if (($type === 'Subscription' || $type === 'Donation') && !empty($email)) {
                $this->upgradeUser($email);
            }

            $this->jsonResponse(['success' => true]);
        } catch (Throwable $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    private function upgradeUser(string $email): void
    {
        // Set expiry to 31 days from now
        $expiry = date('Y-m-d H:i:s', strtotime('+31 days'));
        
        $stmt = $this->db->prepare(
            "UPDATE users 
             SET subscription_tier = 'pro', 
                 subscription_expires_at = :expiry 
             WHERE email = :email"
        );
        $stmt->execute([
            'expiry' => $expiry,
            'email' => $email
        ]);
    }

    private function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
