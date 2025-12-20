<?php

declare(strict_types=1);

namespace App\Actions;

use App\External\RambleRepository;

final class ListRamblesAction
{
    public function __construct(
        private readonly RambleRepository $rambleRepository
    ) {}

    public function execute(int $userId): array
    {
        $rambles = $this->rambleRepository->findByUserId($userId);

        foreach ($rambles as &$ramble) {
            if (isset($ramble['topics'])) {
                $ramble['topics'] = json_decode((string)$ramble['topics'], true) ?: [];
            }
            if (isset($ramble['questions'])) {
                $ramble['questions'] = json_decode((string)$ramble['questions'], true) ?: [];
            }
            if (isset($ramble['ideas'])) {
                $ramble['ideas'] = json_decode((string)$ramble['ideas'], true) ?: [];
            }
        }

        return $rambles;
    }
}
