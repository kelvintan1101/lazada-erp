<?php

namespace App\Console\Commands;

use App\Models\LazadaToken;
use App\Services\LazadaApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshLazadaToken extends Command
{
    protected $signature = 'lazada:refresh-token';
    protected $description = 'Refresh Lazada API access token';

    private $lazadaApiService;

    public function __construct(LazadaApiService $lazadaApiService)
    {
        parent::__construct();
        $this->lazadaApiService = $lazadaApiService;
    }

    public function handle()
    {
        $token = LazadaToken::latest()->first();

        if (!$token) {
            $this->error('No Lazada token found in the database.');
            Log::error('No Lazada token found in the database.');
            return Command::FAILURE;
        }

        if ($token->isExpiringSoon(7200)) { // 2 hours buffer
            $this->info('Refreshing Lazada token...');
            $success = $this->lazadaApiService->refreshToken();

            if ($success) {
                $this->info('Lazada token refreshed successfully.');
                Log::info('Lazada token refreshed successfully.');
                return Command::SUCCESS;
            } else {
                $this->error('Failed to refresh Lazada token.');
                Log::error('Failed to refresh Lazada token.');
                return Command::FAILURE;
            }
        }

        $this->info('Lazada token is still valid. No refresh needed.');
        return Command::SUCCESS;
    }
}