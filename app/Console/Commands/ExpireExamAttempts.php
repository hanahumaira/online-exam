<?php

namespace App\Console\Commands;

use App\Models\Attempt;
use App\Services\AttemptGradingService;
use Illuminate\Console\Command;

class ExpireExamAttempts extends Command
{
    protected $signature = 'attempts:expire';

    protected $description =
        'Close in-progress exam attempts that reached their deadline';

    public function handle(AttemptGradingService $gradingService): int
    {
        $expiredCount = 0;

        Attempt::query()
            ->where('status', 'in_progress')
            ->where('expires_at', '<=', now())
            ->chunkById(
                100,
                function ($attempts) use (&$expiredCount, $gradingService) {
                    foreach ($attempts as $attempt) {
                        $gradingService->finalizeAttempt(
                            $attempt,
                            'expired',
                        );

                        $expiredCount++;
                    }
                },
            );

        $this->info("{$expiredCount} attempt(s) expired.");

        return self::SUCCESS;
    }
}
