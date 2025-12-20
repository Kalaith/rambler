<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Actions\CaptureRambleAction;
use App\Actions\ProcessRambleAction;
use App\External\RambleRepository;
use App\Core\Request;
use App\Core\Response;
use App\Services\RateLimiter;
use Throwable;

final class RambleController
{
    public function __construct(
        private readonly CaptureRambleAction $captureRambleAction,
        private readonly ProcessRambleAction $processRambleAction,
        private readonly RambleRepository $rambleRepository,
        private readonly RateLimiter $rateLimiter
    ) {}

    public function capture(Request $request, Response $response): void
    {
        try {
            $userId = (int)$request->getAttribute('user_id', 0);
            
            $result = $this->captureRambleAction->execute(
                $userId,
                (string)$request->get('content', '')
            );

            $response->withStatus(201)->success($result);
        } catch (Throwable $e) {
            $response->error($e->getMessage(), 400);
        }
    }

    public function process(Request $request, Response $response): void
    {
        try {
            $userId = (int)$request->getAttribute('user_id', 0);
            
            if (!$this->rateLimiter->canProcess($userId)) {
                $limitInfo = $this->rateLimiter->getLimitInfo($userId);
                throw new \Exception("Daily processing limit reached ({$limitInfo['limit']}). Please try again tomorrow.");
            }

            $rambleId = (int)$request->getParam('id', 0);
            
            $result = $this->processRambleAction->execute($userId, $rambleId);

            $response->success($result);
        } catch (Throwable $e) {
            $response->error($e->getMessage(), 400);
        }
    }

    public function update(Request $request, Response $response): void
    {
        try {
            $userId = (int)$request->getAttribute('user_id', 0);
            $rambleId = (int)$request->getParam('id', 0);
            $content = (string)$request->get('content', '');
            $wordCount = str_word_count($content);

            $success = $this->rambleRepository->update($rambleId, $userId, $content, $wordCount);

            if (!$success) {
                throw new \Exception("Failed to update ramble or ramble not found.");
            }

            $response->success(null, 'Ramble updated successfully');
        } catch (Throwable $e) {
            $response->error($e->getMessage(), 400);
        }
    }

    public function list(Request $request, Response $response): void
    {
        try {
            $userId = (int)$request->getAttribute('user_id', 0);
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

            $response->success($rambles);
        } catch (Throwable $e) {
            $response->error($e->getMessage(), 500);
        }
    }

    public function delete(Request $request, Response $response): void
    {
        try {
            $userId = (int)$request->getAttribute('user_id', 0);
            $rambleId = (int)$request->getParam('id', 0);

            $success = $this->rambleRepository->delete($rambleId, $userId);

            if (!$success) {
                throw new \Exception("Failed to delete ramble or ramble not found.");
            }

            $response->success(null, 'Ramble deleted successfully');
        } catch (Throwable $e) {
            $response->error($e->getMessage(), 400);
        }
    }
}
