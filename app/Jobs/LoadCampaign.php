<?php

    namespace App\Jobs;

    use App\Library\Contracts\CampaignInterface;
    use App\Library\Exception\RateLimitExceeded;
    use App\Library\Traits\Trackable;
    use Carbon\Carbon;
    use Exception;
    use Illuminate\Bus\Batchable;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use function App\Helpers\plogger;

    class LoadCampaign implements ShouldQueue
    {
        use Trackable, Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

        public $timeout       = 86400;
        public $failOnTimeout = true;
        public $tries         = 1;
        public $maxExceptions = 1;

        protected CampaignInterface $campaign;
        protected                   $page;
        protected                   $listOfIds;

        /**
         * Create a new job instance.
         */
        public function __construct(CampaignInterface $campaign, int $page, array $listOfIds)
        {
            $this->campaign  = $campaign;
            $this->page      = $page;
            $this->listOfIds = $listOfIds;
        }

        /**
         * Execute the job.
         *
         * @throws Exception
         */
        public function handle(): void
        {
            if ($this->batch()->cancelled()) {
                return;
            }


            $plogger = plogger($this->campaign->uid);

            // Last update recording should go here
            // before any other tasks (to prevent IO blocking tasks)
            // In case we need to clean up pending jobs, at least we know that last job start time
            // Reduce the possibility of killing a newly started (and still running) job
            $plogger->info('LoadCampaign: getting campaign debug() lock...');
            $this->campaign->debug(function ($info) use ($plogger) {
                $plogger->info('LoadCampaign: got campaign debug() lock!');
                // Record last activity, no matter it is a successful delivery or exception
                // This information is useful when we want to audit delivery processes
                // i.e. when we can to automatically restart dead jobs for example
                $info['last_activity_at'] = Carbon::now()->toString();

                // Must return;
                return $info;
            });

            $count = 0;
            $total = sizeof($this->listOfIds);

            $this->campaign->logger()->info(sprintf('LoadCampaign: loading contacts for page %s (#%s)', $this->page, $total));
            $plogger->info(sprintf('LoadCampaign: loading contacts for page %s (#%s)', $this->page, $total));

            try {
                if ($this->campaign->upload_type == 'file') {
                    $this->campaign->loadBulkDeliveryJobsByIds(function (ShouldQueue $deliveryJob) use (&$count, $total, $plogger) {
                        $this->batch()->add($deliveryJob);

                        $count += 1;
                        $plogger->info(sprintf("LoadCampaign: job loaded %s/%s", $count, $total));
                        $this->campaign->logger()->info(sprintf("LoadCampaign: job loaded %s/%s", $count, $total));

                        $delayFlag = $this->campaign->checkDelayFlag();
                        if ($delayFlag) {
                            $plogger->info(sprintf("Rate limit hit! Quit loading jobs at %s/%s", $count, $total));
                            $this->campaign->logger()->info(sprintf("Rate limit hit! Quit loading jobs at %s/%s", $count, $total));

                            throw new RateLimitExceeded('Stop loading jobs');
                        }
                    }, $this->page, $this->listOfIds);
                } else {
                    $this->campaign->loadDeliveryJobsByIds(function (ShouldQueue $deliveryJob) use (&$count, $total, $plogger) {
                        $this->batch()->add($deliveryJob);

                        $count += 1;
                        $plogger->info(sprintf("LoadCampaign: job loaded %s/%s", $count, $total));
                        $this->campaign->logger()->info(sprintf("LoadCampaign: job loaded %s/%s", $count, $total));

                        $delayFlag = $this->campaign->checkDelayFlag();
                        if ($delayFlag) {
                            $plogger->info(sprintf("Rate limit hit! Quit loading jobs at %s/%s", $count, $total));
                            $this->campaign->logger()->info(sprintf("Rate limit hit! Quit loading jobs at %s/%s", $count, $total));

                            throw new RateLimitExceeded('Stop loading jobs');
                        }
                    }, $this->page, $this->listOfIds);
                }
                $plogger->info(sprintf("LoadCampaign: DONE loading all %s job(s)", $total));
            } catch (RateLimitExceeded $e) {
                // just do nothing
                $this->campaign->setError($e->getMessage());
                $plogger->info("Quit loading!");
            }

        }

    }
