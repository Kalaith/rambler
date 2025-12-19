<?php

declare(strict_types=1);

namespace App\Actions;

use App\External\RambleRepository;
use App\External\ResultRepository;
use App\Services\GeminiService;
use InvalidArgumentException;
use RuntimeException;

final class ProcessRambleAction
{
    public function __construct(
        private readonly RambleRepository $rambleRepository,
        private readonly ResultRepository $resultRepository,
        private readonly GeminiService $geminiService
    ) {}

    public function execute(int $userId, int $rambleId): array
    {
        $ramble = $this->rambleRepository->findById($rambleId);

        if (!$ramble || (int)$ramble['user_id'] !== $userId) {
            throw new InvalidArgumentException('Ramble not found or access denied');
        }

        // Check if already processed
        $existing = $this->resultRepository->findByRambleId($rambleId);
        if ($existing) {
            return $existing;
        }

        // Process with AI
        $extraction = $this->geminiService->generateStructuredExtraction($ramble['content']);

        // Record results
        $this->resultRepository->create($rambleId, $extraction);

        return $extraction;
    }
}
