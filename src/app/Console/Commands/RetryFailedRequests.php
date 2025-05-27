<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FailedRequest;
use App\Jobs\ProcessFailedRequest;

class RetryFailedRequests extends Command
{
    protected $signature = 'failed-requests:retry';
    protected $description = 'Retry all failed requests with status pending or retrying';

    public function handle()
    {
        $failed = FailedRequest::whereIn('status', ['pending', 'retrying'])->get();

        foreach ($failed as $request) {
            dispatch(new ProcessFailedRequest($request));
            $this->info("Reenviando request ID {$request->id}");
        }
    }
}
