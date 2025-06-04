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

            // Update the custom_sending_servers table with new tokens
            if ($response->successful()) {
                $responseData = $response->json();
                
                // Update the custom_sending_servers table
                $updated = DB::table('custom_sending_servers')
                    ->where('id', 2)
                    ->where('server_id', 5)
                    ->update([
                        'username_value' => $responseData['access_token'],
                        'password_value' => $responseData['refresh_token'],
                        'updated_at' => now()
                    ]);

                if ($updated) {
                    $this->info('Successfully updated tokens in custom_sending_servers table for server_id 5');
                    Log::info('Successfully updated tokens in custom_sending_servers table for server_id 5');
                } else {
                    $this->warn('No matching record found in custom_sending_servers table with id=2 and server_id=5');
                    Log::warning('No matching record found in custom_sending_servers table with id=2 and server_id=5');
                }
            } else {
                $this->error('Failed to get new tokens from MTN Zambia API');
                Log::error('Failed to get new tokens from MTN Zambia API', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);
            }

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
            // Get all sending servers
            $servers = CustomSendingServers::find(2);
            
            $this->info('All Sending Servers:');
            $this->info(json_encode($servers, JSON_PRETTY_PRINT));
            
            Log::info('All Sending Servers:', [
                'servers' => $servers
            ]);

            // // Also get the specific ZAMBIAMTN server if it exists
            // $zambiaServer = $servers->where('name', 'ZAMBIAMTN')->first();
            
            // if ($zambiaServer) {
            //     $this->info('ZAMBIAMTN Server Details:');
            //     $this->info(json_encode($zambiaServer, JSON_PRETTY_PRINT));
                
            //     Log::info('ZAMBIAMTN Server Details:', [
            //         'server' => $zambiaServer
            //     ]);
            // } else {
            //     $this->warn('ZAMBIAMTN sending server not found');
            //     Log::warning('ZAMBIAMTN sending server not found');
            // }
        } catch (\Exception $e) {
            $this->error('Error logging sending server details: ' . $e->getMessage());
            Log::error('Error logging sending server details: ' . $e->getMessage());
        }
    }
} 