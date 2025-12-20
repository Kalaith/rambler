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
        private readonly \App\Actions\CaptureRambleAction $captureRambleAction,
        private readonly \App\Actions\ProcessRambleAction $processRambleAction,
        private readonly \App\Actions\UpdateRambleAction $updateRambleAction,
        private readonly \App\Actions\ListRamblesAction $listRamblesAction,
        private readonly \App\Actions\DeleteRambleAction $deleteRambleAction,
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

            $this->updateRambleAction->execute($userId, $rambleId, $content);

            $response->success(null, 'Ramble updated successfully');
        } catch (Throwable $e) {
            $response->error($e->getMessage(), 400);
        }
    }

    public function list(Request $request, Response $response): void
    {
        try {
            $userId = (int)$request->getAttribute('user_id', 0);
            $rambles = $this->listRamblesAction->execute($userId);

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

            $this->deleteRambleAction->execute($userId, $rambleId);

            $response->success(null, 'Ramble deleted successfully');
        } catch (Throwable $e) {
            $response->error($e->getMessage(), 400);
        }
    }

    public function getUsage(Request $request, Response $response): void
    {
        try {
            $userId = (int)$request->getAttribute('user_id', 0);
            $limitInfo = $this->rateLimiter->getLimitInfo($userId);
            $response->success($limitInfo);
        } catch (Throwable $e) {
            $response->error($e->getMessage(), 500);
        }
    }
}
