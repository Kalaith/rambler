<?php

declare(strict_types=1);

namespace App\Actions;

use App\External\RambleRepository;
use RuntimeException;

final class DeleteRambleAction
{
    public function __construct(
        private readonly RambleRepository $rambleRepository
    ) {}

    public function execute(int $userId, int $rambleId): void
    {
        $success = $this->rambleRepository->delete($rambleId, $userId);

        if (!$success) {
            throw new RuntimeException("Failed to delete ramble or ramble not found.");
        }
    }
}
