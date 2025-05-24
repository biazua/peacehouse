<?php

    namespace App\Console\Commands;

    use App\Library\Tool;
    use App\Models\Announcements;
    use App\Models\AppConfig;
    use App\Models\Automation;
    use App\Models\Blacklists;
    use App\Models\Campaigns;
    use App\Models\CampaignsList;
    use App\Models\CampaignsSenderid;
    use App\Models\ContactGroups;
    use App\Models\Contacts;
    use App\Models\Currency;
    use App\Models\Customer;
    use App\Models\Invoices;
    use App\Models\Keywords;
    use App\Models\Language;
    use App\Models\Notifications;
    use App\Models\PhoneNumbers;
    use App\Models\Plan;
    use App\Models\PlansCoverageCountries;
    use App\Models\PlanSendingCreditPrice;
    use App\Models\Reports;
    use App\Models\Role;
    use App\Models\Senderid;
    use App\Models\SenderidPlan;
    use App\Models\SendingServer;
    use App\Models\SpamWord;
    use App\Models\Subscription;
    use App\Models\SubscriptionLog;
    use App\Models\SubscriptionTransaction;
    use App\Models\Templates;
    use App\Models\TrackingLog;
    use App\Models\User;
    use App\Repositories\Eloquent\EloquentSendingServerRepository;
    use Carbon\Carbon;
    use Database\Seeders\ChatBoxSeeder;
    use Faker\Factory;
    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\DB;

    class UpdateDemo extends Command
    {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'demo:update';

        /**
         * The console command description.
         *
         * @var string
         */
        protected $description = 'Update Demo Database in every day';

        /**
         * Create a new command instance.
         *
         * @return void
         */
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * Execute the console command.
         */
        public function handle(): int
        {

            AppConfig::where('setting', 'customer_permissions')->update([
                'value' => json_encode(
                    [
                        'access_backend',
                        'view_reports',
                        'automations',
                        'view_contact_group',
                        'create_contact_group',
                        'update_contact_group',
                        'delete_contact_group',
                        'view_contact',
                        'create_contact',
                        'update_contact',
                        'delete_contact',
                        'view_numbers',
                        'buy_numbers',
                        'buy_numbers_using_api',
                        'release_numbers',
                        'view_keywords',
                        'create_keywords',
                        'buy_keywords',
                        'update_keywords',
                        'release_keywords',
                        'view_sender_id',
                        'create_sender_id',
                        'delete_sender_id',
                        'view_blacklist',
                        'create_blacklist',
                        'delete_blacklist',
                        'sms_campaign_builder',
                        'sms_quick_send',
                        'sms_bulk_messages',
                        'voice_campaign_builder',
                        'voice_quick_send',
                        'voice_bulk_messages',
                        'mms_campaign_builder',
                        'mms_quick_send',
                        'mms_bulk_messages',
                        'whatsapp_campaign_builder',
                        'whatsapp_quick_send',
                        'whatsapp_bulk_messages',
                        'viber_campaign_builder',
                        'viber_quick_send',
                        'viber_bulk_messages',
                        'otp_campaign_builder',
                        'otp_quick_send',
                        'otp_bulk_messages',
                        'sms_template',
                        'chat_box',
                        'developers',
                    ]
                ),
            ]);

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('languages')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $languages = [
                [
                    'name'     => 'English',
                    'code'     => 'en',
                    'iso_code' => 'us',
                    'status'   => true,
                ],
                [
                    'name'     => 'French',
                    'code'     => 'fr',
                    'iso_code' => 'fr',
                    'status'   => true,
                ],
                [
                    'name'     => 'Chinese',
                    'code'     => 'zh',
                    'iso_code' => 'cn',
                    'status'   => true,
                ],
                [
                    'name'     => 'Spanish',
                    'code'     => 'es',
                    'iso_code' => 'es',
                    'status'   => true,
                ],
                [
                    'name'     => 'Portuguese',
                    'code'     => 'pt',
                    'iso_code' => 'br',
                    'status'   => true,
                ],
                [
                    'name'     => 'Arabic',
                    'code'     => 'ar',
                    'iso_code' => 'sa',
                    'status'   => true,
                ],
                [
                    'name'     => 'Italian',
                    'code'     => 'it',
                    'iso_code' => 'it',
                    'status'   => true,
                ],
                [
                    'name'     => 'Korean',
                    'code'     => 'ko',
                    'iso_code' => 'kr',
                    'status'   => true,
                ],
                [
                    'name'     => 'Slovenian',
                    'code'     => 'sl',
                    'iso_code' => 'sk',
                    'status'   => true,
                ],
            ];

            foreach ($languages as $language) {
                Language::create($language);
            }

            $defaultPassword = '12345678';

            // Create super admin user
            $user     = new User();
            $role     = new Role();
            $customer = new Customer();

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $user->truncate();
            $role->truncate();
            $customer->truncate();
            DB::table('role_user')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            /*
         * Create roles
         */

            $superAdminRole = $role->create([
                'name'   => 'administrator',
                'status' => true,
            ]);

            foreach (config('permissions') as $key => $name) {
                $superAdminRole->permissions()->create(['name' => $key]);
            }

            $authorRole = $role->create([
                'name'   => 'author',
                'status' => true,
            ]);

            foreach (
                [

                    'access backend',
                    'view customer',
                    'create customer',
                    'edit customer',
                    'delete customer',
                    'view subscription',
                    'new subscription',
                    'manage subscription',
                    'delete subscription',
                    'manage plans',
                    'create plans',
                    'edit plans',
                    'delete plans',
                    'manage currencies',
                    'create currencies',
                    'edit currencies',
                    'delete currencies',
                    'view sending_servers',
                    'create sending_servers',
                    'edit sending_servers',
                    'delete sending_servers',
                    'view keywords',
                    'create keywords',
                    'edit keywords',
                    'delete keywords',
                    'view sender_id',
                    'create sender_id',
                    'edit sender_id',
                    'delete sender_id',
                    'view blacklist',
                    'create blacklist',
                    'edit blacklist',
                    'delete blacklist',
                    'view spam_word',
                    'create spam_word',
                    'edit spam_word',
                    'delete spam_word',
                    'view invoices',
                    'create invoices',
                    'edit invoices',
                    'delete invoices',
                    'view sms_history',
                    'view block_message',
                    'manage coverage_rates',
                ] as $name) {
                $authorRole->permissions()->create(['name' => $name]);
            }

            $superAdmin = $user->create([
                'first_name'        => 'Super',
                'last_name'         => 'Admin',
                'image'             => 'app/profile/avatar-1.jpg',
                'email'             => 'admin@codeglen.com',
                'password'          => bcrypt($defaultPassword),
                'status'            => true,
                'is_admin'          => true,
                'is_customer'       => true,
                'sms_unit'          => '6000',
                'active_portal'     => 'admin',
                'locale'            => app()->getLocale(),
                'timezone'          => config('app.timezone'),
                'email_verified_at' => now(),
            ]);

            $superAdmin->customer()->create([
                'user_id'            => $user->id,
                'company'            => 'Codeglen',
                'phone'              => '8801721970168',
                'address'            => 'House # 01, Road # 01, Block # C, Gulshan 1',
                'city'               => 'Dhaka',
                'state'              => 'Dhaka',
                'country'            => 'Bangladesh',
                'postcode'           => '1216',
                'financial_address'  => 'House # 01, Road # 01, Block # C, Gulshan 1',
                'financial_city'     => 'Dhaka',
                'financial_postcode' => '1216',
                'tax_number'         => '21-4330267',
                'website'            => config('app.url'),
                'notifications'      => json_encode([
                    'login'        => 'no',
                    'sender_id'    => 'yes',
                    'keyword'      => 'yes',
                    'subscription' => 'yes',
                    'promotion'    => 'yes',
                    'profile'      => 'yes',
                ]),
                'permissions'        => json_encode([
                    'access_backend',
                    'view_reports',
                    'automations',
                    'view_contact_group',
                    'create_contact_group',
                    'update_contact_group',
                    'delete_contact_group',
                    'view_contact',
                    'create_contact',
                    'update_contact',
                    'delete_contact',
                    'view_numbers',
                    'buy_numbers',
                    'buy_numbers_using_api',
                    'release_numbers',
                    'view_keywords',
                    'create_keywords',
                    'buy_keywords',
                    'update_keywords',
                    'release_keywords',
                    'view_sender_id',
                    'create_sender_id',
                    'delete_sender_id',
                    'view_blacklist',
                    'create_blacklist',
                    'delete_blacklist',
                    'sms_campaign_builder',
                    'sms_quick_send',
                    'sms_bulk_messages',
                    'voice_campaign_builder',
                    'voice_quick_send',
                    'voice_bulk_messages',
                    'mms_campaign_builder',
                    'mms_quick_send',
                    'mms_bulk_messages',
                    'whatsapp_campaign_builder',
                    'whatsapp_quick_send',
                    'whatsapp_bulk_messages',
                    'viber_campaign_builder',
                    'viber_quick_send',
                    'viber_bulk_messages',
                    'otp_campaign_builder',
                    'otp_quick_send',
                    'otp_bulk_messages',
                    'sms_template',
                    'chat_box',
                    'developers',
                ]),
            ]);

            $superAdmin->api_token = $superAdmin->createToken('admin@codeglen.com')->plainTextToken;
            $superAdmin->save();

            $superAdmin->roles()->save($superAdminRole);

            $supervisor = $user->create([
                'first_name'        => 'Shamim',
                'last_name'         => 'Rahman',
                'image'             => 'app/profile/avatar-3.JPG',
                'email'             => 'shamim97@gmail.com',
                'password'          => bcrypt($defaultPassword),
                'status'            => true,
                'is_admin'          => true,
                'active_portal'     => 'admin',
                'locale'            => app()->getLocale(),
                'timezone'          => config('app.timezone'),
                'email_verified_at' => now(),
            ]);

            $supervisor->api_token = $supervisor->createToken('shamim97@gmail.com')->plainTextToken;
            $supervisor->save();

            $supervisor->roles()->save($authorRole);

            $customers = $user->create([
                'first_name'        => 'Codeglen',
                'last_name'         => null,
                'image'             => 'app/profile/avatar-3.jpeg',
                'email'             => 'customer@codeglen.com',
                'password'          => bcrypt($defaultPassword),
                'status'            => true,
                'sms_unit'          => 6000,
                'is_admin'          => false,
                'is_customer'       => true,
                'active_portal'     => 'customer',
                'locale'            => app()->getLocale(),
                'timezone'          => config('app.timezone'),
                'email_verified_at' => now(),
            ]);

            $customers->api_token = $customers->createToken('customer@codeglen.com')->plainTextToken;
            $customers->save();

            $customer->create([
                'user_id'            => $customers->id,
                'company'            => 'Codeglen',
                'website'            => 'https://codeglen.com',
                'address'            => 'Banasree, Rampura',
                'city'               => 'Dhaka',
                'postcode'           => '1219',
                'financial_address'  => 'Banasree, Rampura',
                'financial_city'     => 'Dhaka',
                'financial_postcode' => '1219',
                'tax_number'         => '21-4330267',
                'state'              => 'Dhaka',
                'country'            => 'Bangladesh',
                'phone'              => '8801621970168',
                'permissions'        => json_encode([
                    'access_backend',
                    'view_reports',
                    'automations',
                    'view_contact_group',
                    'create_contact_group',
                    'update_contact_group',
                    'delete_contact_group',
                    'view_contact',
                    'create_contact',
                    'update_contact',
                    'delete_contact',
                    'view_numbers',
                    'buy_numbers',
                    'buy_numbers_using_api',
                    'release_numbers',
                    'view_keywords',
                    'create_keywords',
                    'buy_keywords',
                    'update_keywords',
                    'release_keywords',
                    'view_sender_id',
                    'create_sender_id',
                    'delete_sender_id',
                    'view_blacklist',
                    'create_blacklist',
                    'delete_blacklist',
                    'sms_campaign_builder',
                    'sms_quick_send',
                    'sms_bulk_messages',
                    'voice_campaign_builder',
                    'voice_quick_send',
                    'voice_bulk_messages',
                    'mms_campaign_builder',
                    'mms_quick_send',
                    'mms_bulk_messages',
                    'whatsapp_campaign_builder',
                    'whatsapp_quick_send',
                    'whatsapp_bulk_messages',
                    'viber_campaign_builder',
                    'viber_quick_send',
                    'viber_bulk_messages',
                    'otp_campaign_builder',
                    'otp_quick_send',
                    'otp_bulk_messages',
                    'sms_template',
                    'chat_box',
                    'developers',
                ]),
                'notifications'      => json_encode([
                    'login'        => 'no',
                    'sender_id'    => 'yes',
                    'keyword'      => 'yes',
                    'subscription' => 'yes',
                    'promotion'    => 'yes',
                    'profile'      => 'yes',
                ]),
            ]);

            $customer_two = $user->create([
                'first_name'        => 'DLT',
                'last_name'         => 'User',
                'image'             => 'app/profile/avatar-4.jpg',
                'email'             => 'dlt@codeglen.com',
                'password'          => bcrypt($defaultPassword),
                'status'            => true,
                'is_admin'          => false,
                'is_customer'       => true,
                'sms_unit'          => 5000,
                'active_portal'     => 'customer',
                'locale'            => app()->getLocale(),
                'timezone'          => config('app.timezone'),
                'email_verified_at' => now(),
                'created_at'        => Carbon::now()->subMonths(3),
                'updated_at'        => Carbon::now()->subMonths(3),
            ]);

            $customer_two->api_token = $customer_two->createToken('dlt@codeglen.com')->plainTextToken;
            $customer_two->save();

            $customer->create([
                'user_id'            => $customer_two->id,
                'company'            => 'Codeglen',
                'phone'              => '8801721970000',
                'address'            => 'House # 01, Road # 01, Block # C, Gulshan 1',
                'city'               => 'Dhaka',
                'state'              => 'Dhaka',
                'country'            => 'Bangladesh',
                'postcode'           => '1216',
                'financial_address'  => 'House # 01, Road # 01, Block # C, Gulshan 1',
                'financial_city'     => 'Dhaka',
                'financial_postcode' => '1216',
                'tax_number'         => '21-4330267',
                'website'            => config('app.url'),
                'notifications'      => json_encode([
                    'login'        => 'no',
                    'sender_id'    => 'yes',
                    'keyword'      => 'yes',
                    'subscription' => 'yes',
                    'promotion'    => 'yes',
                    'profile'      => 'yes',
                ]),
                'permissions'        => json_encode([
                    'access_backend',
                    'view_reports',
                    'automations',
                    'view_contact_group',
                    'create_contact_group',
                    'update_contact_group',
                    'delete_contact_group',
                    'view_contact',
                    'create_contact',
                    'update_contact',
                    'delete_contact',
                    'view_numbers',
                    'buy_numbers',
                    'buy_numbers_using_api',
                    'release_numbers',
                    'view_keywords',
                    'create_keywords',
                    'buy_keywords',
                    'update_keywords',
                    'release_keywords',
                    'view_sender_id',
                    'create_sender_id',
                    'delete_sender_id',
                    'view_blacklist',
                    'create_blacklist',
                    'delete_blacklist',
                    'sms_campaign_builder',
                    'sms_quick_send',
                    'sms_bulk_messages',
                    'voice_campaign_builder',
                    'voice_quick_send',
                    'voice_bulk_messages',
                    'mms_campaign_builder',
                    'mms_quick_send',
                    'mms_bulk_messages',
                    'whatsapp_campaign_builder',
                    'whatsapp_quick_send',
                    'whatsapp_bulk_messages',
                    'viber_campaign_builder',
                    'viber_quick_send',
                    'viber_bulk_messages',
                    'otp_campaign_builder',
                    'otp_quick_send',
                    'otp_bulk_messages',
                    'sms_template',
                    'chat_box',
                    'developers',
                ]),
            ]);

            $customer_three = $user->create([
                'first_name'        => 'Abul Kashem',
                'last_name'         => 'Shamim',
                'image'             => 'app/profile/avatar-5.jpg',
                'email'             => 'kashem97@gmail.com',
                'password'          => bcrypt($defaultPassword),
                'status'            => true,
                'is_admin'          => false,
                'sms_unit'          => '5',
                'is_customer'       => true,
                'active_portal'     => 'customer',
                'locale'            => app()->getLocale(),
                'timezone'          => config('app.timezone'),
                'email_verified_at' => now(),
                'created_at'        => Carbon::now()->subMonths(2),
                'updated_at'        => Carbon::now()->subMonths(2),
            ]);


            $customer_three->api_token = $customer_three->createToken('kashem97@gmail.com')->plainTextToken;
            $customer_three->save();

            $customer->create([
                'user_id'            => $customer_three->id,
                'company'            => 'Codeglen',
                'website'            => 'https://codeglen.com',
                'address'            => 'Banasree, Rampura',
                'city'               => 'Dhaka',
                'postcode'           => '1219',
                'financial_address'  => 'Banasree, Rampura',
                'financial_city'     => 'Dhaka',
                'financial_postcode' => '1219',
                'tax_number'         => '21-4330267',
                'state'              => 'Dhaka',
                'country'            => 'Bangladesh',
                'phone'              => '8801700000000',
                'created_at'         => Carbon::now()->subMonths(2),
                'updated_at'         => Carbon::now()->subMonths(2),
                'permissions'        => json_encode([
                    'view_reports',
                    'view_contact_group',
                    'create_contact_group',
                    'update_contact_group',
                    'delete_contact_group',
                    'view_contact',
                    'create_contact',
                    'update_contact',
                    'delete_contact',
                    'view_numbers',
                    'buy_numbers',
                    'release_numbers',
                    'view_keywords',
                    'buy_keywords',
                    'update_keywords',
                    'release_keywords',
                    'view_sender_id',
                    'create_sender_id',
                    'view_blacklist',
                    'create_blacklist',
                    'delete_blacklist',
                    'sms_campaign_builder',
                    'sms_quick_send',
                    'sms_bulk_messages',
                    'access_backend',
                ]),
                'notifications'      => json_encode([
                    'login'        => 'no',
                    'sender_id'    => 'no',
                    'keyword'      => 'yes',
                    'subscription' => 'yes',
                    'promotion'    => 'no',
                    'profile'      => 'yes',
                ]),
            ]);

            $customer_four = $user->create([
                'first_name'        => 'Jhon',
                'last_name'         => 'Doe',
                'image'             => 'app/profile/avatar-6.jpg',
                'email'             => 'jhon@gmail.com',
                'password'          => bcrypt($defaultPassword),
                'status'            => true,
                'is_admin'          => false,
                'is_customer'       => true,
                'sms_unit'          => '15000',
                'active_portal'     => 'customer',
                'locale'            => app()->getLocale(),
                'timezone'          => config('app.timezone'),
                'email_verified_at' => now(),
                'created_at'        => Carbon::now()->subMonth(),
                'updated_at'        => Carbon::now()->subMonth(),
            ]);


            $customer_four->api_token = $customer_four->createToken('jhon@gmail.com')->plainTextToken;
            $customer_four->save();

            $customer->create([
                'user_id'            => $customer_four->id,
                'company'            => 'Codeglen',
                'website'            => 'https://codeglen.com',
                'address'            => 'Banasree, Rampura',
                'city'               => 'Dhaka',
                'postcode'           => '1219',
                'financial_address'  => 'Banasree, Rampura',
                'financial_city'     => 'Dhaka',
                'financial_postcode' => '1219',
                'tax_number'         => '21-4330267',
                'state'              => 'Dhaka',
                'country'            => 'Bangladesh',
                'phone'              => '8801700000000',
                'created_at'         => Carbon::now()->subMonth(),
                'updated_at'         => Carbon::now()->subMonth(),
                'permissions'        => json_encode([
                    'view_reports',
                    'view_contact_group',
                    'create_contact_group',
                    'update_contact_group',
                    'delete_contact_group',
                    'view_contact',
                    'create_contact',
                    'update_contact',
                    'delete_contact',
                    'view_numbers',
                    'buy_numbers',
                    'release_numbers',
                    'view_keywords',
                    'buy_keywords',
                    'update_keywords',
                    'release_keywords',
                    'view_sender_id',
                    'create_sender_id',
                    'view_blacklist',
                    'create_blacklist',
                    'delete_blacklist',
                    'sms_campaign_builder',
                    'sms_quick_send',
                    'sms_bulk_messages',
                    'access_backend',
                ]),
                'notifications'      => json_encode([
                    'login'        => 'no',
                    'sender_id'    => 'no',
                    'keyword'      => 'yes',
                    'subscription' => 'yes',
                    'promotion'    => 'no',
                    'profile'      => 'yes',
                ]),
            ]);

            $customer_five = $user->create([
                'first_name'        => 'Sara',
                'last_name'         => 'Doe',
                'image'             => null,
                'email'             => 'sara@gmail.com',
                'password'          => bcrypt($defaultPassword),
                'status'            => true,
                'is_admin'          => false,
                'is_customer'       => true,
                'active_portal'     => 'customer',
                'locale'            => app()->getLocale(),
                'timezone'          => config('app.timezone'),
                'email_verified_at' => now(),
                'created_at'        => Carbon::now()->subMonth(),
                'updated_at'        => Carbon::now()->subMonth(),
            ]);

            $customer_five->api_token = $customer_five->createToken('sara@gmail.com')->plainTextToken;
            $customer_five->save();

            $customer->create([
                'user_id'            => $customer_five->id,
                'company'            => 'Codeglen',
                'website'            => 'https://codeglen.com',
                'address'            => 'Banasree, Rampura',
                'city'               => 'Dhaka',
                'postcode'           => '1219',
                'financial_address'  => 'Banasree, Rampura',
                'financial_city'     => 'Dhaka',
                'financial_postcode' => '1219',
                'tax_number'         => '21-4330267',
                'state'              => 'Dhaka',
                'country'            => 'Bangladesh',
                'phone'              => '8801700000000',
                'created_at'         => Carbon::now()->subMonth(),
                'updated_at'         => Carbon::now()->subMonth(),
                'permissions'        => json_encode([
                    'view_reports',
                    'view_contact_group',
                    'create_contact_group',
                    'update_contact_group',
                    'delete_contact_group',
                    'view_contact',
                    'create_contact',
                    'update_contact',
                    'delete_contact',
                    'view_numbers',
                    'buy_numbers',
                    'release_numbers',
                    'view_keywords',
                    'buy_keywords',
                    'update_keywords',
                    'release_keywords',
                    'view_sender_id',
                    'create_sender_id',
                    'view_blacklist',
                    'create_blacklist',
                    'delete_blacklist',
                    'sms_campaign_builder',
                    'sms_quick_send',
                    'sms_bulk_messages',
                    'access_backend',
                ]),
                'notifications'      => json_encode([
                    'login'        => 'no',
                    'sender_id'    => 'no',
                    'keyword'      => 'yes',
                    'subscription' => 'yes',
                    'promotion'    => 'no',
                    'profile'      => 'yes',
                ]),
            ]);

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('blacklists')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $blacklists = [
                [
                    'user_id' => 1,
                    'number'  => '8801721970156',
                    'reason'  => null,
                ],
                [
                    'user_id' => 1,
                    'number'  => '8801921970156',
                    'reason'  => strtoupper('stop promotion'),
                ],
                [
                    'user_id' => 1,
                    'number'  => '8801520970156',
                    'reason'  => strtoupper('stop promotion'),
                ],
                [
                    'user_id' => 1,
                    'number'  => '8801781970156',
                    'reason'  => strtoupper('stop promotion'),
                ],
                [
                    'user_id' => 3,
                    'number'  => '8801621970156',
                    'reason'  => 'SPAMMING',
                ],
                [
                    'user_id' => 3,
                    'number'  => '8801721970156',
                    'reason'  => null,
                ],
                [
                    'user_id' => 3,
                    'number'  => '8801821970156',
                    'reason'  => strtoupper('stop promotion'),
                ],
                [
                    'user_id' => 3,
                    'number'  => '8801741970156',
                    'reason'  => null,
                ],
                [
                    'user_id' => 3,
                    'number'  => '8801851970156',
                    'reason'  => strtoupper('stop promotion'),
                ],
            ];

            foreach ($blacklists as $blacklist) {
                Blacklists::create($blacklist);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('currencies')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $currency_data = [
                [
                    'uid'     => uniqid(),
                    'user_id' => 1,
                    'name'    => 'US Dollar',
                    'code'    => 'USD',
                    'format'  => '${PRICE}',
                    'status'  => true,
                ], [
                    'uid'     => uniqid(),
                    'user_id' => 1,
                    'name'    => 'EURO',
                    'code'    => 'EUR',
                    'format'  => '€{PRICE}',
                    'status'  => true,
                ], [
                    'uid'     => uniqid(),
                    'user_id' => 1,
                    'name'    => 'British Pound',
                    'code'    => 'GBP',
                    'format'  => '£{PRICE}',
                    'status'  => true,
                ], [
                    'uid'     => uniqid(),
                    'user_id' => 1,
                    'name'    => 'Japanese Yen',
                    'code'    => 'JPY',
                    'format'  => '¥{PRICE}',
                    'status'  => true,
                ], [
                    'uid'     => uniqid(),
                    'user_id' => 1,
                    'name'    => 'Russian Ruble',
                    'code'    => 'RUB',
                    'format'  => '‎₽{PRICE}',
                    'status'  => true,
                ], [
                    'uid'     => uniqid(),
                    'user_id' => 1,
                    'name'    => 'Vietnam Dong',
                    'code'    => 'VND',
                    'format'  => '{PRICE}₫',
                    'status'  => true,
                ], [
                    'uid'     => uniqid(),
                    'user_id' => 1,
                    'name'    => 'Brazilian Real',
                    'code'    => 'BRL',
                    'format'  => '‎R${PRICE}',
                    'status'  => true,
                ], [
                    'uid'     => uniqid(),
                    'user_id' => 1,
                    'name'    => 'Bangladeshi Taka',
                    'code'    => 'BDT',
                    'format'  => '‎৳{PRICE}',
                    'status'  => true,
                ], [
                    'uid'     => uniqid(),
                    'user_id' => 1,
                    'name'    => 'Canadian Dollar',
                    'code'    => 'CAD',
                    'format'  => '‎C${PRICE}',
                    'status'  => true,
                ], [
                    'uid'     => uniqid(),
                    'user_id' => 1,
                    'name'    => 'Indian rupee',
                    'code'    => 'INR',
                    'format'  => '‎₹{PRICE}',
                    'status'  => true,
                ], [
                    'uid'     => uniqid(),
                    'user_id' => 1,
                    'name'    => 'Nigerian Naira',
                    'code'    => 'CBN',
                    'format'  => '‎₦{PRICE}',
                    'status'  => true,
                ],
            ];

            foreach ($currency_data as $data) {
                Currency::create($data);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('keywords')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $keywords = [
                [
                    'user_id'          => 1,
                    'currency_id'      => 1,
                    'title'            => '50% OFF',
                    'keyword_name'     => '50OFF',
                    'sender_id'        => 'Codeglen',
                    'reply_text'       => 'you will get 50% from our next promotion.',
                    'reply_voice'      => 'you will get 50% from our next promotion.',
                    'price'            => 10,
                    'billing_cycle'    => 'yearly',
                    'frequency_amount' => '1',
                    'frequency_unit'   => 'year',
                    'status'           => 'available',
                ],
                [
                    'user_id'          => 1,
                    'currency_id'      => 2,
                    'title'            => 'CR7',
                    'keyword_name'     => 'CR7',
                    'sender_id'        => 'Codeglen',
                    'reply_text'       => 'Thank you for voting Cristiano Ronaldo.',
                    'reply_voice'      => 'Thank you for voting Cristiano Ronaldo.',
                    'price'            => 10,
                    'billing_cycle'    => 'yearly',
                    'frequency_amount' => '1',
                    'frequency_unit'   => 'year',
                    'validity_date'    => Carbon::now()->add(1, 'year'),
                    'status'           => 'assigned',
                ],
                [
                    'user_id'          => 1,
                    'currency_id'      => 3,
                    'title'            => 'MESSI10',
                    'keyword_name'     => 'MESSI10',
                    'sender_id'        => 'Codeglen',
                    'reply_text'       => 'Thank you for voting Leonel Messi.',
                    'reply_voice'      => 'Thank you for voting Leonel Messi.',
                    'price'            => 10,
                    'billing_cycle'    => 'yearly',
                    'frequency_amount' => '1',
                    'frequency_unit'   => 'year',
                    'validity_date'    => Carbon::yesterday(),
                    'status'           => 'expired',
                ],
                [
                    'user_id'          => 3,
                    'currency_id'      => 1,
                    'title'            => '999',
                    'keyword_name'     => '999',
                    'sender_id'        => 'Codeglen',
                    'reply_text'       => 'You will receive all govt facilities from now.',
                    'reply_voice'      => 'You will receive all govt facilities from now.',
                    'price'            => 10,
                    'billing_cycle'    => 'yearly',
                    'frequency_amount' => '1',
                    'frequency_unit'   => 'year',
                    'status'           => 'assigned',
                ],
                [
                    'user_id'          => 3,
                    'currency_id'      => 1,
                    'title'            => 'PROMO50',
                    'keyword_name'     => 'PROMO50',
                    'sender_id'        => 'Codeglen',
                    'reply_text'       => 'You will get 50% from our next promotion.',
                    'reply_voice'      => 'You will get 50% from our next promotion.',
                    'price'            => 10,
                    'billing_cycle'    => 'yearly',
                    'frequency_amount' => '1',
                    'frequency_unit'   => 'year',
                    'validity_date'    => Carbon::yesterday(),
                    'status'           => 'expired',
                ],
                [
                    'user_id'          => 4,
                    'currency_id'      => 10,
                    'title'            => 'BlackFriday',
                    'keyword_name'     => 'BFOFF50',
                    'sender_id'        => 'Codeglen',
                    'reply_text'       => 'You will get 50% from our next black friday promotion.',
                    'reply_voice'      => 'You will get 50% from our next black friday promotion.',
                    'price'            => 10,
                    'billing_cycle'    => 'yearly',
                    'frequency_amount' => '1',
                    'frequency_unit'   => 'year',
                    'status'           => 'assigned',
                ],
                [
                    'user_id'          => 4,
                    'currency_id'      => 10,
                    'title'            => 'CodeglenDeal',
                    'keyword_name'     => 'CODEGLENDEAL',
                    'sender_id'        => 'Codeglen',
                    'reply_text'       => 'Use voucher CODEGLENDEAL and get 34% off upto INR 120 on orders over INR 349',
                    'reply_voice'      => 'Use voucher CODEGLENDEAL and get 34% off upto INR 120 on orders over INR 349',
                    'price'            => 10,
                    'billing_cycle'    => 'yearly',
                    'frequency_amount' => '1',
                    'frequency_unit'   => 'year',
                    'validity_date'    => Carbon::yesterday(),
                    'status'           => 'expired',
                ],
            ];

            foreach ($keywords as $keyword) {
                Keywords::create($keyword);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('phone_numbers')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $phone_numbers = [
                [
                    'user_id'          => 1,
                    'number'           => '8801721970168',
                    'status'           => 'available',
                    'capabilities'     => json_encode(['sms', 'voice', 'mms', 'whatsapp', 'viber', 'otp']),
                    'price'            => 5,
                    'billing_cycle'    => 'monthly',
                    'frequency_amount' => 1,
                    'frequency_unit'   => 'month',
                    'currency_id'      => 1,
                ],
                [
                    'user_id'          => 1,
                    'number'           => '8801526970168',
                    'status'           => 'assigned',
                    'capabilities'     => json_encode(['sms', 'voice', 'mms', 'whatsapp', 'viber', 'otp']),
                    'price'            => 5,
                    'billing_cycle'    => 'monthly',
                    'frequency_amount' => 1,
                    'frequency_unit'   => 'month',
                    'currency_id'      => 1,
                    'validity_date'    => Carbon::now()->addMonth(),
                ],
                [
                    'user_id'          => 1,
                    'number'           => '8801626980168',
                    'status'           => 'expired',
                    'capabilities'     => json_encode(['sms', 'voice', 'mms', 'whatsapp', 'viber', 'otp']),
                    'price'            => 5,
                    'billing_cycle'    => 'monthly',
                    'frequency_amount' => 1,
                    'frequency_unit'   => 'month',
                    'currency_id'      => 1,
                ],
                [
                    'user_id'          => 3,
                    'number'           => '8801921970168',
                    'status'           => 'assigned',
                    'capabilities'     => json_encode(['sms', 'voice', 'mms', 'whatsapp', 'viber', 'otp']),
                    'price'            => 5,
                    'billing_cycle'    => 'monthly',
                    'frequency_amount' => 1,
                    'frequency_unit'   => 'month',
                    'currency_id'      => 1,
                    'validity_date'    => Carbon::now()->addMonth(),
                ],
                [
                    'user_id'          => 3,
                    'number'           => '8801621970168',
                    'status'           => 'expired',
                    'price'            => 5,
                    'capabilities'     => json_encode(['voice', 'mms', 'whatsapp']),
                    'billing_cycle'    => 'custom',
                    'frequency_amount' => 6,
                    'frequency_unit'   => 'month',
                    'currency_id'      => 3,
                ],
                [
                    'user_id'          => 4,
                    'number'           => '8801521970168',
                    'status'           => 'assigned',
                    'price'            => 5,
                    'capabilities'     => json_encode(['sms', 'whatsapp', 'otp']),
                    'billing_cycle'    => 'yearly',
                    'frequency_amount' => 1,
                    'frequency_unit'   => 'year',
                    'currency_id'      => 10,
                    'validity_date'    => Carbon::now()->addMonth(),
                ],
                [
                    'user_id'          => 4,
                    'number'           => '8801821970168',
                    'status'           => 'assigned',
                    'price'            => 5,
                    'capabilities'     => json_encode(['sms', 'otp']),
                    'billing_cycle'    => 'monthly',
                    'frequency_amount' => 6,
                    'frequency_unit'   => 'month',
                    'currency_id'      => 10,
                    'validity_date'    => Carbon::now()->add('month', 6),
                ],
                [
                    'user_id'          => 4,
                    'number'           => '8801920000168',
                    'status'           => 'expired',
                    'price'            => 5,
                    'capabilities'     => json_encode(['sms', 'voice', 'mms', 'whatsapp', 'viber', 'otp']),
                    'billing_cycle'    => 'custom',
                    'frequency_amount' => 6,
                    'frequency_unit'   => 'month',
                    'currency_id'      => 10,
                ],
            ];

            foreach ($phone_numbers as $number) {
                PhoneNumbers::create($number);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('senderid')->truncate();
            DB::table('senderid_plans')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $sender_ids = [
                [
                    'user_id'          => 1,
                    'sender_id'        => 'USMS',
                    'status'           => 'active',
                    'price'            => 5,
                    'billing_cycle'    => 'yearly',
                    'frequency_amount' => '1',
                    'frequency_unit'   => 'year',
                    'currency_id'      => 1,
                    'validity_date'    => Carbon::now()->addYear(),
                ],
                [
                    'user_id'          => 1,
                    'sender_id'        => 'Apple',
                    'status'           => 'payment_required',
                    'price'            => 5,
                    'billing_cycle'    => 'yearly',
                    'frequency_amount' => '1',
                    'frequency_unit'   => 'year',
                    'currency_id'      => 1,
                    'validity_date'    => Carbon::now()->addYear(),
                ],
                [
                    'user_id'          => 1,
                    'sender_id'        => 'Info',
                    'status'           => 'expired',
                    'price'            => 5,
                    'billing_cycle'    => 'yearly',
                    'frequency_amount' => '1',
                    'frequency_unit'   => 'year',
                    'currency_id'      => 1,
                    'validity_date'    => Carbon::now()->subDay(),
                ],
                [
                    'user_id'          => 1,
                    'sender_id'        => 'Police',
                    'status'           => 'block',
                    'price'            => 5,
                    'billing_cycle'    => 'monthly',
                    'frequency_amount' => 1,
                    'frequency_unit'   => 'month',
                    'currency_id'      => 1,
                    'validity_date'    => Carbon::now()->addMonth(),
                ],
                [
                    'user_id'          => 1,
                    'sender_id'        => 'SHAMIM',
                    'status'           => 'pending',
                    'price'            => 5,
                    'billing_cycle'    => 'custom',
                    'frequency_amount' => 6,
                    'frequency_unit'   => 'month',
                    'currency_id'      => 1,
                    'validity_date'    => Carbon::now()->add('month', 6),
                ],
                [
                    'user_id'          => 1,
                    'sender_id'        => 'Codeglen',
                    'status'           => 'active',
                    'price'            => 5,
                    'billing_cycle'    => 'monthly',
                    'frequency_amount' => 1,
                    'frequency_unit'   => 'month',
                    'currency_id'      => 1,
                    'validity_date'    => Carbon::now()->addMonth(),
                ],
                [
                    'user_id'          => 3,
                    'sender_id'        => 'Codeglen',
                    'status'           => 'active',
                    'price'            => 5,
                    'billing_cycle'    => 'yearly',
                    'frequency_amount' => '1',
                    'frequency_unit'   => 'year',
                    'currency_id'      => 1,
                    'validity_date'    => Carbon::now()->addYear(),
                ],
                [
                    'user_id'          => 3,
                    'sender_id'        => 'Apple',
                    'status'           => 'payment_required',
                    'price'            => 5,
                    'billing_cycle'    => 'yearly',
                    'frequency_amount' => '1',
                    'frequency_unit'   => 'year',
                    'currency_id'      => 1,
                    'validity_date'    => Carbon::now()->addYear(),
                ],
                [
                    'user_id'          => 3,
                    'sender_id'        => 'Expo',
                    'status'           => 'expired',
                    'price'            => 5,
                    'billing_cycle'    => 'yearly',
                    'frequency_amount' => '1',
                    'frequency_unit'   => 'year',
                    'currency_id'      => 1,
                    'validity_date'    => Carbon::now()->subDay(),
                ],
                [
                    'user_id'          => 3,
                    'sender_id'        => 'NDP',
                    'status'           => 'block',
                    'price'            => 5,
                    'billing_cycle'    => 'monthly',
                    'frequency_amount' => 1,
                    'frequency_unit'   => 'month',
                    'currency_id'      => 1,
                    'validity_date'    => Carbon::now()->addMonth(),
                ],
                [
                    'user_id'          => 3,
                    'sender_id'        => 'Saad',
                    'status'           => 'pending',
                    'price'            => 5,
                    'billing_cycle'    => 'custom',
                    'frequency_amount' => 6,
                    'frequency_unit'   => 'month',
                    'currency_id'      => 1,
                    'validity_date'    => Carbon::now()->add('month', 6),
                ],
                [
                    'user_id'          => 3,
                    'sender_id'        => 'CoderPixel',
                    'status'           => 'active',
                    'price'            => 5,
                    'billing_cycle'    => 'monthly',
                    'frequency_amount' => 1,
                    'frequency_unit'   => 'month',
                    'currency_id'      => 1,
                    'validity_date'    => Carbon::now()->addMonth(),
                ],
                [
                    'user_id'          => 4,
                    'sender_id'        => 'DLT',
                    'status'           => 'active',
                    'price'            => 5,
                    'billing_cycle'    => 'monthly',
                    'frequency_amount' => 1,
                    'frequency_unit'   => 'month',
                    'currency_id'      => 10,
                    'validity_date'    => Carbon::now()->addMonth(),
                    'description'      => 'Description for DLT',
                    'entity_id'        => '0x4a8d2c8Fd8e18F5E46d5b2e9D0B25B43eCD0fC21',
                ],
                [
                    'user_id'          => 4,
                    'sender_id'        => 'DLTTRAI',
                    'status'           => 'pending',
                    'price'            => 5,
                    'billing_cycle'    => 'monthly',
                    'frequency_amount' => 1,
                    'frequency_unit'   => 'month',
                    'currency_id'      => 10,
                    'validity_date'    => Carbon::now()->addMonth(),
                    'description'      => 'Description for DLT',
                    'entity_id'        => '0x4a8d2c8Fd8e18F5E46d5b2e9D0B25B43eCD0fC21',
                ],
                [
                    'user_id'          => 4,
                    'sender_id'        => 'TRAI',
                    'status'           => 'expired',
                    'price'            => 5,
                    'billing_cycle'    => 'monthly',
                    'frequency_amount' => 1,
                    'frequency_unit'   => 'month',
                    'currency_id'      => 10,
                    'validity_date'    => Carbon::now()->subDay(),
                    'description'      => 'Description for TRAI',
                    'entity_id'        => '0x4a8d2c8Fd8e18F5E46d5b2e9D0B25B43eC67fC21',
                ],
            ];

            foreach ($sender_ids as $senderId) {
                Senderid::create($senderId);
            }
            $sender_ids_plan = [
                [
                    'price'            => 5,
                    'billing_cycle'    => 'monthly',
                    'frequency_amount' => '1',
                    'frequency_unit'   => 'month',
                    'currency_id'      => 1,
                ],
                [
                    'price'            => 12,
                    'billing_cycle'    => 'custom',
                    'frequency_amount' => '3',
                    'frequency_unit'   => 'month',
                    'currency_id'      => 1,
                ],
                [
                    'price'            => 20,
                    'billing_cycle'    => 'custom',
                    'frequency_amount' => '6',
                    'frequency_unit'   => 'month',
                    'currency_id'      => 1,
                ],
                [
                    'price'            => 35,
                    'billing_cycle'    => 'yearly',
                    'frequency_amount' => '1',
                    'frequency_unit'   => 'year',
                    'currency_id'      => 1,
                ],
                [
                    'price'            => 65,
                    'billing_cycle'    => 'custom',
                    'frequency_amount' => '2',
                    'frequency_unit'   => 'year',
                    'currency_id'      => 1,
                ],
                [
                    'price'            => 115,
                    'billing_cycle'    => 'custom',
                    'frequency_amount' => '3',
                    'frequency_unit'   => 'year',
                    'currency_id'      => 1,
                ],
            ];

            foreach ($sender_ids_plan as $plan) {
                SenderidPlan::create($plan);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('sending_servers')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $sendingServersRepo = new EloquentSendingServerRepository(new SendingServer());
            $sendingServers     = collect($sendingServersRepo->allSendingServer());

            foreach ($sendingServers->reverse() as $server) {
                $server['user_id'] = 1;
                $server['status']  = true;

                SendingServer::create($server);
            }

            SpamWord::truncate();

            $spam_words = [
                [
                    'word' => 'POLICE',
                ],
                [
                    'word' => 'RAB',
                ],
                [
                    'word' => 'GOVT',
                ],
                [
                    'word' => 'NYPD',
                ],
                [
                    'word' => 'CIA',
                ],
                [
                    'word' => 'NDP',
                ],
                [
                    'word' => 'FBI',
                ],
            ];

            foreach ($spam_words as $word) {
                SpamWord::create($word);
            }


            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('plans')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $plans = [
                [
                    'user_id'              => 1,
                    'currency_id'          => 1,
                    'name'                 => 'Free',
                    'description'          => 'A simple start for everyone',
                    'price'                => '0',
                    'billing_cycle'        => 'monthly',
                    'frequency_amount'     => 1,
                    'frequency_unit'       => 'month',
                    'options'              => '{"sms_max":"5","list_max":"5","subscriber_max":"500","subscriber_per_list_max":"100","segment_per_list_max":"3","billing_cycle":"monthly","sending_limit":"50000_per_hour","sending_quota":"100","sending_quota_time":"1","sending_quota_time_unit":"hour","max_process":"1","list_import":"yes","list_export":"yes","api_access":"no","create_sub_account":"no","delete_sms_history":"no","add_previous_balance":"no","sender_id_verification":"yes","send_spam_message":"no"}',
                    'status'               => true,
                    'tax_billing_required' => false,
                ],
                [
                    'user_id'              => 1,
                    'currency_id'          => 1,
                    'name'                 => 'Standard',
                    'description'          => 'For small to medium businesses',
                    'price'                => '49',
                    'billing_cycle'        => 'monthly',
                    'frequency_amount'     => 1,
                    'frequency_unit'       => 'month',
                    'is_popular'           => true,
                    'options'              => '{"sms_max":"6000","list_max":"-1","subscriber_max":"-1","subscriber_per_list_max":"5000","segment_per_list_max":"3","billing_cycle":"monthly","sending_limit":"50000_per_hour","sending_quota":"600","sending_quota_time":"1","sending_quota_time_unit":"minute","max_process":"1","list_import":"yes","list_export":"yes","api_access":"yes","create_sub_account":"yes","delete_sms_history":"yes","add_previous_balance":"yes","sender_id_verification":"yes","send_spam_message":"no"}',
                    'status'               => true,
                    'tax_billing_required' => true,
                ],
                [
                    'user_id'              => 1,
                    'currency_id'          => 10,
                    'name'                 => 'DLT Enabled',
                    'description'          => 'TRAI - Distributed Ledger Technology (DLT)',
                    'price'                => '1300',
                    'billing_cycle'        => 'yearly',
                    'frequency_amount'     => 1,
                    'frequency_unit'       => 'year',
                    'options'              => '{"sms_max":"5000","list_max":"-1","subscriber_max":"-1","subscriber_per_list_max":"5000","segment_per_list_max":"3","billing_cycle":"monthly","sending_limit":"50000_per_hour","sending_quota":"600","sending_quota_time":"1","sending_quota_time_unit":"minute","max_process":"1","list_import":"yes","list_export":"yes","api_access":"yes","create_sub_account":"yes","delete_sms_history":"yes","add_previous_balance":"yes","sender_id_verification":"yes","send_spam_message":"no"}',
                    'status'               => true,
                    'is_dlt'               => true,
                    'tax_billing_required' => true,
                ],
                [
                    'user_id'              => 1,
                    'currency_id'          => 1,
                    'name'                 => 'Enterprise',
                    'description'          => 'Solution for big organizations',
                    'price'                => '99',
                    'billing_cycle'        => 'monthly',
                    'frequency_amount'     => 1,
                    'frequency_unit'       => 'month',
                    'options'              => '{"sms_max":"15000","list_max":"-1","subscriber_max":"-1","subscriber_per_list_max":"10000","segment_per_list_max":"-1","billing_cycle":"monthly","sending_limit":"50000_per_hour","sending_quota":"600","sending_quota_time":"1","sending_quota_time_unit":"minute","max_process":"3","list_import":"yes","list_export":"yes","api_access":"yes","create_sub_account":"yes","delete_sms_history":"yes","add_previous_balance":"yes","sender_id_verification":"yes","send_spam_message":"yes"}',
                    'status'               => true,
                    'tax_billing_required' => true,
                ],
            ];

            foreach ($plans as $plan) {
                Plan::create($plan);
            }


            PlanSendingCreditPrice::truncate();

            $plans = [
                1 => 'free',
                2 => 'standard',
                4 => 'premium',
            ];

            foreach ($plans as $planId => $planType) {
                $planCreditPricing = [];

                for ($i = 1; $i <= 8; $i++) {
                    $unitFrom = ($i - 1) * 150000 + 1;
                    $unitTo   = $i * 150000;

                    $planCreditPricing[] = [
                        'uid'             => uniqid(),
                        'plan_id'         => $planId,
                        'unit_from'       => $unitFrom,
                        'unit_to'         => $unitTo,
                        'per_credit_cost' => 0.0079 - ($i * 0.0002), // Example calculation, adjust as needed
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }

                PlanSendingCreditPrice::insert($planCreditPricing);
            }


            $dltCreditPricing = [
                [
                    'uid'             => uniqid(),
                    'plan_id'         => 3,
                    'unit_from'       => 1,
                    'unit_to'         => 2999,
                    'per_credit_cost' => 0.24,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
                [
                    'uid'             => uniqid(),
                    'plan_id'         => 3,
                    'unit_from'       => 3000,
                    'unit_to'         => 9999,
                    'per_credit_cost' => 0.22,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
                [
                    'uid'             => uniqid(),
                    'plan_id'         => 3,
                    'unit_from'       => 10000,
                    'unit_to'         => 24999,
                    'per_credit_cost' => 0.19,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
                [
                    'uid'             => uniqid(),
                    'plan_id'         => 3,
                    'unit_from'       => 25000,
                    'unit_to'         => 49999,
                    'per_credit_cost' => 0.18,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
                [
                    'uid'             => uniqid(),
                    'plan_id'         => 3,
                    'unit_from'       => 50000,
                    'unit_to'         => 99999,
                    'per_credit_cost' => 0.17,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
                [
                    'uid'             => uniqid(),
                    'plan_id'         => 3,
                    'unit_from'       => 100000,
                    'unit_to'         => 499999,
                    'per_credit_cost' => 0.16,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
                [
                    'uid'             => uniqid(),
                    'plan_id'         => 3,
                    'unit_from'       => 500000,
                    'unit_to'         => 999999,
                    'per_credit_cost' => 0.14,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
                [
                    'uid'             => uniqid(),
                    'plan_id'         => 3,
                    'unit_from'       => 1000000,
                    'unit_to'         => 9999999,
                    'per_credit_cost' => 0.13,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
            ];

            PlanSendingCreditPrice::insert($dltCreditPricing);

            $plan_coverage = [
                /*Default/Standard or Plan 2*/
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 16,
                    'plan_id'                 => 2,
                    'sending_server'          => 219,
                    'voice_sending_server'    => 219,
                    'mms_sending_server'      => 219,
                    'whatsapp_sending_server' => 219,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 86,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"2","receive_voice_sms":"1","mms_sms":"3","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 92,
                    'plan_id'                 => 2,
                    'sending_server'          => 218,
                    'voice_sending_server'    => 212,
                    'mms_sending_server'      => 211,
                    'whatsapp_sending_server' => 46,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 87,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 147,
                    'plan_id'                 => 2,
                    'sending_server'          => 217,
                    'voice_sending_server'    => 211,
                    'mms_sending_server'      => 209,
                    'whatsapp_sending_server' => 81,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 29,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 193,
                    'plan_id'                 => 2,
                    'sending_server'          => 216,
                    'voice_sending_server'    => 199,
                    'mms_sending_server'      => 187,
                    'whatsapp_sending_server' => 74,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 27,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 220,
                    'plan_id'                 => 2,
                    'sending_server'          => 213,
                    'voice_sending_server'    => 198,
                    'mms_sending_server'      => 196,
                    'whatsapp_sending_server' => 58,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 29,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 221,
                    'plan_id'                 => 2,
                    'sending_server'          => 211,
                    'voice_sending_server'    => 196,
                    'mms_sending_server'      => 198,
                    'whatsapp_sending_server' => 46,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 27,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 122,
                    'plan_id'                 => 2,
                    'sending_server'          => 206,
                    'voice_sending_server'    => 187,
                    'mms_sending_server'      => 16,
                    'whatsapp_sending_server' => 41,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 86,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 146,
                    'plan_id'                 => 2,
                    'sending_server'          => 207,
                    'voice_sending_server'    => 209,
                    'mms_sending_server'      => 212,
                    'whatsapp_sending_server' => 19,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 86,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 192,
                    'plan_id'                 => 2,
                    'sending_server'          => 212,
                    'voice_sending_server'    => 167,
                    'mms_sending_server'      => 199,
                    'whatsapp_sending_server' => 12,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 29,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 190,
                    'plan_id'                 => 2,
                    'sending_server'          => 219,
                    'voice_sending_server'    => 219,
                    'mms_sending_server'      => 219,
                    'whatsapp_sending_server' => 219,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 86,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"0","receive_voice_sms":"0","mms_sms":"0","receive_mms_sms":"0","whatsapp_sms":"0","receive_whatsapp_sms":"0","viber_sms":"0","receive_viber_sms":"0","otp_sms":"0","receive_otp_sms":"0"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],

                /*Free Plan ID 1*/
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 16,
                    'plan_id'                 => 1,
                    'sending_server'          => 219,
                    'voice_sending_server'    => 219,
                    'mms_sending_server'      => 219,
                    'whatsapp_sending_server' => 219,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 86,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"2","receive_voice_sms":"1","mms_sms":"3","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 92,
                    'plan_id'                 => 1,
                    'sending_server'          => 218,
                    'voice_sending_server'    => 212,
                    'mms_sending_server'      => 211,
                    'whatsapp_sending_server' => 46,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 87,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 193,
                    'plan_id'                 => 1,
                    'sending_server'          => 216,
                    'voice_sending_server'    => 199,
                    'mms_sending_server'      => 187,
                    'whatsapp_sending_server' => 74,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 27,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 220,
                    'plan_id'                 => 1,
                    'sending_server'          => 213,
                    'voice_sending_server'    => 198,
                    'mms_sending_server'      => 196,
                    'whatsapp_sending_server' => 58,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 29,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 146,
                    'plan_id'                 => 1,
                    'sending_server'          => 207,
                    'voice_sending_server'    => 209,
                    'mms_sending_server'      => 212,
                    'whatsapp_sending_server' => 19,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 86,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 192,
                    'plan_id'                 => 1,
                    'sending_server'          => 212,
                    'voice_sending_server'    => 167,
                    'mms_sending_server'      => 199,
                    'whatsapp_sending_server' => 12,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 29,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 190,
                    'plan_id'                 => 1,
                    'sending_server'          => 219,
                    'voice_sending_server'    => 219,
                    'mms_sending_server'      => 219,
                    'whatsapp_sending_server' => 219,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 86,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"0","receive_voice_sms":"0","mms_sms":"0","receive_mms_sms":"0","whatsapp_sms":"0","receive_whatsapp_sms":"0","viber_sms":"0","receive_viber_sms":"0","otp_sms":"0","receive_otp_sms":"0"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],

                /*Enterprise Plan ID 4*/
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 16,
                    'plan_id'                 => 4,
                    'sending_server'          => 219,
                    'voice_sending_server'    => 219,
                    'mms_sending_server'      => 219,
                    'whatsapp_sending_server' => 219,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 86,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"0","receive_plain_sms":"0","voice_sms":"0","receive_voice_sms":"0","mms_sms":"3","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 92,
                    'plan_id'                 => 4,
                    'sending_server'          => 218,
                    'voice_sending_server'    => 212,
                    'mms_sending_server'      => 211,
                    'whatsapp_sending_server' => 46,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 87,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 193,
                    'plan_id'                 => 4,
                    'sending_server'          => 216,
                    'voice_sending_server'    => 199,
                    'mms_sending_server'      => 187,
                    'whatsapp_sending_server' => 74,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 27,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 220,
                    'plan_id'                 => 4,
                    'sending_server'          => 213,
                    'voice_sending_server'    => 198,
                    'mms_sending_server'      => 196,
                    'whatsapp_sending_server' => 58,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 29,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 146,
                    'plan_id'                 => 4,
                    'sending_server'          => 207,
                    'voice_sending_server'    => 209,
                    'mms_sending_server'      => 212,
                    'whatsapp_sending_server' => 19,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 86,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 192,
                    'plan_id'                 => 4,
                    'sending_server'          => 212,
                    'voice_sending_server'    => 167,
                    'mms_sending_server'      => 199,
                    'whatsapp_sending_server' => 12,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 29,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"1","receive_voice_sms":"1","mms_sms":"1","receive_mms_sms":"1","whatsapp_sms":"1","receive_whatsapp_sms":"1","viber_sms":"1","receive_viber_sms":"1","otp_sms":"1","receive_otp_sms":"1"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 190,
                    'plan_id'                 => 4,
                    'sending_server'          => 219,
                    'voice_sending_server'    => 219,
                    'mms_sending_server'      => 219,
                    'whatsapp_sending_server' => 219,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 86,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"0","receive_voice_sms":"0","mms_sms":"0","receive_mms_sms":"0","whatsapp_sms":"0","receive_whatsapp_sms":"0","viber_sms":"0","receive_viber_sms":"0","otp_sms":"0","receive_otp_sms":"0"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],

                /*DLT Plan ID 3*/
                [
                    'uid'                     => uniqid(),
                    'country_id'              => 92,
                    'plan_id'                 => 3,
                    'sending_server'          => 206,
                    'voice_sending_server'    => 219,
                    'mms_sending_server'      => 219,
                    'whatsapp_sending_server' => 219,
                    'viber_sending_server'    => 30,
                    'otp_sending_server'      => 86,
                    'status'                  => true,
                    'options'                 => '{"plain_sms":"1","receive_plain_sms":"1","voice_sms":"0","receive_voice_sms":"0","mms_sms":"0","receive_mms_sms":"0","whatsapp_sms":"0","receive_whatsapp_sms":"0","viber_sms":"0","receive_viber_sms":"0","otp_sms":"0","receive_otp_sms":"0"}',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ],

            ];

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('plans_coverage_countries')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            PlansCoverageCountries::insert($plan_coverage);

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('subscriptions')->truncate();
            DB::table('subscription_logs')->truncate();
            DB::table('subscription_transactions')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $subscriptions = [
                [
                    'uid'                    => uniqid(),
                    'user_id'                => 1,
                    'plan_id'                => 2,
                    'options'                => '{"credit_warning":true,"credit":"100","credit_notify":"both","subscription_warning":true,"subscription_notify":"both"}',
                    'start_at'               => now(),
                    'status'                 => 'active',
                    'end_period_last_days'   => 10,
                    'current_period_ends_at' => Carbon::now()->addMonth(),
                    'end_at'                 => null,
                    'end_by'                 => null,
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ],
                [
                    'uid'                    => uniqid(),
                    'user_id'                => 3,
                    'plan_id'                => 2,
                    'options'                => '{"credit_warning":true,"credit":"100","credit_notify":"both","subscription_warning":true,"subscription_notify":"both"}',
                    'start_at'               => now(),
                    'status'                 => 'active',
                    'end_period_last_days'   => 10,
                    'current_period_ends_at' => Carbon::now()->addMonth(),
                    'end_at'                 => null,
                    'end_by'                 => null,
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ],
                [
                    'uid'                    => uniqid(),
                    'user_id'                => 4,
                    'plan_id'                => 3,
                    'start_at'               => now(),
                    'status'                 => 'active',
                    'options'                => '{"credit_warning":true,"credit":"100","credit_notify":"both","subscription_warning":true,"subscription_notify":"both"}',
                    'end_period_last_days'   => 10,
                    'current_period_ends_at' => Carbon::now()->addYear(),
                    'end_at'                 => null,
                    'end_by'                 => null,
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ],
                [
                    'uid'                    => uniqid(),
                    'user_id'                => 5,
                    'plan_id'                => 1,
                    'start_at'               => now(),
                    'status'                 => 'active',
                    'options'                => '{"credit_warning":true,"credit":"100","credit_notify":"both","subscription_warning":true,"subscription_notify":"both"}',
                    'end_period_last_days'   => 10,
                    'current_period_ends_at' => Carbon::now()->addMonth(),
                    'end_at'                 => null,
                    'end_by'                 => null,
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ],
                [
                    'uid'                    => uniqid(),
                    'user_id'                => 6,
                    'plan_id'                => 4,
                    'start_at'               => now(),
                    'status'                 => 'active',
                    'options'                => '{"credit_warning":true,"credit":"100","credit_notify":"both","subscription_warning":true,"subscription_notify":"both"}',
                    'end_period_last_days'   => 10,
                    'current_period_ends_at' => Carbon::now()->addMonth(),
                    'end_at'                 => null,
                    'end_by'                 => null,
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ],
            ];

            Subscription::insert($subscriptions);

            $subscriptionLogs = [
                [
                    'subscription_id' => 1,
                    'type'            => 'admin_plan_assigned',
                    'data'            => '{"plan":"Standard","price":"\u00a3500"}',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
                [
                    'subscription_id' => 2,
                    'type'            => 'admin_plan_assigned',
                    'data'            => '{"plan":"Premium","price":"$5,000"}',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
            ];

            SubscriptionLog::insert($subscriptionLogs);

            $subscriptionTransaction = [
                [
                    'subscription_id' => 1,
                    'title'           => 'Subscribed to Standard plan',
                    'type'            => 'subscribe',
                    'status'          => 'success',
                    'amount'          => '£500',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
                [
                    'subscription_id' => 2,
                    'title'           => 'Subscribed to Premium plan',
                    'type'            => 'subscribe',
                    'status'          => 'success',
                    'amount'          => '$5,000',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
            ];

            SubscriptionTransaction::insert($subscriptionTransaction);


            //invoice
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('invoices')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $invoices = [

                /*User ID 1*/
                [
                    'uid'            => uniqid(),
                    'user_id'        => 1,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'senderid',
                    'description'    => 'Payment for Sender ID Apple',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(3),
                    'updated_at'     => Carbon::now()->subDays(3),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 1,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'keyword',
                    'description'    => 'Payment for keyword CR7',
                    'transaction_id' => 'pi_1Id6n9Jer' . uniqid(),
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(3),
                    'updated_at'     => Carbon::now()->subDays(3),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 1,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'keyword',
                    'description'    => 'Payment for keyword MESSI10',
                    'transaction_id' => 'pi_1Id6n9Jer' . uniqid(),
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(2),
                    'updated_at'     => Carbon::now()->subDays(2),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 1,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 500,
                    'type'           => 'subscription',
                    'description'    => 'Payment for subscription Premium',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(2),
                    'updated_at'     => Carbon::now()->subDays(2),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 1,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 500,
                    'type'           => 'subscription',
                    'description'    => 'Payment for subscription Standard',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(2),
                    'updated_at'     => Carbon::now()->subDays(2),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 1,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'number',
                    'description'    => 'Payment for number',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(2),
                    'updated_at'     => Carbon::now()->subDays(2),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 1,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'senderid',
                    'description'    => 'Payment for Sender ID Info',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(2),
                    'updated_at'     => Carbon::now()->subDays(2),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 1,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'keyword',
                    'description'    => 'Payment for Keyword Apple',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDay(),
                    'updated_at'     => Carbon::now()->subDay(),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 1,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'senderid',
                    'description'    => 'Payment for Sender ID Codeglen',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDay(),
                    'updated_at'     => Carbon::now()->subDay(),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 1,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'senderid',
                    'description'    => 'Payment for Sender ID USMS',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDay(),
                    'updated_at'     => Carbon::now()->subDay(),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 1,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'senderid',
                    'description'    => 'Payment for Sender ID SHAMIM',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDay(),
                    'updated_at'     => Carbon::now()->subDay(),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 1,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'number',
                    'description'    => 'Payment for Number 88014754789',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now(),
                    'updated_at'     => Carbon::now(),
                ],

                /*User ID 3*/

                [
                    'uid'            => uniqid(),
                    'user_id'        => 3,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'senderid',
                    'description'    => 'Payment for Sender ID Apple',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(3),
                    'updated_at'     => Carbon::now()->subDays(3),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 3,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'keyword',
                    'description'    => 'Payment for keyword CR7',
                    'transaction_id' => 'pi_1Id6n9Jer' . uniqid(),
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(3),
                    'updated_at'     => Carbon::now()->subDays(3),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 3,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'keyword',
                    'description'    => 'Payment for keyword MESSI10',
                    'transaction_id' => 'pi_1Id6n9Jer' . uniqid(),
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(2),
                    'updated_at'     => Carbon::now()->subDays(2),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 3,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 500,
                    'type'           => 'subscription',
                    'description'    => 'Payment for subscription Premium',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(2),
                    'updated_at'     => Carbon::now()->subDays(2),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 3,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 500,
                    'type'           => 'subscription',
                    'description'    => 'Payment for subscription Standard',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(2),
                    'updated_at'     => Carbon::now()->subDays(2),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 3,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'number',
                    'description'    => 'Payment for number',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(2),
                    'updated_at'     => Carbon::now()->subDays(2),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 3,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'senderid',
                    'description'    => 'Payment for Sender ID Info',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(2),
                    'updated_at'     => Carbon::now()->subDays(2),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 3,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'keyword',
                    'description'    => 'Payment for Keyword Apple',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDay(),
                    'updated_at'     => Carbon::now()->subDay(),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 3,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'senderid',
                    'description'    => 'Payment for Sender ID Codeglen',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDay(),
                    'updated_at'     => Carbon::now()->subDay(),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 3,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'senderid',
                    'description'    => 'Payment for Sender ID USMS',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDay(),
                    'updated_at'     => Carbon::now()->subDay(),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 3,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'senderid',
                    'description'    => 'Payment for Sender ID SHAMIM',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDay(),
                    'updated_at'     => Carbon::now()->subDay(),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 3,
                    'currency_id'    => 1,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'number',
                    'description'    => 'Payment for Number 88014754789',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now(),
                    'updated_at'     => Carbon::now(),
                ],

                /*User ID 4*/

                [
                    'uid'            => uniqid(),
                    'user_id'        => 4,
                    'currency_id'    => 10,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'senderid',
                    'description'    => 'Payment for Sender ID Apple',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(3),
                    'updated_at'     => Carbon::now()->subDays(3),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 4,
                    'currency_id'    => 10,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'keyword',
                    'description'    => 'Payment for keyword CR7',
                    'transaction_id' => 'pi_1Id6n9Jer' . uniqid(),
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(3),
                    'updated_at'     => Carbon::now()->subDays(3),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 4,
                    'currency_id'    => 10,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'keyword',
                    'description'    => 'Payment for keyword MESSI10',
                    'transaction_id' => 'pi_1Id6n9Jer' . uniqid(),
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(2),
                    'updated_at'     => Carbon::now()->subDays(2),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 4,
                    'currency_id'    => 10,
                    'payment_method' => 3,
                    'amount'         => 500,
                    'type'           => 'subscription',
                    'description'    => 'Payment for subscription Premium',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(2),
                    'updated_at'     => Carbon::now()->subDays(2),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 4,
                    'currency_id'    => 10,
                    'payment_method' => 3,
                    'amount'         => 500,
                    'type'           => 'subscription',
                    'description'    => 'Payment for subscription Standard',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(2),
                    'updated_at'     => Carbon::now()->subDays(2),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 4,
                    'currency_id'    => 10,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'number',
                    'description'    => 'Payment for number',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(2),
                    'updated_at'     => Carbon::now()->subDays(2),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 4,
                    'currency_id'    => 10,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'senderid',
                    'description'    => 'Payment for Sender ID Info',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDays(2),
                    'updated_at'     => Carbon::now()->subDays(2),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 4,
                    'currency_id'    => 10,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'keyword',
                    'description'    => 'Payment for Keyword Apple',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDay(),
                    'updated_at'     => Carbon::now()->subDay(),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 4,
                    'currency_id'    => 10,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'senderid',
                    'description'    => 'Payment for Sender ID Codeglen',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDay(),
                    'updated_at'     => Carbon::now()->subDay(),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 4,
                    'currency_id'    => 10,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'senderid',
                    'description'    => 'Payment for Sender ID USMS',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDay(),
                    'updated_at'     => Carbon::now()->subDay(),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 4,
                    'currency_id'    => 10,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'senderid',
                    'description'    => 'Payment for Sender ID SHAMIM',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now()->subDay(),
                    'updated_at'     => Carbon::now()->subDay(),
                ],
                [
                    'uid'            => uniqid(),
                    'user_id'        => 4,
                    'currency_id'    => 10,
                    'payment_method' => 3,
                    'amount'         => 50,
                    'type'           => 'number',
                    'description'    => 'Payment for Number 88014754789',
                    'transaction_id' => 'pi_1Id6n9JerTkfRDz2sMhOqnNS',
                    'status'         => 'paid',
                    'created_at'     => Carbon::now(),
                    'updated_at'     => Carbon::now(),
                ],
            ];

            Invoices::insert($invoices);

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('contact_groups')->truncate();
            DB::table('contact_groups_optin_keywords')->truncate();
            DB::table('contact_groups_optout_keywords')->truncate();
            DB::table('contact_group_fields')->truncate();
            DB::table('contact_group_field_options')->truncate();
            DB::table('contacts')->truncate();
            DB::table('contacts_custom_field')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            //contact groups
            $contact_groups = [
                /*Customer ID 1*/
                [
                    'customer_id'              => 1,
                    'name'                     => 'BlackFriday',
                    'sender_id'                => 'USMS',
                    'send_welcome_sms'         => true,
                    'unsubscribe_notification' => true,
                    'send_keyword_message'     => true,
                    'status'                   => true,
                    'cache'                    => json_encode([
                        'SubscribersCount'  => 95,
                        'TotalSubscribers'  => 100,
                        'UnsubscribesCount' => 5,
                    ]),

                ],
                [
                    'customer_id'              => 1,
                    'name'                     => 'CyberMonday',
                    'sender_id'                => '8801526970168',
                    'send_welcome_sms'         => true,
                    'unsubscribe_notification' => true,
                    'send_keyword_message'     => false,
                    'status'                   => true,
                    'cache'                    => json_encode([
                        'SubscribersCount'  => 100,
                        'TotalSubscribers'  => 100,
                        'UnsubscribesCount' => 0,
                    ]),
                ],
                [
                    'customer_id'              => 1,
                    'name'                     => 'Codeglen',
                    'sender_id'                => 'Codeglen',
                    'send_welcome_sms'         => true,
                    'unsubscribe_notification' => true,
                    'send_keyword_message'     => false,
                    'status'                   => true,
                    'cache'                    => json_encode([
                        'SubscribersCount'  => 9,
                        'TotalSubscribers'  => 10,
                        'UnsubscribesCount' => 1,
                    ]),
                ],

                /*Customer ID 3*/

                [
                    'customer_id'              => 3,
                    'name'                     => 'MonthlyPromotion',
                    'sender_id'                => 'Codeglen',
                    'send_welcome_sms'         => true,
                    'unsubscribe_notification' => true,
                    'send_keyword_message'     => true,
                    'status'                   => true,
                    'cache'                    => json_encode([
                        'SubscribersCount'  => 95,
                        'TotalSubscribers'  => 100,
                        'UnsubscribesCount' => 5,
                    ]),

                ],
                [
                    'customer_id'              => 3,
                    'name'                     => 'HalfYearlyPromotion',
                    'sender_id'                => '8801921970168',
                    'send_welcome_sms'         => true,
                    'unsubscribe_notification' => true,
                    'send_keyword_message'     => false,
                    'status'                   => true,
                    'cache'                    => json_encode([
                        'SubscribersCount'  => 100,
                        'TotalSubscribers'  => 100,
                        'UnsubscribesCount' => 0,
                    ]),
                ],
                [
                    'customer_id'              => 3,
                    'name'                     => 'YearlyPromotion',
                    'sender_id'                => 'CoderPixel',
                    'send_welcome_sms'         => true,
                    'unsubscribe_notification' => true,
                    'send_keyword_message'     => false,
                    'status'                   => true,
                    'cache'                    => json_encode([
                        'SubscribersCount'  => 9,
                        'TotalSubscribers'  => 10,
                        'UnsubscribesCount' => 1,
                    ]),
                ],
                /*Customer ID 4*/

                [
                    'customer_id'              => 4,
                    'name'                     => 'EidPromotion',
                    'sender_id'                => 'DLT',
                    'send_welcome_sms'         => true,
                    'unsubscribe_notification' => true,
                    'send_keyword_message'     => true,
                    'status'                   => true,
                    'cache'                    => json_encode([
                        'SubscribersCount'  => 95,
                        'TotalSubscribers'  => 100,
                        'UnsubscribesCount' => 5,
                    ]),

                ],
                [
                    'customer_id'              => 4,
                    'name'                     => 'IndependenceDayPromotion',
                    'sender_id'                => '8801521970168',
                    'send_welcome_sms'         => true,
                    'unsubscribe_notification' => true,
                    'send_keyword_message'     => false,
                    'status'                   => true,
                    'cache'                    => json_encode([
                        'SubscribersCount'  => 100,
                        'TotalSubscribers'  => 100,
                        'UnsubscribesCount' => 0,
                    ]),
                ],
                [
                    'customer_id'              => 4,
                    'name'                     => 'RepublicDayPromotion',
                    'sender_id'                => '8801821970168',
                    'send_welcome_sms'         => true,
                    'unsubscribe_notification' => true,
                    'send_keyword_message'     => false,
                    'status'                   => true,
                    'cache'                    => json_encode([
                        'SubscribersCount'  => 9,
                        'TotalSubscribers'  => 10,
                        'UnsubscribesCount' => 1,
                    ]),
                ],
            ];

            foreach ($contact_groups as $group) {
                (new ContactGroups)->create($group);
            }

            $factory      = Factory::create();
            $data         = [];
            $customer_ids = [1, 3, 4];
            for ($i = 0; $i < 95; $i++) {
                $number = '88017' . $i . time();
                $number = substr($number, 0, 13);

                foreach ($customer_ids as $customer_id) {

                    $group_id = match ($customer_id) {
                        3 => 4,
                        4 => 7,
                        default => 1,
                    };

                    $data[] = [
                        'uid'         => uniqid(),
                        'customer_id' => $customer_id,
                        'group_id'    => $group_id,
                        'phone'       => $number,
                        'status'      => 'subscribe',
                    ];
                }
            }


            for ($i = 0; $i < 5; $i++) {
                $number = '88017' . $i . time();
                $number = substr($number, 0, 13);

                foreach ($customer_ids as $customer_id) {

                    $group_id = match ($customer_id) {
                        3 => 4,
                        4 => 7,
                        default => 1,
                    };

                    $data[] = [
                        'uid'         => uniqid(),
                        'customer_id' => $customer_id,
                        'group_id'    => $group_id,
                        'phone'       => $number,
                        'status'      => 'unsubscribe',
                    ];
                }
            }


            for ($i = 0; $i < 100; $i++) {
                $number = '88016' . $i . time();
                $number = substr($number, 0, 13);


                foreach ($customer_ids as $customer_id) {

                    $group_id = match ($customer_id) {
                        3 => 5,
                        4 => 8,
                        default => 2,
                    };

                    $data[] = [
                        'uid'         => uniqid(),
                        'customer_id' => $customer_id,
                        'group_id'    => $group_id,
                        'phone'       => $number,
                        'status'      => 'subscribe',
                    ];
                }

            }

            for ($i = 0; $i < 9; $i++) {
                $number = '88015' . $i . time();
                $number = substr($number, 0, 13);

                foreach ($customer_ids as $customer_id) {

                    $group_id = match ($customer_id) {
                        3 => 6,
                        4 => 9,
                        default => 3,
                    };

                    $data[] = [
                        'uid'         => uniqid(),
                        'customer_id' => $customer_id,
                        'group_id'    => $group_id,
                        'phone'       => $number,
                        'status'      => 'subscribe',
                    ];
                }
            }


            for ($i = 0; $i < 1; $i++) {
                $number = '88015' . $i . time();
                $number = substr($number, 0, 13);

                foreach ($customer_ids as $customer_id) {

                    $group_id = match ($customer_id) {
                        3 => 6,
                        4 => 9,
                        default => 3,
                    };

                    $data[] = [
                        'uid'         => uniqid(),
                        'customer_id' => $customer_id,
                        'group_id'    => $group_id,
                        'phone'       => $number,
                        'status'      => 'unsubscribe',
                    ];
                }
            }

            foreach ($data as $row) {
                $subscriber = Contacts::create($row);
                $subscriber->updateFields([
                    "PHONE"      => $row['phone'],
                    "FIRST_NAME" => $factory->firstName,
                    "LAST_NAME"  => $factory->lastName,
                ]);
            }

            //sms template
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('templates')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $sms_templates = [
                /*Customer ID 1*/
                [
                    'user_id' => 1,
                    'name'    => 'Promotion',
                    'message' => 'You will get 50 Percent off from next {EVENT_DATE}',
                ],
                [
                    'user_id' => 1,
                    'name'    => 'Greeting',
                    'message' => 'Hi {FIRST_NAME} {LAST_NAME}, welcome to Codeglen',
                ],

                /*Customer ID 3*/
                [
                    'user_id' => 3,
                    'name'    => 'BlackFriday',
                    'message' => 'You will get 50 Percent off from next black friday {EVENT_DATE}',
                ],
                [
                    'user_id' => 3,
                    'name'    => 'Welcome',
                    'message' => 'Hi {FIRST_NAME} {LAST_NAME}, welcome to Codeglen',
                ],

                /*Customer ID 4*/
                [
                    'user_id'         => 4,
                    'name'            => 'CyberMonday',
                    'sender_id'       => 'DLT',
                    'message'         => 'You will get 34 Percent off from next cyber monday {EVENT_DATE}',
                    'dlt_template_id' => 'template123ABC',
                    'dlt_category'    => 'promotional',
                    'approved'        => 'approved',
                ],
                [
                    'user_id'         => 4,
                    'name'            => 'Hello',
                    'sender_id'       => '8801821970168',
                    'message'         => 'Hi {FIRST_NAME} {LAST_NAME}, welcome to Codeglen',
                    'dlt_template_id' => 'template123Hello',
                    'dlt_category'    => 'transactional',
                    'approved'        => 'approved',
                ],
            ];

            foreach ($sms_templates as $template) {
                Templates::create($template);
            }

            //campaigns

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('campaigns_senderids')->truncate();
            DB::table('campaigns_lists')->truncate();
            DB::table('campaigns')->truncate();
            DB::table('reports')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');


            $status  = collect(['Delivered', 'Failed', 'Enroute', 'Undelivered', 'Expired', 'Rejected', 'Accepted', 'Skipped']);
            $factory = Factory::create();

            $userOneCampaigns = [
                /*Customer ID 1*/
                [
                    'uid'           => uniqid(),
                    'user_id'       => 1,
                    'campaign_name' => 'SMS Campaign',
                    'message'       => 'Hi {first_name}, welcome to {company}',
                    'sms_type'      => 'plain',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_DONE,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'run_at'        => Carbon::now()->subMinutes(5),
                    'delivery_at'   => Carbon::now()->subMinutes(2),
                    'created_at'    => Carbon::now()->subMinutes(6),
                    'updated_at'    => Carbon::now()->subMinutes(2),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 1,
                    'campaign_name' => 'Voice Campaign',
                    'message'       => 'You will get 50 Percent off from next {event_date}',
                    'sms_type'      => 'voice',
                    'language'      => 'en-GB',
                    'gender'        => 'male',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_QUEUING,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 1,
                    'campaign_name' => 'MMS Campaign',
                    'message'       => 'Hi {first_name}, welcome to {company}',
                    'media_url'     => 'https://ultimatesms.codeglen.com/demo/mms/mms_1617527278.png',
                    'sms_type'      => 'mms',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_QUEUING,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 1,
                    'campaign_name' => 'WhatsApp Campaign',
                    'message'       => 'You will get 50 Percent off from next {event_date}',
                    'sms_type'      => 'whatsapp',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_QUEUING,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 1,
                    'campaign_name' => 'Schedule Campaign',
                    'message'       => 'You will get 50 Percent off from next {event_date}',
                    'sms_type'      => 'plain',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_SCHEDULED,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'schedule_time' => Carbon::now()->addDays(3),
                    'schedule_type' => 'onetime',
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ],
                [
                    'uid'              => uniqid(),
                    'user_id'          => 1,
                    'campaign_name'    => 'Recurring Campaign',
                    'message'          => 'You will get 50 Percent off from next {event_date}',
                    'sms_type'         => 'plain',
                    'upload_type'      => 'normal',
                    'status'           => Campaigns::STATUS_SCHEDULED,
                    'cache'            => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'schedule_time'    => Carbon::now()->addDays(2),
                    'schedule_type'    => 'recurring',
                    'frequency_cycle'  => 'monthly',
                    'frequency_amount' => 1,
                    'frequency_unit'   => 'month',
                    'recurring_end'    => Carbon::now()->addMonth(),
                    'created_at'       => Carbon::now(),
                    'updated_at'       => Carbon::now(),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 1,
                    'campaign_name' => 'Normal Campaign',
                    'message'       => 'You will get 50 Percent off from next {event_date}',
                    'sms_type'      => 'plain',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_QUEUING,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 1,
                    'campaign_name' => 'Campaign Paused',
                    'message'       => 'Hi {first_name}, welcome to {company}',
                    'sms_type'      => 'plain',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_PAUSED,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'run_at'        => Carbon::now()->subMinutes(5),
                    'created_at'    => Carbon::now()->subMinutes(6),
                    'updated_at'    => Carbon::now()->subMinutes(6),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 1,
                    'campaign_name' => 'Campaign Processing',
                    'message'       => 'Hi {first_name}, welcome to {company}',
                    'sms_type'      => 'plain',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_SENDING,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'run_at'        => Carbon::now()->subMinutes(5),
                    'created_at'    => Carbon::now()->subMinutes(6),
                    'updated_at'    => Carbon::now()->subMinutes(6),
                ],
            ];
            $campaign_data    = [];

            $contacts = Contacts::where('group_id', 1)->take(30)->get();

            foreach ($userOneCampaigns as $campaign) {
                $getData = Campaigns::insertGetId($campaign);

                if ($getData) {

                    CampaignsList::create([
                        'campaign_id'     => $getData,
                        'contact_list_id' => 1,
                    ]);

                    if ($getData % 2) {
                        CampaignsSenderid::create([
                            'campaign_id' => $getData,
                            'sender_id'   => 'Codeglen',
                            'originator'  => 'sender_id',
                        ]);

                        foreach ($contacts as $contact) {

                            $campaign_data[] = [
                                'uid'               => uniqid(),
                                'user_id'           => 1,
                                'from'              => 'Codeglen',
                                'to'                => $contact->phone,
                                'message'           => 'You will get 50 Percent off from next yearly sale.',
                                'sms_type'          => $campaign['sms_type'],
                                'status'            => 'Delivered',
                                'customer_status'   => 'Delivered',
                                'send_by'           => 'from',
                                'campaign_id'       => $getData,
                                'cost'              => 1,
                                'sms_count'         => 1,
                                'sending_server_id' => 219,
                                'created_at'        => $factory->dateTimeThisMonth(),
                                'updated_at'        => $factory->dateTimeThisMonth(),
                            ];
                        }

                    } else {
                        CampaignsSenderid::create([
                            'campaign_id' => $getData,
                            'sender_id'   => '8801526970168',
                            'originator'  => 'phone_number',
                        ]);

                        foreach ($contacts as $contact) {

                            $campaign_data[] = [
                                'uid'               => uniqid(),
                                'user_id'           => 1,
                                'from'              => '8801526970168',
                                'to'                => $contact->phone,
                                'message'           => 'You will get 50 Percent off from next yearly sale.',
                                'sms_type'          => $campaign['sms_type'],
                                'status'            => 'Delivered',
                                'customer_status'   => 'Delivered',
                                'send_by'           => 'from',
                                'campaign_id'       => $getData,
                                'cost'              => 1,
                                'sms_count'         => 1,
                                'sending_server_id' => 219,
                                'created_at'        => $factory->dateTimeThisMonth(),
                                'updated_at'        => $factory->dateTimeThisMonth(),
                            ];
                        }
                    }
                }
            }

            Reports::insert($campaign_data);

            $campaign_data_four = [];
            $userFourCampaigns  = [

                /*Customer ID 4*/
                [
                    'uid'           => uniqid(),
                    'user_id'       => 4,
                    'campaign_name' => 'SMS Campaign',
                    'message'       => 'Hi {first_name}, welcome to {company}',
                    'sms_type'      => 'plain',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_DONE,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'run_at'        => Carbon::now()->subMinutes(5),
                    'delivery_at'   => Carbon::now()->subMinutes(2),
                    'created_at'    => Carbon::now()->subMinutes(6),
                    'updated_at'    => Carbon::now()->subMinutes(2),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 4,
                    'campaign_name' => 'WhatsApp Campaign',
                    'message'       => 'You will get 50 Percent off from next {event_date}',
                    'sms_type'      => 'whatsapp',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_QUEUING,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 4,
                    'campaign_name' => 'Schedule Campaign',
                    'message'       => 'You will get 50 Percent off from next {event_date}',
                    'sms_type'      => 'plain',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_SCHEDULED,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'schedule_time' => Carbon::now()->addDays(3),
                    'schedule_type' => 'onetime',
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ],
                [
                    'uid'              => uniqid(),
                    'user_id'          => 4,
                    'campaign_name'    => 'Recurring Campaign',
                    'message'          => 'You will get 50 Percent off from next {event_date}',
                    'sms_type'         => 'plain',
                    'upload_type'      => 'normal',
                    'status'           => Campaigns::STATUS_SCHEDULED,
                    'cache'            => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'schedule_time'    => Carbon::now()->addDays(2),
                    'schedule_type'    => 'recurring',
                    'frequency_cycle'  => 'monthly',
                    'frequency_amount' => 1,
                    'frequency_unit'   => 'month',
                    'recurring_end'    => Carbon::now()->addMonth(),
                    'created_at'       => Carbon::now(),
                    'updated_at'       => Carbon::now(),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 4,
                    'campaign_name' => 'Normal Campaign',
                    'message'       => 'You will get 50 Percent off from next {event_date}',
                    'sms_type'      => 'plain',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_QUEUING,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 4,
                    'campaign_name' => 'Campaign Paused',
                    'message'       => 'Hi {first_name}, welcome to {company}',
                    'sms_type'      => 'plain',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_PAUSED,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'run_at'        => Carbon::now()->subMinutes(5),
                    'created_at'    => Carbon::now()->subMinutes(6),
                    'updated_at'    => Carbon::now()->subMinutes(2),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 4,
                    'campaign_name' => 'Campaign Processing',
                    'message'       => 'Hi {first_name}, welcome to {company}',
                    'sms_type'      => 'plain',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_SENDING,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'run_at'        => Carbon::now()->subMinutes(5),
                    'created_at'    => Carbon::now()->subMinutes(6),
                    'updated_at'    => Carbon::now()->subMinutes(6),
                ],
            ];

            $contactsFour = Contacts::where('group_id', 7)->take(30)->get();

            foreach ($userFourCampaigns as $campaign) {
                $getData = Campaigns::insertGetId($campaign);

                if ($getData) {

                    CampaignsList::create([
                        'campaign_id'     => $getData,
                        'contact_list_id' => 7,
                    ]);

                    if ($getData % 2) {
                        CampaignsSenderid::create([
                            'campaign_id' => $getData,
                            'sender_id'   => 'DLT',
                            'originator'  => 'sender_id',
                        ]);

                        foreach ($contactsFour as $contact) {

                            $campaign_data_four[] = [
                                'uid'               => uniqid(),
                                'user_id'           => 4,
                                'from'              => 'DLT',
                                'to'                => $contact->phone,
                                'message'           => 'You will get 50 Percent off from next yearly sale.',
                                'sms_type'          => $campaign['sms_type'],
                                'status'            => 'Delivered',
                                'customer_status'   => 'Delivered',
                                'send_by'           => 'from',
                                'campaign_id'       => $getData,
                                'cost'              => 1,
                                'sms_count'         => 1,
                                'sending_server_id' => 219,
                                'created_at'        => $factory->dateTimeThisMonth(),
                                'updated_at'        => $factory->dateTimeThisMonth(),
                            ];
                        }

                    } else {
                        CampaignsSenderid::create([
                            'campaign_id' => $getData,
                            'sender_id'   => '8801521970168',
                            'originator'  => 'phone_number',
                        ]);

                        foreach ($contactsFour as $contact) {

                            $campaign_data_four[] = [
                                'uid'               => uniqid(),
                                'user_id'           => 4,
                                'from'              => '8801921970168',
                                'to'                => $contact->phone,
                                'message'           => 'You will get 50 Percent off from next yearly sale.',
                                'sms_type'          => $campaign['sms_type'],
                                'status'            => 'Delivered',
                                'customer_status'   => 'Delivered',
                                'send_by'           => 'from',
                                'campaign_id'       => $getData,
                                'cost'              => 1,
                                'sms_count'         => 1,
                                'sending_server_id' => 219,
                                'created_at'        => $factory->dateTimeThisMonth(),
                                'updated_at'        => $factory->dateTimeThisMonth(),
                            ];
                        }
                    }
                }
            }
            Reports::insert($campaign_data_four);


            $campaign_data_three = [];

            $userThreeCampaigns = [

                /*Customer ID 3*/
                [
                    'uid'           => uniqid(),
                    'user_id'       => 3,
                    'campaign_name' => 'SMS Campaign',
                    'message'       => 'Hi {first_name}, welcome to {company}',
                    'sms_type'      => 'plain',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_DONE,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'run_at'        => Carbon::now()->subMinutes(5),
                    'delivery_at'   => Carbon::now()->subMinutes(2),
                    'created_at'    => Carbon::now()->subMinutes(6),
                    'updated_at'    => Carbon::now()->subMinutes(2),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 3,
                    'campaign_name' => 'Voice Campaign',
                    'message'       => 'You will get 50 Percent off from next {event_date}',
                    'sms_type'      => 'voice',
                    'language'      => 'en-GB',
                    'gender'        => 'male',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_QUEUING,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 3,
                    'campaign_name' => 'MMS Campaign',
                    'message'       => 'Hi {first_name}, welcome to {company}',
                    'media_url'     => 'https://ultimatesms.codeglen.com/demo/mms/mms_1617527278.png',
                    'sms_type'      => 'mms',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_QUEUING,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 3,
                    'campaign_name' => 'WhatsApp Campaign',
                    'message'       => 'You will get 50 Percent off from next {event_date}',
                    'sms_type'      => 'whatsapp',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_QUEUING,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 3,
                    'campaign_name' => 'Schedule Campaign',
                    'message'       => 'You will get 50 Percent off from next {event_date}',
                    'sms_type'      => 'plain',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_SCHEDULED,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'schedule_time' => Carbon::now()->addDays(3),
                    'schedule_type' => 'onetime',
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ],
                [
                    'uid'              => uniqid(),
                    'user_id'          => 3,
                    'campaign_name'    => 'Recurring Campaign',
                    'message'          => 'You will get 50 Percent off from next {event_date}',
                    'sms_type'         => 'plain',
                    'upload_type'      => 'normal',
                    'status'           => Campaigns::STATUS_SCHEDULED,
                    'cache'            => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'schedule_time'    => Carbon::now()->addDays(2),
                    'schedule_type'    => 'recurring',
                    'frequency_cycle'  => 'monthly',
                    'frequency_amount' => 1,
                    'frequency_unit'   => 'month',
                    'recurring_end'    => Carbon::now()->addMonth(),
                    'created_at'       => Carbon::now(),
                    'updated_at'       => Carbon::now(),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 3,
                    'campaign_name' => 'Normal Campaign',
                    'message'       => 'You will get 50 Percent off from next {event_date}',
                    'sms_type'      => 'plain',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_QUEUING,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 3,
                    'campaign_name' => 'Campaign Paused',
                    'message'       => 'Hi {first_name}, welcome to {company}',
                    'sms_type'      => 'plain',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_PAUSED,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'run_at'        => Carbon::now()->subMinutes(5),
                    'created_at'    => Carbon::now()->subMinutes(6),
                    'updated_at'    => Carbon::now()->subMinutes(2),
                ],
                [
                    'uid'           => uniqid(),
                    'user_id'       => 3,
                    'campaign_name' => 'Campaign Processing',
                    'message'       => 'Hi {first_name}, welcome to {company}',
                    'sms_type'      => 'plain',
                    'upload_type'   => 'normal',
                    'status'        => Campaigns::STATUS_SENDING,
                    'cache'         => '{"ContactCount":30,"DeliveredCount":30,"FailedDeliveredCount":0,"NotDeliveredCount":0}',
                    'run_at'        => Carbon::now()->subMinutes(5),
                    'created_at'    => Carbon::now()->subMinutes(6),
                    'updated_at'    => Carbon::now()->subMinutes(6),
                ],

            ];

            $contactsThree = Contacts::where('group_id', 4)->take(30)->get();

            foreach ($userThreeCampaigns as $campaign) {
                $getData = Campaigns::insertGetId($campaign);

                if ($getData) {

                    CampaignsList::create([
                        'campaign_id'     => $getData,
                        'contact_list_id' => 4,
                    ]);

                    if ($getData % 2) {
                        CampaignsSenderid::create([
                            'campaign_id' => $getData,
                            'sender_id'   => 'CoderPixel',
                            'originator'  => 'sender_id',
                        ]);

                        foreach ($contactsThree as $contact) {

                            $campaign_data_three[] = [
                                'uid'               => uniqid(),
                                'user_id'           => 3,
                                'from'              => 'CoderPixel',
                                'to'                => $contact->phone,
                                'message'           => 'You will get 50 Percent off from next yearly sale.',
                                'sms_type'          => $campaign['sms_type'],
                                'status'            => 'Delivered',
                                'customer_status'   => 'Delivered',
                                'send_by'           => 'from',
                                'campaign_id'       => $getData,
                                'cost'              => 1,
                                'sms_count'         => 1,
                                'sending_server_id' => 219,
                                'created_at'        => $factory->dateTimeThisMonth(),
                                'updated_at'        => $factory->dateTimeThisMonth(),
                            ];
                        }

                    } else {
                        CampaignsSenderid::create([
                            'campaign_id' => $getData,
                            'sender_id'   => '8801921970168',
                            'originator'  => 'phone_number',
                        ]);

                        foreach ($contactsThree as $contact) {

                            $campaign_data_three[] = [
                                'uid'               => uniqid(),
                                'user_id'           => 3,
                                'from'              => '8801921970168',
                                'to'                => $contact->phone,
                                'message'           => 'You will get 50 Percent off from next yearly sale.',
                                'sms_type'          => $campaign['sms_type'],
                                'status'            => 'Delivered',
                                'customer_status'   => 'Delivered',
                                'send_by'           => 'from',
                                'campaign_id'       => $getData,
                                'cost'              => 1,
                                'sms_count'         => 1,
                                'sending_server_id' => 219,
                                'created_at'        => $factory->dateTimeThisMonth(),
                                'updated_at'        => $factory->dateTimeThisMonth(),
                            ];
                        }
                    }
                }
            }

            Reports::insert($campaign_data_three);


            //Automations

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('tracking_logs')->truncate();
            DB::table('automations')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $automations = [
                [
                    'name'              => 'SayBirthday',
                    'user_id'           => 1,
                    'contact_list_id'   => 1,
                    'sending_server_id' => 219,
                    'timezone'          => 'Asia/Dhaka',
                    'sender_id'         => json_encode(['USMS']),
                    'message'           => 'Happy Birthday {first_name}! Thank you for being with us.',
                    'sms_type'          => 'plain',
                    'status'            => 'active',
                    'cache'             => json_encode([
                        'ContactCount'         => 95,
                        'DeliveredCount'       => 30,
                        'FailedDeliveredCount' => 0,
                        'NotDeliveredCount'    => 0,
                        'PendingContactCount'  => 65,
                    ]),
                    'data'              => json_encode([
                        'options' => [
                            'before' => 0,
                            'at'     => '14:21',
                        ],
                    ]),
                ],
                [
                    'name'              => 'BirthdayGreetings',
                    'user_id'           => 1,
                    'contact_list_id'   => 2,
                    'sending_server_id' => 219,
                    'timezone'          => 'Asia/Dhaka',
                    'sender_id'         => json_encode(['USMS']),
                    'message'           => 'Happy Birthday {first_name}! Thank you for being with us.',
                    'sms_type'          => 'plain',
                    'status'            => 'inactive',
                    'cache'             => json_encode([
                        'ContactCount'         => 100,
                        'DeliveredCount'       => 0,
                        'FailedDeliveredCount' => 0,
                        'NotDeliveredCount'    => 0,
                        'PendingContactCount'  => 100,
                    ]),
                    'data'              => json_encode([
                        'options' => [
                            'before' => 0,
                            'at'     => '14:21',
                        ],
                    ]),
                ],
            ];

            foreach ($automations as $automation) {
                Automation::create($automation);
            }
            $AutomationContacts = Contacts::where('group_id', 1)->take(30)->get();

            $automationData = [];

            $message = 'Happy Birthday {first_name}! Thank you for being with us.';

            foreach ($AutomationContacts as $contact) {
                $render_message = Tool::renderSMS($message, [
                    'first_name' => $contact->first_name,
                ]);

                $automationData[] = [
                    'user_id'           => 1,
                    'from'              => 'Codeglen',
                    'to'                => $contact->phone,
                    'message'           => $render_message,
                    'sms_type'          => 'plain',
                    'status'            => 'Delivered',
                    'send_by'           => 'to',
                    'automation_id'     => 1,
                    'cost'              => 1,
                    'sms_count'         => 1,
                    'sending_server_id' => 219,
                ];
            }

            $i = 1;
            foreach ($automationData as $aData) {
                $response = Reports::create($aData);

                $params = [
                    'message_id'        => $response->id,
                    'customer_id'       => 1,
                    'sending_server_id' => 219,
                    'automation_id'     => 1,
                    'contact_id'        => $i,
                    'contact_group_id'  => 1,
                    'sms_count'         => 1,
                    'status'            => 'Delivered',
                ];

                TrackingLog::create($params);
                $i++;
            }

            Automation::find(1)->update([
                'cache' => json_encode([
                    'ContactCount'         => 95,
                    'DeliveredCount'       => 30,
                    'FailedDeliveredCount' => 0,
                    'NotDeliveredCount'    => 0,
                    'PendingContactCount'  => 65,
                ]),
            ]);


            $automationsThree = [
                [
                    'name'              => 'SayBirthday',
                    'user_id'           => 3,
                    'contact_list_id'   => 4,
                    'sending_server_id' => 219,
                    'timezone'          => 'Asia/Dhaka',
                    'sender_id'         => json_encode(['USMS']),
                    'message'           => 'Happy Birthday {first_name}! Thank you for being with us.',
                    'sms_type'          => 'plain',
                    'status'            => 'active',
                    'cache'             => json_encode([
                        'ContactCount'         => 95,
                        'DeliveredCount'       => 30,
                        'FailedDeliveredCount' => 0,
                        'NotDeliveredCount'    => 0,
                        'PendingContactCount'  => 65,
                    ]),
                    'data'              => json_encode([
                        'options' => [
                            'before' => 0,
                            'at'     => '14:21',
                        ],
                    ]),
                ],
                [
                    'name'              => 'BirthdayGreetings',
                    'user_id'           => 3,
                    'contact_list_id'   => 5,
                    'sending_server_id' => 219,
                    'timezone'          => 'Asia/Dhaka',
                    'sender_id'         => json_encode(['USMS']),
                    'message'           => 'Happy Birthday {first_name}! Thank you for being with us.',
                    'sms_type'          => 'plain',
                    'status'            => 'inactive',
                    'cache'             => json_encode([
                        'ContactCount'         => 100,
                        'DeliveredCount'       => 0,
                        'FailedDeliveredCount' => 0,
                        'NotDeliveredCount'    => 0,
                        'PendingContactCount'  => 100,
                    ]),
                    'data'              => json_encode([
                        'options' => [
                            'before' => 0,
                            'at'     => '14:21',
                        ],
                    ]),
                ],
            ];

            foreach ($automationsThree as $automation) {
                Automation::create($automation);
            }
            $AutomationContacts = Contacts::where('group_id', 4)->take(30)->get();

            $automationData = [];


            foreach ($AutomationContacts as $contact) {
                $render_message = Tool::renderSMS($message, [
                    'first_name' => $contact->first_name,
                ]);

                $automationData[] = [
                    'user_id'           => 3,
                    'from'              => 'CoderPixel',
                    'to'                => $contact->phone,
                    'message'           => $render_message,
                    'sms_type'          => 'plain',
                    'status'            => 'Delivered',
                    'send_by'           => 'to',
                    'automation_id'     => 3,
                    'cost'              => 1,
                    'sms_count'         => 1,
                    'sending_server_id' => 219,
                ];
            }

            $i = 1;
            foreach ($automationData as $aData) {
                $response = Reports::create($aData);

                $params = [
                    'message_id'        => $response->id,
                    'customer_id'       => 3,
                    'sending_server_id' => 219,
                    'automation_id'     => 3,
                    'contact_id'        => $i,
                    'contact_group_id'  => 4,
                    'sms_count'         => 1,
                    'status'            => 'Delivered',
                ];

                TrackingLog::create($params);
                $i++;
            }

            Automation::find(3)->update([
                'cache' => json_encode([
                    'ContactCount'         => 95,
                    'DeliveredCount'       => 30,
                    'FailedDeliveredCount' => 0,
                    'NotDeliveredCount'    => 0,
                    'PendingContactCount'  => 65,
                ]),
            ]);


            //chat box
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('chat_box_messages')->truncate();
            DB::table('chat_boxes')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');


            $chatbox = new ChatBoxSeeder();
            $chatbox->run();

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('notifications')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $notifications = [
                [
                    'user_id'           => 1,
                    'notification_for'  => 'admin',
                    'notification_type' => 'user',
                    'message'           => 'Codeglen Registered',
                    'mark_read'         => 0,
                ],
                [
                    'user_id'           => 1,
                    'notification_for'  => 'admin',
                    'notification_type' => 'plan',
                    'message'           => 'Standard Purchased By Codeglen',
                    'mark_read'         => 0,
                ],
                [
                    'user_id'           => 1,
                    'notification_for'  => 'admin',
                    'notification_type' => 'senderid',
                    'message'           => 'New Sender ID request from Codeglen',
                    'mark_read'         => 0,
                ],
                [
                    'user_id'           => 1,
                    'notification_for'  => 'admin',
                    'notification_type' => 'number',
                    'message'           => '8801521970168 Purchased By Codeglen',
                    'mark_read'         => 0,
                ],
                [
                    'user_id'           => 3,
                    'notification_for'  => 'client',
                    'notification_type' => 'chatbox',
                    'message'           => 'Message From 8801521970168',
                    'mark_read'         => 0,
                ],

                [
                    'user_id'           => 3,
                    'notification_for'  => 'admin',
                    'notification_type' => 'senderid',
                    'message'           => 'Sender ID Codeglen Approved',
                    'mark_read'         => 0,
                ],
                [
                    'user_id'           => 4,
                    'notification_for'  => 'client',
                    'notification_type' => 'chatbox',
                    'message'           => 'Message From 8801521970168',
                    'mark_read'         => 0,
                ],

                [
                    'user_id'           => 4,
                    'notification_for'  => 'admin',
                    'notification_type' => 'senderid',
                    'message'           => 'Sender ID Codeglen Approved',
                    'mark_read'         => 0,
                ],
            ];

            foreach ($notifications as $notification) {
                Notifications::create($notification);
            }


            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('announcements')->truncate();
            DB::table('announcements_user')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $announcements = [
                [
                    'title'       => 'All Envato Market payments',
                    'description' => 'All Envato Market payments are now being sent via our new payout system. If you have not set up your details yet, visit https://ultimatesms.codeglen.com and select ‘Update Payout Method.’ For more information, see this Help Centre article.',
                ],
                [
                    'title'       => 'Free File nominations for CodeCanyon',
                    'description' => "Free File nominations for CodeCanyon are now open - we're looking for files to use in Marketing promotions during January, February and March, including CodeCanyon's Free File of the Month items. View the criteria and nominate items",
                ],
            ];

            foreach ($announcements as $announcement) {

                $announcement = new Announcements($announcement);

                $announcement->save();

                $announcement->users()->attach([1, 3, 4]);
            }


            $reportsDataCustom = [];

            foreach ($customer_ids as $customer_id) {
                $sendBy    = collect(['to', 'from']);
                $smsTypes  = collect(['plain', 'mms', 'voice', 'viber', 'whatsapp', 'otp']);
                $senderIds = collect(['CoderPixel', 'Codeglen', 'DLT']);

                for ($i = 0; $i < 10; $i++) {
                    $reportsDataCustom[] = [

                        'uid'               => uniqid(),
                        'user_id'           => $customer_id,
                        'from'              => $senderIds->random(),
                        'to'                => '88015' . time(),
                        'message'           => $factory->text(100),
                        'sms_type'          => $smsTypes->random(),
                        'status'            => $status->random(),
                        'customer_status'   => $status->random(),
                        'send_by'           => $sendBy->random(),
                        'sms_count'         => 1,
                        'cost'              => 1,
                        'sending_server_id' => 219,
                        'created_at'        => $factory->dateTimeThisMonth(),
                        'updated_at'        => $factory->dateTimeThisMonth(),
                    ];
                }
            }
            Reports::insert($reportsDataCustom);


            return 0;

        }

    }
