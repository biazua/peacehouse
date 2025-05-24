<?php

    namespace App\Http\Controllers\Debug;

    use App\Http\Controllers\Controller;
    use App\Models\Campaigns;
    use App\Models\ChatBox;
    use App\Models\ContactGroups;
    use App\Models\Contacts;
    use App\Models\PaymentMethods;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\DB;
    use libphonenumber\NumberParseException;
    use libphonenumber\PhoneNumberUtil;

    class DebugController extends Controller
    {
        public function index()
        {
            $chatboxes = ChatBox::with(['chatBoxMessages' => function ($query) {
                $query->latest();
            }])->get();

            $updates = [];

            foreach ($chatboxes as $box) {
                // Check if there are any messages and get the latest one
                $latestMessage = $box->chatBoxMessages->first();

                // Determine the reply_by_customer status
                $updates[$box->id] = [
                    'reply_by_customer' => $latestMessage && $latestMessage->send_by === 'to',
                ];
            }

// Perform a batch update on all chat boxes
            ChatBox::whereIn('id', array_keys($updates))
                ->update(['reply_by_customer' => false]); // Default value

            foreach ($updates as $id => $data) {
                if ($data['reply_by_customer']) {
                    ChatBox::where('id', $id)->update(['reply_by_customer' => true]);
                }
            }


            return redirect()->route('user.home');
        }

        public function removeJobs()
        {
            DB::table('job_monitors')->truncate();
            DB::table('job_batches')->truncate();
            DB::table('jobs')->truncate();
            DB::table('import_job_histories')->truncate();
            DB::table('failed_jobs')->truncate();

            return 'Job cleared successfully';
        }

        public function addGateways()
        {
            $check_exist = PaymentMethods::where('type', 'myFatoorah')->first();
            if ( ! $check_exist) {
                $data = PaymentMethods::create(

                    [
                        'name'    => PaymentMethods::TYPE_MYFATOORAH,
                        'type'    => PaymentMethods::TYPE_MYFATOORAH,
                        'options' => json_encode([
                            'api_token'        => 'rLtt6JWvbUHDDhsZnfpAhpYk4dxYDQkbcPTyGaKp2TYqQgG7FGZ5Th_WD53Oq8Ebz6A53njUoo1w3pjU1D4vs_ZMqFiz_j0urb_BH9Oq9VZoKFoJEDAbRZepGcQanImyYrry7Kt6MnMdgfG5jn4HngWoRdKduNNyP4kzcp3mRv7x00ahkm9LAK7ZRieg7k1PDAnBIOG3EyVSJ5kK4WLMvYr7sCwHbHcu4A5WwelxYK0GMJy37bNAarSJDFQsJ2ZvJjvMDmfWwDVFEVe_5tOomfVNt6bOg9mexbGjMrnHBnKnZR1vQbBtQieDlQepzTZMuQrSuKn-t5XZM7V6fCW7oP-uXGX-sMOajeX65JOf6XVpk29DP6ro8WTAflCDANC193yof8-f5_EYY-3hXhJj7RBXmizDpneEQDSaSz5sFk0sV5qPcARJ9zGG73vuGFyenjPPmtDtXtpx35A-BVcOSBYVIWe9kndG3nclfefjKEuZ3m4jL9Gg1h2JBvmXSMYiZtp9MR5I6pvbvylU_PP5xJFSjVTIz7IQSjcVGO41npnwIxRXNRxFOdIUHn0tjQ-7LwvEcTXyPsHXcMD8WtgBh-wxR8aKX7WPSsT1O8d8reb2aR7K3rkV3K82K_0OgawImEpwSvp9MNKynEAJQS6ZHe_J_l77652xwPNxMRTMASk1ZsJL',
                            'country_iso_code' => 'KWT',
                            'environment'      => 'sandbox',
                        ]),
                        'status'  => false,
                    ],

                );
                if ($data) {
                    return redirect()->route('admin.payment-gateways.show', $data->uid)->with([
                        'status'  => 'success',
                        'message' => 'Gateway was successfully Added',
                    ]);
                }

                return redirect()->route('login')->with([
                    'status'  => 'error',
                    'message' => __('locale.exceptions.something_went_wrong'),
                ]);

            }

            return 'Gateway already exists';
        }


        public function removeContacts()
        {
            Contacts::chunk(1000, function ($contacts) {
                foreach ($contacts as $contact) {
                    try {

                        $phoneUtil         = PhoneNumberUtil::getInstance();
                        $phoneNumberObject = $phoneUtil->parse('+' . $contact->phone);
                        $countryCode       = $phoneNumberObject->getCountryCode();
                        $isoCode           = $phoneUtil->getRegionCodeForNumber($phoneNumberObject);

                        if ( ! $phoneUtil->isPossibleNumber($phoneNumberObject) || empty($countryCode) || empty($isoCode)) {
                            $contact->delete();
                        }
                    } catch (NumberParseException) {
                        $contact->delete();
                    }
                }
            });

            ContactGroups::chunk(100, function ($contactGroups) {
                foreach ($contactGroups as $contactGroup) {
                    $contactGroup->updateCache();
                }
            });

            return 'Unwanted contacts removed successfully';

        }

        public function cacheClear()
        {

            Cache::flush();

            return 'Cache was cleared successfully';
        }

        public function updateCampaignCache($campaign, $number)
        {
            $campaign = Campaigns::where('uid', $campaign)->first();
            if ($campaign) {
                $data                 = json_decode($campaign->cache, true);
                $data['ContactCount'] = $number;
                $campaign->cache      = json_encode($data);
                $campaign->setDone();
                $campaign->delivery_at = now();
                $campaign->save();

                return 'Cache was updated successfully';

            }

            return 'Campaign not found';
        }

    }
