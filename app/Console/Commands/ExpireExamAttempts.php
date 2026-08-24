<?php

namespace App\Console\Commands;

use App\Models\Attempt;
use Illuminate\Console\Command;

class ExpireExamAttempts extends Command
{
    protected $signature = 'attempts:expire';

    protected $description =
        'Close in-progress exam attempts that reached their deadline';

    public function handle(): int
    {
        $expiredCount = 0;

        Attempt::query()
            ->where('status', 'in_progress')
            ->where('expires_at', '<=', now())
            ->chunkById(
                100,
                function ($attempts) use (&$expiredCount) {
                    foreach ($attempts as $attempt) {
                        $attempt->update([
                            'status' => 'expired',
                            'submitted_at' => $attempt->expires_at,
                        ]);

                        $expiredCount++;
                    }
                },
            );

        $this->info("{$expiredCount} attempt(s) expired.");

        return self::SUCCESS;
    }
}
