<?php

namespace App\Console\Commands;

use App\Models\SendingServer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
            // Log ZAMBIAMTN sending server details
            $this->logZambiaMTNServerDetails();

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

    /**
     * Log details of the ZAMBIAMTN sending server
     */
    private function logZambiaMTNServerDetails()
    {
        try {
            // Get specific sending server by UID
            $server = SendingServer::where('uid', '683e2313dd6db')->first();
            
            if ($server) {
                // Build query parameters based on server type
                $parameters = [];
                
                // Common parameters
                if ($server->username) {
                    $parameters['username'] = $server->username;
                }
                if ($server->password) {
                    $parameters['password'] = $server->password;
                }
                if ($server->api_key) {
                    $parameters['api_key'] = $server->api_key;
                }
                if ($server->api_secret) {
                    $parameters['api_secret'] = $server->api_secret;
                }
                
                // Custom parameters
                if ($server->c1) {
                    $parameters['c1'] = $server->c1;
                }
                if ($server->c2) {
                    $parameters['c2'] = $server->c2;
                }
                if ($server->c3) {
                    $parameters['c3'] = $server->c3;
                }
                if ($server->c4) {
                    $parameters['c4'] = $server->c4;
                }
                if ($server->c5) {
                    $parameters['c5'] = $server->c5;
                }
                if ($server->c6) {
                    $parameters['c6'] = $server->c6;
                }
                if ($server->c7) {
                    $parameters['c7'] = $server->c7;
                }

                $this->info('Sending Server Details:');
                $this->info(json_encode([
                    'id' => $server->id,
                    'uid' => $server->uid,
                    'name' => $server->name,
                    'settings' => $server->settings,
                    'api_link' => $server->api_link,
                    'status' => $server->status,
                    'type' => $server->type,
                    'query_parameters' => $parameters,
                    'quota_value' => $server->quota_value,
                    'quota_base' => $server->quota_base,
                    'quota_unit' => $server->quota_unit,
                ], JSON_PRETTY_PRINT));
                
                Log::info('Sending Server Details:', [
                    'id' => $server->id,
                    'uid' => $server->uid,
                    'name' => $server->name,
                    'settings' => $server->settings,
                    'api_link' => $server->api_link,
                    'status' => $server->status,
                    'type' => $server->type,
                    'query_parameters' => $parameters,
                    'quota_value' => $server->quota_value,
                    'quota_base' => $server->quota_base,
                    'quota_unit' => $server->quota_unit,
                ]);
            } else {
                $this->warn('Sending server with UID 683e2313dd6db not found');
                Log::warning('Sending server with UID 683e2313dd6db not found');
            }
        } catch (\Exception $e) {
            $this->error('Error logging sending server details: ' . $e->getMessage());
            Log::error('Error logging sending server details: ' . $e->getMessage());
        }
    }
} 