<?php

namespace kashem\licenseChecker;

use App\Models\AppConfig;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class ProductVerifyController extends Controller
{
    public function verifyPurchaseCode()
    {

        $pageConfigs = [
                'bodyClass' => "bg-full-screen-image",
                'blankPage' => true,
        ];

        return view('licenseChecker::verify-purchase-code', compact('pageConfigs'));
    }

    public function postVerifyPurchaseCode(Request $request)
    {

        // License validation bypassed: always treat as valid
        $purchase_code = $request->input('purchase_code');
        $domain_name   = $request->input('application_url');

        AppConfig::where('setting', 'license')->update(['value' => $purchase_code]);
        AppConfig::where('setting', 'license_type')->update(['value' => 'bypassed']);
        AppConfig::where('setting', 'valid_domain')->update(['value' => 'yes']);

        return redirect()->route('admin.home')->with([
            'message' => 'License check bypassed. Access granted.',
            'status'  => 'success',
        ]);

    }
}
