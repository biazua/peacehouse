<?php

    namespace App\Jobs;

    use App\Exceptions\CampaignPausedException;
    use App\Helpers\Helper;
    use App\Library\Exception\NoCreditsLeft;
    use App\Library\Exception\QuotaExceeded;
    use App\Library\Exception\RateLimitExceeded;
    use App\Models\Contacts;
    use Carbon\Carbon;
    use DateTime;
    use Exception;
    use Illuminate\Bus\Batchable;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Throwable;
    use function App\Helpers\execute_with_limits;
    use function App\Helpers\plogger;
    use Illuminate\Support\Facades\Log as LaravelLog;

    //use App\Library\QuotaManager;

    class SendMessage implements ShouldQueue
    {
        use Batchable;
        use Dispatchable;
        use InteractsWithQueue;
        use Queueable;
        use SerializesModels;

        public int $timeout = 900;

        protected $contact;
        protected $campaign;
        protected $server;
        protected $priceOption;
        protected $triggerId;
        protected $stopOnError = false;

        /**
         * Create a new job instance.
         */
        public function __construct($campaign, Contacts $contact, $server, $priceOption, $triggerId = null)
        {
            $this->campaign    = $campaign;
            $this->contact     = $contact;
            $this->server      = $server;
            $this->priceOption = $priceOption;
            $this->triggerId   = $triggerId;
        }

        /**
         * @throws Exception
         */
        public function setStopOnError($value): void
        {
            if ( ! is_bool($value)) {
                throw new Exception('Parameter passed to setStopOnError must be bool');
            }

            $this->stopOnError = $value;
        }


        /**
         * Determine the time at which the job should timeout.
         *
         * @return DateTime
         */
        public function retryUntil(): DateTime
        {
            return now()->addDays(30);
        }


        /**
         * @throws QuotaExceeded
         */
        public function handle(): void
        {
            if ($this->batch() && $this->batch()->cancelled()) {
                return;
            }

            $this->campaign->debug(function ($info) {
                // Record last activity, no matter it is a successful delivery or exception
                // This information is useful when we want to audit delivery processes
                // i.e. when we can to automatically restart dead jobs for example
                $info['last_activity_at'] = Carbon::now()->toString();

                // Must return;
                return $info;
            });


            $this->send();

        }

        /**
         * @throws QuotaExceeded
         * @throws Exception
         */
        public function send($exceptionCallback = null)
        {
            $subscription = $this->campaign->user->customer->getCurrentSubscription();

            // debug
            $startAt = Carbon::now()->getTimestampMs();

            $logger  = $this->campaign->logger();
            $plogger = plogger($this->campaign->uid);
            $phone   = $this->contact->phone;

            $logger->info(sprintf('Sending to %s [Server "%s"]', $phone, $this->server->name));
            $plogger->info(sprintf('Sending to %s [Server "%s"]', $phone, $this->server->name));


            // Rate limit trackers
            // Here we have 2 rate trackers
            // 1. Sending server sending rate tracker with 1 or more limits.
            // 2. Subscription (plan) sending speed limits with 1 or more limits.
            $rateTrackers = [
                $this->server->getRateLimitTracker(),
            ];

            if ( ! is_null($subscription)) {

                $rateTrackers[] = $subscription->getSendSMSRateTracker();
            }

            try {

                if ($this->campaign->user->sms_unit != '-1' && $this->campaign->user->sms_unit == 0) {
                    throw new CampaignPausedException(sprintf("Campaign `%s` (%s) halted, customer exceeds sms balance", $this->campaign->campaign_name, $this->campaign->uid));
                }


                // DEBUG
                $finishPreparingAt = Carbon::now()->getTimestampMs();
                // END DEBUG

                $startGettingLock = Carbon::now()->getTimestampMs();


                execute_with_limits($rateTrackers, function () use ($startAt, $logger, $plogger, $startGettingLock, $phone) {

                    $getLockAt       = Carbon::now()->getTimestampMs();
                    $getLockDiff     = ($getLockAt - $startAt) / 1000;
                    $lockWaitingTime = ($getLockAt - $startGettingLock) / 1000;

                    $logger->info(sprintf('Got lock for %s after "%s" seconds (lock waiting time %s)', $phone, $getLockDiff, $lockWaitingTime));
                    $plogger->info(sprintf('Got lock for %s after "%s" seconds (lock waiting time %s)', $phone, $getLockDiff, $lockWaitingTime));


                    $sent = $this->campaign->send($this->contact, $this->priceOption, $this->server);
                    $this->campaign->track_message($sent, $this->contact, $this->server);

                    $logger->info(sprintf('Sent to %s', $phone));
                    $plogger->info(sprintf('Sent to %s', $phone));


                    // Done, written to tracking_logs table
                    $logger->info(sprintf('Done with %s [Server "%s"]', $phone, $this->server->name));
                    $plogger->info(sprintf('Done with %s [Server "%s"]', $phone, $this->server->name));
                });

                // Debug
                $now      = Carbon::now(); // OK DONE ALL
                $finishAt = $now->getTimestampMs();

                $this->campaign->debug(function ($info) use ($startAt, $now, $finishAt, $finishPreparingAt) {
                    $getLockAt        = null;
                    $finishDeliveryAt = null;
                    $diff             = ($finishAt - $startAt) / 1000;
                    $avg              = $info['send_message_avg_time'];
                    if (is_null($avg)) {
                        $info['send_message_avg_time'] = $diff;
                    } else {
                        $info['send_message_avg_time'] = ($avg * $info['send_message_count'] + $diff) / ($info['send_message_count'] + 1);
                    }

                    $prepareDiff = ($finishPreparingAt - $startAt) / 1000;
                    $prepareAvg  = $info['send_message_prepare_avg_time'] ?? null;
                    if (is_null($prepareAvg)) {
                        $info['send_message_prepare_avg_time'] = $prepareDiff;
                    } else {
                        $info['send_message_prepare_avg_time'] = ($prepareAvg * $info['send_message_count'] + $prepareDiff) / ($info['send_message_count'] + 1);
                    }

                    $getLockDiff = ($getLockAt - $startAt) / 1000;
                    $getLockAvg  = $info['send_message_lock_avg_time'] ?? null;
                    if (is_null($getLockAvg)) {
                        $info['send_message_lock_avg_time'] = $getLockDiff;
                    } else {
                        $info['send_message_lock_avg_time'] = ($getLockAvg * $info['send_message_count'] + $getLockDiff) / ($info['send_message_count'] + 1);
                    }

                    $deliveryDiff = ($finishDeliveryAt - $startAt) / 1000;
                    $deliveryAvg  = $info['send_message_delivery_avg_time'] ?? null;
                    if (is_null($deliveryAvg)) {
                        $info['send_message_delivery_avg_time'] = $deliveryDiff;
                    } else {
                        $info['send_message_delivery_avg_time'] = ($deliveryAvg * $info['send_message_count'] + $deliveryDiff) / ($info['send_message_count'] + 1);
                    }

                    // COUNT MESSAGE. IMPORTANT: it must go after the other calculation
                    $info['send_message_count'] = $info['send_message_count'] + 1;

                    if (is_null($info['send_message_min_time']) || $diff < $info['send_message_min_time']) {
                        $info['send_message_min_time'] = $diff;
                    }

                    if (is_null($info['send_message_max_time']) || $diff > $info['send_message_max_time']) {
                        $info['send_message_max_time'] = $diff;
                    }

                    $info['last_message_sent_at'] = $now->toString();
                    $campaignStartAt              = $info['start_at'];
                    $timeSinceCampaignStart       = $now->diffInSeconds(Carbon::parse($campaignStartAt));

                    // In case it is too fast, avoid DivisionByZero
                    $info['total_time']               = ($timeSinceCampaignStart == 0) ? 1 : $timeSinceCampaignStart;
                    $info['messages_sent_per_second'] = $info['send_message_count'] / $info['total_time'];

                    // Info
                    $info['delay_note'] = null;

                    return $info;
                });
            } catch (RateLimitExceeded $ex) {
                if ( ! is_null($exceptionCallback)) {
                    return $exceptionCallback($ex);
                }

                if ($this->batch()) {
                    $lockKey = "campaign-delay-flag-lock-{$this->campaign->uid}";
                    Helper::with_cache_lock($lockKey, function () use ($rateTrackers, $logger, $plogger, $phone, $ex) {
                        $delayFlag = $this->campaign->checkDelayFlag();

                        if ($delayFlag) {
                            // just finish the task
                            $logger->info(sprintf("Delayed [%s] due to rate limit: %s", $phone, $ex->getMessage()));
                            $plogger->warning(sprintf("Delayed [%s] due to rate limit: %s", $phone, $ex->getMessage()));

                            return true;
                        } else {
                            // Release the job, have it tried again later on, after 1 minutes

                            $delayInSeconds = 60; // reservation stately, so 60 seconds is good enough

                            $logger->warning(sprintf("Delay [%s], dispatch WAITING job (%s seconds): %s", $phone, $delayInSeconds, $ex->getMessage()));
                            $plogger->warning(sprintf("Delay [%s], dispatch WAITING job (%s seconds): %s", $phone, $delayInSeconds, $ex->getMessage()));

                            // set delay flag to true
                            $this->campaign->setDelayFlag(true);
                            $delay = new Delay($delayInSeconds, $this->campaign, $rateTrackers);
                            $this->batch()->add($delay);

                            $this->campaign->debug(function ($info) use ($ex) {
                                // @todo: consider making it an interface, rather than access the .delay_note attribute directly like this
                                $info['delay_note'] = sprintf("Speed limit hit: %s", $ex->getMessage());

                                // Must return;
                                return $info;
                            });
                        }

                        return '';
                    });

                } else {
                    $logger->warning(sprintf("Delay [%s] for 60 seconds (no batch): %s", $phone, $ex->getMessage()));
                    $plogger->warning(sprintf("Delay [%s] for 60 seconds (no batch): %s", $phone, $ex->getMessage()));
                    $this->release(600); // should be only 60 seconds
                }
            } catch (Throwable|CampaignPausedException $ex) {
                if ( ! is_null($exceptionCallback)) {
                    return $exceptionCallback($ex);
                }

                $message = sprintf("Error sending to [%s]. Error: %s", $phone, $ex->getMessage());
                LaravelLog::error('ERROR SENDING EMAIL (debug): ' . $ex->getTraceAsString());
                $logger->error($message);
                $plogger->error($message);

                // In case of these exceptions, stop campaign immediately even if stopOnError is currently false
                // This is helpful in certain cases: for example, when credits runs out, then it does not make sense to keep sending (and failing)
                $forceEndCampaignExceptions = [
                    NoCreditsLeft::class,
                    // Other "end-game" exception like "SendingServer out of credits, etc."
                ];

                $forceEndCampaign = in_array(get_class($ex), $forceEndCampaignExceptions);

                // There are 2 options here
                // Option 1: throw an exception and show it to users as the campaign status
                //     throw new Exception($message);
                // Option 2: just skip the error, log it and proceed with the next subscriber
                if ($this->stopOnError || $forceEndCampaign) {
                    $this->campaign->pause($message);
                    $this->batch()->cancel();
                } else {
                    $params = [
                        'message_id' => null,
                        'status'     => 'Failed',
                        'sms_count'  => 1,
                        'cost'       => 0,
                    ];

                    $sent = json_decode(json_encode($params));

                    $this->campaign->track_message($sent, $this->contact, $this->server);
                }
            } finally {
                //
            }

            $plogger->info('SendMessage: ALL done');

            return true;
        }

    }
