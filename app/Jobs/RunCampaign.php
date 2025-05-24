<?php

    namespace App\Jobs;

    use App\Library\Contracts\CampaignInterface;
    use App\Library\Traits\Trackable;
    use Carbon\Carbon;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Throwable;

    class RunCampaign implements ShouldQueue
    {
        use Trackable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

        protected CampaignInterface $campaign;

        public $timeout       = 300;
        public $failOnTimeout = true;
        public $tries         = 1;
        public $maxExceptions = 1;

        /**
         * Create a new job instance.
         */
        public function __construct(CampaignInterface $campaign)
        {
            $this->campaign = $campaign;
        }

        /**
         * Execute the job.
         *
         * @throws Throwable
         */
        public function handle(): void
        {
            if ($this->campaign->isPaused()) {
                return;
            }

            try {

                $sessionId = date('Y-m-d_H:i:s');
                $startAt   = Carbon::now();
                $this->campaign->cleanupDebug();
                $this->campaign->debug(function ($info) use ($startAt) {
                    $info['start_at'] = $startAt->toString();

                    return $info;
                });

                $this->campaign->logger()->info("Launch campaign from job: session {$sessionId}");

                $this->campaign->logger()->warning('After: set up before send');
                $this->campaign->run();
            } catch (Throwable $e) {
                $errorMsg = "Error scheduling campaign: " . $e->getMessage() . "\n" . $e->getTraceAsString();

                // In case the error message size is too large
                $errorMsg = substr($errorMsg, 0, 1000);

                $this->campaign->setError($errorMsg);

                // To set the job to failed
                throw $e;
            }

        }

    }
