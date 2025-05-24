<?php

    namespace App\Http\Controllers\User;

    use App\Http\Controllers\Controller;
    use App\Models\Reports;
    use App\Repositories\Contracts\UserRepository;
    use ArielMejiaDev\LarapexCharts\LarapexChart;
    use Carbon\Carbon;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\View\Factory;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use Illuminate\View\View;

    class UserController extends Controller
    {
        protected UserRepository $users;

        /**
         * UserController constructor.
         */
        public function __construct(UserRepository $users)
        {
            $this->users = $users;
        }

        /**
         * Show user homepage.
         *
         * @return Application|Factory|\Illuminate\Contracts\View\View|View
         */
        public function index()
        {

            $breadcrumbs = [
                ['link' => url('dashboard'), 'name' => __('locale.menu.Dashboard')],
                ['name' => Auth::user()->displayName()],
            ];


            $reportsCounts = Reports::where('user_id', Auth::user()->id);

            $total_sms_sent   = $reportsCounts->count();
            $deliveredCount   = $reportsCounts->where('status', 'like', '%Delivered%')->count();
            $undeliveredCount = $total_sms_sent - $deliveredCount;

            // Create the chart with the calculated counts
            $sms_history = (new LarapexChart)->pieChart()
                ->addData([$deliveredCount, $undeliveredCount]);


            $currentMonthReports = Reports::where('user_id', Auth::user()->id)->whereIn('sms_type', ['plain', 'unicode', 'mms', 'voice', 'viber', 'whatsapp', 'otp'])
                ->currentMonth()
                ->select(
                    DB::raw('DAY(created_at) as day'),
                    DB::raw('SUM(CASE WHEN send_by = "from" AND sms_type = "plain"  THEN 1 ELSE 0 END) as outgoing_plain_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "to" AND sms_type = "plain"  THEN 1 ELSE 0 END) as incoming_plain_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "api" AND sms_type = "plain"  THEN 1 ELSE 0 END) as api_plain_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "from" AND sms_type = "unicode"  THEN 1 ELSE 0 END) as outgoing_unicode_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "to" AND sms_type = "unicode"  THEN 1 ELSE 0 END) as incoming_unicode_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "api" AND sms_type = "unicode"  THEN 1 ELSE 0 END) as api_unicode_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "from" AND sms_type = "voice" THEN 1 ELSE 0 END) as outgoing_voice_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "to" AND sms_type = "voice" THEN 1 ELSE 0 END) as incoming_voice_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "api" AND sms_type = "voice" THEN 1 ELSE 0 END) as api_voice_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "from" AND sms_type = "mms"  THEN 1 ELSE 0 END) as outgoing_mms_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "to" AND sms_type = "mms"  THEN 1 ELSE 0 END) as incoming_mms_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "api" AND sms_type = "mms"  THEN 1 ELSE 0 END) as api_mms_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "from" AND sms_type = "whatsapp" THEN 1 ELSE 0 END) as outgoing_whatsapp_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "to" AND sms_type = "whatsapp" THEN 1 ELSE 0 END) as incoming_whatsapp_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "api" AND sms_type = "whatsapp" THEN 1 ELSE 0 END) as api_whatsapp_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "from" AND sms_type = "viber" THEN 1 ELSE 0 END) as outgoing_viber_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "to" AND sms_type = "viber" THEN 1 ELSE 0 END) as incoming_viber_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "api" AND sms_type = "viber" THEN 1 ELSE 0 END) as api_viber_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "from" AND sms_type = "otp" THEN 1 ELSE 0 END) as outgoing_otp_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "to" AND sms_type = "otp" THEN 1 ELSE 0 END) as incoming_otp_sms'),
                    DB::raw('SUM(CASE WHEN send_by = "api" AND sms_type = "otp" THEN 1 ELSE 0 END) as api_otp_sms'),

                )
                ->groupBy(DB::raw('DAY(created_at)'))
                ->get();

// Convert the current month reports to a collection keyed by day
            $currentMonthReports = $currentMonthReports->keyBy('day');


// Create a range of days for a 30-day period
            $daysRange = range(Carbon::now()->startOfMonth()->day, Carbon::now()->endOfMonth()->day);

// Fill in the missing days with zeros
            $mergedData = collect($daysRange)->map(function ($day) use ($currentMonthReports) {
                return [
                    'day'                   => $day,
                    'outgoing_plain_sms'    => $currentMonthReports->get($day)->outgoing_plain_sms ?? 0,
                    'incoming_plain_sms'    => $currentMonthReports->get($day)->incoming_plain_sms ?? 0,
                    'api_plain_sms'         => $currentMonthReports->get($day)->api_plain_sms ?? 0,
                    'outgoing_unicode_sms'  => $currentMonthReports->get($day)->outgoing_unicode_sms ?? 0,
                    'incoming_unicode_sms'  => $currentMonthReports->get($day)->incoming_unicode_sms ?? 0,
                    'api_unicode_sms'       => $currentMonthReports->get($day)->api_unicode_sms ?? 0,
                    'outgoing_voice_sms'    => $currentMonthReports->get($day)->outgoing_voice_sms ?? 0,
                    'incoming_voice_sms'    => $currentMonthReports->get($day)->incoming_voice_sms ?? 0,
                    'api_voice_sms'         => $currentMonthReports->get($day)->api_voice_sms ?? 0,
                    'outgoing_mms_sms'      => $currentMonthReports->get($day)->outgoing_mms_sms ?? 0,
                    'incoming_mms_sms'      => $currentMonthReports->get($day)->incoming_mms_sms ?? 0,
                    'api_mms_sms'           => $currentMonthReports->get($day)->api_mms_sms ?? 0,
                    'outgoing_whatsapp_sms' => $currentMonthReports->get($day)->outgoing_whatsapp_sms ?? 0,
                    'incoming_whatsapp_sms' => $currentMonthReports->get($day)->incoming_whatsapp_sms ?? 0,
                    'api_whatsapp_sms'      => $currentMonthReports->get($day)->api_whatsapp_sms ?? 0,
                    'outgoing_viber_sms'    => $currentMonthReports->get($day)->outgoing_viber_sms ?? 0,
                    'incoming_viber_sms'    => $currentMonthReports->get($day)->incoming_viber_sms ?? 0,
                    'api_viber_sms'         => $currentMonthReports->get($day)->api_viber_sms ?? 0,
                    'outgoing_otp_sms'      => $currentMonthReports->get($day)->outgoing_otp_sms ?? 0,
                    'incoming_otp_sms'      => $currentMonthReports->get($day)->incoming_otp_sms ?? 0,
                    'api_otp_sms'           => $currentMonthReports->get($day)->api_otp_sms ?? 0,
                ];
            })->values();


// Extract the required data for charting
            $days = $mergedData->pluck('day')->toArray();
// Define data arrays for different types of SMS
            $smsTypes = ['plain', 'unicode', 'voice', 'mms', 'whatsapp', 'viber', 'otp'];
            $charts   = [];

            foreach ($smsTypes as $type) {
                $outgoingData = $mergedData->pluck("outgoing_{$type}_sms")->toArray();
                $incomingData = $mergedData->pluck("incoming_{$type}_sms")->toArray();
                $apiData      = $mergedData->pluck("api_{$type}_sms")->toArray();

                $chartData = [
                    'outgoing' => $outgoingData,
                    'incoming' => $incomingData,
                    'api'      => $apiData,
                    'days'     => $days,
                ];

                $charts[$type] = $this->createLineChart("SMS Statistics for {$type} SMS", 'Outgoing, Incoming, and API SMS', $chartData);
            }

            $userAnnouncements = Auth::user()->announcements()
                ->wherePivot('read_at', '=', null)
                ->latest()
                ->take(5)
                ->get();


            return view('customer.dashboard', compact('breadcrumbs', 'sms_history', 'userAnnouncements', 'total_sms_sent', 'deliveredCount', 'undeliveredCount', 'charts'));
        }

        public function createLineChart($title, $subtitle, $data)
        {
            return (new LarapexChart)->lineChart()
                ->setTitle($title)
                ->setSubtitle($subtitle)
                ->addLine(__('locale.labels.outgoing'), $data['outgoing'])
                ->addLine(__('locale.labels.incoming'), $data['incoming'])
                ->addLine(__('locale.labels.api'), $data['api'])
                ->setXAxis($data['days']);
        }

    }
