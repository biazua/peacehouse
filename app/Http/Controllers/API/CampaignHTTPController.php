<?php

    namespace App\Http\Controllers\API;

    use App\Http\Controllers\Controller;
    use App\Library\SMSCounter;
    use App\Models\Campaigns;
    use App\Models\CustomerBasedSendingServer;
    use App\Models\Plan;
    use App\Models\Reports;
    use App\Models\Traits\ApiResponser;
    use App\Models\User;
    use App\Repositories\Contracts\CampaignRepository;
    use Carbon\Carbon;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Validator;
    use libphonenumber\NumberParseException;
    use libphonenumber\PhoneNumberUtil;

    class CampaignHTTPController extends Controller
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
        public function smsSend(Campaigns $campaign, Request $request, Carbon $carbon): JsonResponse
        {

            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }

            $validator = Validator::make($request->all(), [
                'recipient' => 'required',
                'message'   => 'required',
                'api_token' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $validator->errors()->first(),
                ]);
            }

            $user = User::where('api_token', $request->input('api_token'))->first();

            if ( ! $user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => __('locale.auth.failed'),
                ]);
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
         * @return JsonResponse|mixed
         *
         * @throws NumberParseException
         */
        private function processSingleRecipient($campaign, &$input)
        {
            $phone             = str_replace(['+', '(', ')', '-', ' '], '', $input['recipient']);
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
         * view single sms reports
         */
        public function viewSMS(Reports $uid, Request $request): JsonResponse
        {

            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }

            $user = User::where('api_token', $request->input('api_token'))->first();

            if ( ! $user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => __('locale.auth.failed'),
                ]);
            }


            if ( ! $user->can('developers')) {
                return $this->error('You do not have permission to access API', 403);
            }

            $reports = Reports::where('user_id', $user->id)->select('uid', 'to', 'from', 'message', 'customer_status', 'cost')->find($uid->id);
            if ($reports) {
                return $this->success($reports);
            }

            return $this->error('SMS Info not found');
        }

        /**
         * get all messages
         */
        public function viewAllSMS(Request $request): JsonResponse
        {

            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }

            $user = User::where('api_token', $request->input('api_token'))->first();

            if ( ! $user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => __('locale.auth.failed'),
                ]);
            }


            if ( ! $user->can('developers')) {
                return $this->error('You do not have permission to access API', 403);
            }

            $reports = Reports::where('user_id', $user->id)->select('uid', 'to', 'from', 'message', 'customer_status', 'cost')->orderBy('created_at', 'desc')->paginate(25);
            if ($reports) {
                return $this->success($reports);
            }

            return $this->error('SMS Info not found');
        }

    }
