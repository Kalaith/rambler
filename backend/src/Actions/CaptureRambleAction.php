<?php

declare(strict_types=1);

namespace App\Actions;

use App\External\RambleRepository;
use InvalidArgumentException;

final class CaptureRambleAction
{
    public function __construct(
        private readonly RambleRepository $rambleRepository
    ) {}

    public function execute(int $userId, string $content): array
    {
        if (empty(trim($content))) {
            throw new InvalidArgumentException('Content cannot be empty');
        }

        $rambleId = $this->rambleRepository->create($userId, $content, $wordCount);
        $ramble = $this->rambleRepository->findById($rambleId, $userId);
        if (!$ramble) {
            throw new \RuntimeException('Failed to retrieve captured ramble');
        }

        return $ramble;
    }
}
