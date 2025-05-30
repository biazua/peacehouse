<?php

    namespace Database\Seeders;


    use Illuminate\Database\Seeder;
    use App\Models\Currency;

    class CurrenciesSeeder extends Seeder
    {
        /**
         * Run the database seeders.
         *
         * @return void
         */
        public function run()
        {
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
                ], [
                    'uid'     => uniqid(),
                    'user_id' => 1,
                    'name'    => 'Ghana Cedi',
                    'code'    => 'GHS',
                    'format'  => 'GH¢{PRICE}',
                    'status'  => true,
                ],
            ];

            foreach ($currency_data as $data) {
                Currency::create($data);
            }

        }

    }
