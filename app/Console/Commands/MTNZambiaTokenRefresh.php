<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MTNZambiaTokenRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mtn:refresh-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh MTN Zambia CPASS messaging token every 5 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post('https://cpassmessaging.mtn.zm:32147/v1/accounts/users/login', [
                'email' => 'bantubonse.investment@gmail.com',
                'password' => 'Bantu@50102030'
            ]);

            $this->info('MTN Zambia Token Refresh Response:');
            $this->info(json_encode($response->json(), JSON_PRETTY_PRINT));
            
            Log::info('MTN Zambia Token Refresh Response:', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

        } catch (\Exception $e) {
            $this->error('Error refreshing MTN Zambia token: ' . $e->getMessage());
            Log::error('MTN Zambia Token Refresh Error: ' . $e->getMessage());
        }
    }
} 