<?php

declare(strict_types=1);

namespace App\Actions;

use App\External\RambleRepository;
use InvalidArgumentException;
use RuntimeException;

final class UpdateRambleAction
{
    public function __construct(
        private readonly RambleRepository $rambleRepository
    ) {}

    public function execute(int $userId, int $rambleId, string $content): void
    {
        if (empty(trim($content))) {
            throw new InvalidArgumentException('Content cannot be empty');
        }

        $wordCount = str_word_count($content);
        $success = $this->rambleRepository->update($rambleId, $userId, $content, $wordCount);

        if (!$success) {
            throw new RuntimeException("Failed to update ramble or ramble not found.");
        }
    }
}
