<?php

    namespace App\Jobs;

    use App\Library\SMSCounter;
    use App\Library\Tool;
    use App\Models\Campaigns;
    use App\Models\CsvData;
    use App\Models\CustomerBasedPricingPlan;
    use App\Models\FileCampaignData;
    use App\Models\PlansCoverageCountries;
    use Carbon\Carbon;
    use Exception;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Queue;
    use libphonenumber\NumberParseException;
    use libphonenumber\PhoneNumberUtil;
    use Maatwebsite\Excel\Facades\Excel;
    use Throwable;

    class ImportCampaign implements ShouldQueue
    {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

        protected Campaigns $campaign;

        protected CsvData $csvData;

        protected $db_fields;

        protected $plan_id;

        /**
         * Create a new job instance.
         */
        public function __construct(Campaigns $campaign, CsvData $csvData, $db_fields, $plan_id)
        {
            $this->campaign  = $campaign;
            $this->csvData   = $csvData;
            $this->db_fields = $db_fields;
            $this->plan_id   = $plan_id;
        }

        /**
         * Execute the job.
         *
         * @throws NumberParseException
         * @throws Throwable
         */
        public function handle(): void
        {
            $csvPath = storage_path($this->csvData->csv_data);

            // Read the CSV file and apply filtering within the sheet itself
            $collection = Excel::toCollection(null, $csvPath, null, null, true)->first();

            // Remove rows where all values are null directly within the collection
            $filteredCollection = $collection->reject(function ($row) {
                return empty(array_filter($row->toArray(), function ($value) {
                    return ! is_null($value);
                }));
            });

            // Extract campaign fields
            $campaign_fields = $collection->shift()->toArray();

            // Skip header rows
            $collection = $filteredCollection->skip($this->csvData->csv_header);

            // Get the total count
            $total = $collection->count();

            $sending_server = isset($this->campaign->sending_server_id) ? $this->campaign->sending_server_id : null;

            $this->campaign->cache = json_encode([
                'ContactCount'         => $total,
                'DeliveredCount'       => 0,
                'FailedDeliveredCount' => 0,
                'NotDeliveredCount'    => 0,
            ]);

            $this->campaign->update();

            Tool::resetMaxExecutionTime();

            $importData      = [];
            $check_sender_id = $this->campaign->getSenderIds();

            $failed_messages = [];

            $collection->chunk(1000)->each(function ($lines) use (&$importData, $check_sender_id, $campaign_fields, &$sending_server, &$failed_messages) {

                foreach ($lines as $line) {

                    $sender_id = count($check_sender_id) > 0 ? $this->campaign->pickSenderIds() : null;
                    $message   = null;
                    $line      = $line->toArray();

                    $data = array_combine($this->db_fields, $line);

                    if ($data['phone'] != null) {

                        $phone = str_replace(['+', '(', ')', '-', ' '], '', $data['phone']);

                        $sms_type  = $this->campaign->sms_type;
                        $sms_count = 1;

                        if (Tool::validatePhone($phone)) {
                            if ($this->campaign->message) {
                                $b           = array_map('trim', $line);
                                $modify_data = array_combine($campaign_fields, $b);
                                $message     = Tool::renderSMS($this->campaign->message, $modify_data);

                                $sms_counter  = new SMSCounter();
                                $message_data = $sms_counter->count($message, $sms_type == 'whatsapp' ? 'WHATSAPP' : null);
                                $sms_count    = $message_data->messages;
                            }

                            $phoneUtil         = PhoneNumberUtil::getInstance();
                            $phoneNumberObject = $phoneUtil->parse('+' . $phone);
                            $countryCode       = $phoneNumberObject->getCountryCode();
                            $isoCode           = $phoneUtil->getRegionCodeForNumber($phoneNumberObject);

                            if ($countryCode == null || $isoCode == null) {
                                $failed_messages[] = 'Invalid phone number: ' . $phone;
                                continue;
                            }

                            $coverage = CustomerBasedPricingPlan::where('user_id', $this->campaign->user_id)
                                ->whereHas('country', function ($query) use ($countryCode, $isoCode) {
                                    $query->where('country_code', $countryCode)
                                        ->where('iso_code', $isoCode)
                                        ->where('status', 1);
                                })
                                ->with('sendingServer')
                                ->first();

                            if ( ! $coverage) {
                                $coverage = PlansCoverageCountries::where(function ($query) use ($countryCode, $isoCode) {
                                    $query->whereHas('country', function ($query) use ($countryCode, $isoCode) {
                                        $query->where('country_code', $countryCode)
                                            ->where('iso_code', $isoCode)
                                            ->where('status', 1);
                                    })->where('plan_id', $this->plan_id);
                                })->with('sendingServer')->first();
                            }

                            if ( ! $coverage) {
                                $failed_messages[] = "Permission to send an SMS has not been enabled for the region indicated by the 'To' number: " . $phone;
                                continue;
                            }


                            $priceOption = json_decode($coverage->options, true);
                            if ($sending_server == null) {

                                // Define a map of $sms_type to sending server relationships
                                $smsTypeToServerMap = [
                                    'unicode'  => 'plain',
                                    'voice'    => 'voiceSendingServer',
                                    'mms'      => 'mmsSendingServer',
                                    'whatsapp' => 'whatsappSendingServer',
                                    'viber'    => 'viberSendingServer',
                                    'otp'      => 'otpSendingServer',
                                ];

                                // Set a default sending server in case the $sms_type is not found in the map
                                $defaultServer = 'sendingServer';
                                $db_sms_type   = $sms_type == 'unicode' ? 'plain' : $sms_type;

                                // Use the map to get the sending server or fallback to the default
                                $serverKey = $smsTypeToServerMap[$db_sms_type] ?? $defaultServer;

                                $sending_server = $coverage->{$serverKey}->id;
                            }

                            if ($sending_server == null) {
                                $failed_messages[] = "Sending server not found for phone number: " . $phone;
                                continue;
                            }

                            $cost = $sms_count * $this->campaign->getCost($priceOption);

                            $importData[] = [
                                'user_id'           => $this->campaign->user_id,
                                'sending_server_id' => $sending_server,
                                'campaign_id'       => $this->campaign->id,
                                'sender_id'         => $sender_id,
                                'phone'             => $phone,
                                'sms_type'          => $sms_type,
                                'sms_count'         => $sms_count,
                                'cost'              => $cost,
                                'message'           => $message,
                                'created_at'        => Carbon::now(),
                                'updated_at'        => Carbon::now(),
                            ];

                        }

                    }
                }

                FileCampaignData::insert($importData);
                $importData = [];

            });

// Trigger job exit and fail gracefully
            if (count($failed_messages) > 0) {
                Log::error('Campaign failed', ['campaign_id' => $this->campaign->id, 'errors' => $failed_messages]);
                $this->campaign->failed($failed_messages[0]);

                // Mark job as failed and stop the queue
                $this->fail(new Exception($failed_messages[0]));

                // Optionally stop the queue worker
                Queue::stopping(function () {
                    Log::info('Queue worker is stopping due to campaign failure.');
                });

                return; // Avoid using exit()
            }

            $this->campaign->execute();

        }

    }
