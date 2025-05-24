<?php

    namespace App\Http\Controllers\API;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Campaigns\SendAPICampaign;
    use App\Http\Requests\Campaigns\SendAPISMS;
    use App\Library\SMSCounter;
    use App\Library\Tool;
    use App\Models\Campaigns;
    use App\Models\CustomerBasedSendingServer;
    use App\Models\Plan;
    use App\Models\Reports;
    use App\Models\Traits\ApiResponser;
    use App\Repositories\Contracts\CampaignRepository;
    use Carbon\Carbon;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Support\Facades\Auth;
    use libphonenumber\NumberParseException;
    use libphonenumber\PhoneNumberUtil;

    class CampaignController extends Controller
    {
        use ApiResponser;

        protected CampaignRepository $campaigns;

        /**
         * CampaignController constructor.
         */
        public function __construct(CampaignRepository $campaigns)
        {
            $this->campaigns = $campaigns;
        }

        /**
         * sms sending
         */
        public function smsSend(Campaigns $campaign, SendAPISMS $request, Carbon $carbon): JsonResponse
        {
            if (config('app.stage') == 'demo') {
                return $this->error('Sorry! This option is not available in demo mode');
            }

            $user = request()->user();
            if ( ! $user) {
                return $this->error(__('locale.auth.user_not_exist'));
            }

            if ( ! $user->can('developers')) {
                return $this->error('You do not have permission to access API', 403);
            }

            if ( ! isset($user->customer)) {
                return $this->error('Customer not found');
            }

            $activeSubscription = $user->customer->activeSubscription();
            if ($activeSubscription) {
                $plan = Plan::where('status', true)->find($activeSubscription->plan_id);
                if ( ! $plan) {
                    return $this->error('Purchased plan is not active. Please contact support team.');
                }
            }

            if ( ! isset($activeSubscription->plan)) {
                return $this->error('Purchased plan is not active. Please contact support team.');
            }

            if (config('app.trai_dlt') && $activeSubscription->plan->is_dlt && $request->input('dlt_template_id') == null) {
                return $this->error('The DLT Template ID is mandatory. Kindly reach out to the system administrator for further assistance');
            }

            if (config('app.trai_dlt') && $activeSubscription->plan->is_dlt && $user->dlt_entity_id == null) {
                return $this->error('The DLT Entity ID is mandatory. Kindly reach out to the system administrator for further assistance');
            }

            if (config('app.trai_dlt') && $activeSubscription->plan->is_dlt && $user->dlt_telemarketer_id == null) {
                return $this->error('The DLT Telemarketer ID is mandatory. Kindly reach out to the system administrator for further assistance');
            }

            $sms_type     = $request->get('type', 'plain');
            $sms_counter  = new SMSCounter();
            $message_data = $sms_counter->count($request->input('message'), $sms_type == 'whatsapp' ? 'WHATSAPP' : null);

            if ($message_data->encoding == 'UTF16') {
                $sms_type = 'unicode';
            }

            if ( ! in_array($sms_type, ['plain', 'unicode', 'voice', 'mms', 'whatsapp', 'viber', 'otp'])) {
                return $this->error(__('locale.exceptions.invalid_sms_type'));
            }

            if ($sms_type == 'voice' && ( ! $request->filled('gender') || ! $request->filled('language'))) {
                return $this->error('Language and gender parameters are required');
            }

            if ($sms_type == 'mms' && ! $request->filled('media_url')) {
                return $this->error('media_url parameter is required');
            }

            if ($sms_type == 'mms' && filter_var($request->input('media_url'), FILTER_VALIDATE_URL) === false) {
                return $this->error('Valid media url is required.');
            }

            $validateData = $this->campaigns->checkQuickSendValidation([
                'sender_id' => $request->sender_id,
                'message'   => $request->message,
                'user_id'   => $user->id,
                'sms_type'  => $sms_type,
            ]);

            if ($validateData->getData()->status == 'error') {
                return $this->error($validateData->getData()->message);
            }

            try {

                $input = $this->prepareInput($request, $user, $sms_type, $carbon);

                $coverage = CustomerBasedSendingServer::where('user_id', $user->id)->where('status', 1)->count();

                if ($coverage > 0 && isset($user->api_sending_server)) {
                    $input['sending_server'] = $user->api_sending_server;
                }

                $isBulkSms = substr_count($input['recipient'], ',') > 0;

                if ($request->input('dlt_template_id') !== null) {
                    $input['dlt_template_id'] = $request->input('dlt_template_id');
                }

                $data = $isBulkSms
                    ? $this->campaigns->sendApi($campaign, $input)
                    : $this->processSingleRecipient($campaign, $input);

                $status = optional($data->getData())->status;

                return $status === 'success'
                    ? $this->success($data->getData()->data ?? null, $data->getData()->message)
                    : $this->error($data->getData()->message ?? __('locale.exceptions.something_went_wrong'), 403);

            } catch (NumberParseException $exception) {
                return $this->error($exception->getMessage(), 403);
            }
        }

        /**
         * @return JsonResponse|mixed
         *
         * @throws NumberParseException
         */
        private function processSingleRecipient($campaign, &$input)
        {

            $phone             = str_replace(['+', '(', ')', '-', ' '], '', $input['recipient']);
            $phone             = ltrim($phone, '0');
            $phoneUtil         = PhoneNumberUtil::getInstance();
            $phoneNumberObject = $phoneUtil->parse('+' . $phone);
            $countryCode       = $phoneNumberObject->getCountryCode();
            $isoCode           = $phoneUtil->getRegionCodeForNumber($phoneNumberObject);

            if ( ! $phoneUtil->isPossibleNumber($phoneNumberObject) || empty($countryCode) || empty($isoCode)) {
                return $this->error(__('locale.customer.invalid_phone_number', ['phone' => $phone]));
            }

            if ($phoneNumberObject->isItalianLeadingZero()) {
                $input['recipient'] = '0' . $phoneNumberObject->getNationalNumber();
            } else {
                $input['recipient'] = $phoneNumberObject->getNationalNumber();
            }

            $input['country_code'] = $countryCode;
            $input['region_code']  = $isoCode;

            return $this->campaigns->quickSend($campaign, $input);
        }

        /**
         * @return array
         */
        private function prepareInput($request, $user, $sms_type, $carbon)
        {
            $input = [
                'sender_id' => $request->input('sender_id'),
                'sms_type'  => $sms_type,
                'api_key'   => $user->api_token,
                'user'      => $user,
                'recipient' => $request->input('recipient'),
                'delimiter' => ',',
                'message'   => $request->input('message'),
            ];

            switch ($sms_type) {
                case 'voice':
                    $input['language'] = $request->input('language');
                    $input['gender']   = $request->input('gender');
                    break;
                case 'mms':
                case 'whatsapp':
                case 'viber':
                    if ($request->filled('media_url')) {
                        $input['media_url'] = $request->input('media_url');
                    }
                    break;
            }

            if ($request->filled('schedule_time')) {
                $input['schedule']        = true;
                $input['schedule_date']   = $carbon->parse($request->input('schedule_time'))->toDateString();
                $input['schedule_time']   = $carbon->parse($request->input('schedule_time'))->setSeconds(0)->format('H:i');
                $input['timezone']        = $user->timezone;
                $input['frequency_cycle'] = 'onetime';
            }

            return $input;
        }

        /**
         * view single sms reports
         */
        public function viewSMS(Reports $uid): JsonResponse
        {

            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }


            $user = request()->user();
            if ( ! $user) {
                return $this->error(__('locale.auth.user_not_exist'));
            }


            if ( ! request()->user()->can('developers')) {
                return $this->error('You do not have permission to access API', 403);
            }

            if (request()->user()->can('view_reports')) {
                $reports = Reports::where('user_id', $user->id)->select('uid', 'to', 'from', 'message', 'customer_status', 'cost')->find($uid->id);
                if ($reports) {
                    return $this->success($reports);
                }

                return $this->error('SMS Info not found');
            }

            return $this->error(__('locale.http.403.description'), 403);
        }


        /**
         * get all messages
         */
        public function viewAllSMS(): JsonResponse
        {

            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }

            $user = request()->user();
            if ( ! $user) {
                return $this->error(__('locale.auth.user_not_exist'));
            }

            if ( ! request()->user()->can('developers')) {
                return $this->error('You do not have permission to access API', 403);
            }

            if (request()->user()->can('view_reports')) {
                $reports = Reports::where('user_id', $user->id)->select('uid', 'to', 'from', 'message', 'customer_status', 'cost')->orderBy('created_at', 'desc')->paginate(25);
                if ($reports) {
                    return $this->success($reports);
                }

                return $this->error('SMS Info not found');
            }

            return $this->error(__('locale.http.403.description'), 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Version 3.7
        |--------------------------------------------------------------------------
        |
        | Send Campaign Using API
        |
        */

        public function campaign(Campaigns $campaign, SendAPICampaign $request)
        {

            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }

            $input = $request->all();
            $user  = request()->user();

            if ( ! $user) {
                return $this->error(__('locale.auth.user_not_exist'));
            }

            if ( ! $user->can('developers')) {
                return $this->error('You do not have permission to access API', 403);
            }

            $sendingServers = CustomerBasedSendingServer::where('user_id', $user->id)->where('status', 1)->count();

            if ($sendingServers > 0 && isset($user->api_sending_server)) {
                $input['sending_server'] = $user->api_sending_server;
            }

            $sms_type     = $request->get('type', 'plain');
            $sms_counter  = new SMSCounter();
            $message_data = $sms_counter->count($request->input('message'), $sms_type == 'whatsapp' ? 'WHATSAPP' : null);

            if ($message_data->encoding == 'UTF16') {
                $sms_type = 'unicode';
            }

            if ( ! in_array($sms_type, ['plain', 'unicode', 'voice', 'mms', 'whatsapp', 'viber', 'otp'])) {
                return $this->error(__('locale.exceptions.invalid_sms_type'));
            }

            if ($sms_type == 'voice' && ( ! $request->filled('gender') || ! $request->filled('language'))) {
                return $this->error('Language and gender parameters are required');
            }

            if ($sms_type == 'mms' && ! $request->filled('media_url')) {
                return $this->error('media_url parameter is required');
            }

            if ($sms_type == 'mms' && filter_var($request->input('media_url'), FILTER_VALIDATE_URL) === false) {
                return $this->error('Valid media url is required.');
            }
            $input['api_key']  = $user->api_token;
            $input['timezone'] = $user->timezone;
            $input['name']     = 'API_' . time();

            unset($input['sender_id']);

            if ($request->get('sender_id') !== null) {
                if (is_numeric($request->get('sender_id'))) {
                    $input['originator']   = 'phone_number';
                    $input['phone_number'] = [$request->get('sender_id')];
                } else {
                    $input['sender_id']  = [$request->get('sender_id')];
                    $input['originator'] = 'sender_id';
                }
            }

            if ($request->input('schedule_time') !== null) {
                $input['schedule']        = true;
                $input['schedule_date']   = Carbon::parse($request->input('schedule_time'))->toDateString();
                $input['schedule_time']   = Carbon::parse($request->input('schedule_time'))->setSeconds(0)->format('H:i');
                $input['frequency_cycle'] = 'onetime';
            }

            $data = $this->campaigns->apiCampaignBuilder($campaign, $input);

            if (isset($data->getData()->status)) {

                if ($data->getData()->status == 'success') {
                    return $this->success($data->getData()->data, $data->getData()->message);
                }

                return $this->error($data->getData()->message);

            }

            return $this->error(__('locale.exceptions.something_went_wrong'));

        }


        /**
         * view campaign
         */
        public function viewCampaign(Campaigns $uid): JsonResponse
        {

            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }


            if ( ! request()->user()->can('developers')) {
                return $this->error('You do not have permission to access API', 403);
            }

            if (request()->user()->can('view_reports')) {

                $reportStatusCounts = Reports::where('user_id', Auth::user()->id)->where('campaign_id', $uid->id)->selectRaw('
        COUNT(CASE WHEN customer_status = "Enroute" THEN 1 END) as enroute_count,
        COUNT(CASE WHEN customer_status = "Delivered" THEN 1 END) as delivered_count,
        COUNT(CASE WHEN customer_status = "Expired" THEN 1 END) as expired_count,
        COUNT(CASE WHEN customer_status = "Undelivered" THEN 1 END) as undelivered_count,
        COUNT(CASE WHEN customer_status = "Rejected" THEN 1 END) as rejected_count,
        COUNT(CASE WHEN customer_status = "Accepted" THEN 1 END) as accepted_count,
        COUNT(CASE WHEN customer_status = "Skipped" THEN 1 END) as skipped_count,
        COUNT(CASE WHEN customer_status NOT IN ("Enroute", "Delivered", "Expired", "Undelivered", "Rejected", "Accepted", "Skipped") THEN 1 END) as failed_count
    ')
                    ->first();

                $data = [
                    'id'         => $uid->uid,
                    'name'       => $uid->campaign_name,
                    'message'    => $uid->message,
                    'status'     => $uid->status,
                    'type'       => $uid->sms_type,
                    'created_at' => Tool::customerDateTime($uid->created_at),
                ];

                if ( ! empty($uid->run_at)) {
                    $data['start_at'] = Tool::customerDateTime($uid->run_at);
                }

                if ($uid->status == 'done') {
                    $data['delivery_at'] = Tool::customerDateTime($uid->delivery_at);
                }

                if ($reportStatusCounts) {
                    $data['stats'] = $reportStatusCounts;
                }


                return $this->success($data, 'Campaign successfully retrieved');
            }

            return $this->error(__('locale.http.403.description'), 403);
        }


    }
