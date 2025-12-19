<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Actions\CaptureRambleAction;
use App\Actions\ProcessRambleAction;
use App\External\RambleRepository;
use App\Middleware\JwtMiddleware;
use Throwable;

final class RambleController
{
    public function __construct(
        private readonly CaptureRambleAction $captureRambleAction,
        private readonly ProcessRambleAction $processRambleAction,
        private readonly RambleRepository $rambleRepository
    ) {}

    public function capture(): void
    {
        try {
            $userId = $this->authenticate();
            $data = $this->getParsedBody();
            
            $result = $this->captureRambleAction->execute(
                $userId,
                (string)($data['content'] ?? '')
            );

            $this->jsonResponse([
                'success' => true,
                'data' => $result
            ], 201);
        } catch (Throwable $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function process(array $args): void
    {
        try {
            $userId = $this->authenticate();
            $rambleId = (int)($args['id'] ?? 0);
            
            $result = $this->processRambleAction->execute($userId, $rambleId);

            $this->jsonResponse([
                'success' => true,
                'data' => $result
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function list(): void
    {
        try {
            $userId = $this->authenticate();
            $rambles = $this->rambleRepository->findByUserId($userId);

            // Decode JSON fields for each ramble if they exist
            foreach ($rambles as &$ramble) {
                if (isset($ramble['topics'])) {
                    $ramble['topics'] = json_decode($ramble['topics'], true) ?: [];
                }
                if (isset($ramble['questions'])) {
                    $ramble['questions'] = json_decode($ramble['questions'], true) ?: [];
                }
                if (isset($ramble['ideas'])) {
                    $ramble['ideas'] = json_decode($ramble['ideas'], true) ?: [];
                }
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $rambles
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function authenticate(): int
    {
        $middleware = new JwtMiddleware();
        return $middleware->authenticate();
    }

    private function getParsedBody(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? $_POST;
    }

    private function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
