<?php

    /**
     * All public routes listed here. No middleware will not affect these routes
     */
    Route::get('contacts/{contact}/subscribe-url', 'Customer\ContactsController@subscribeURL')->name('contacts.subscribe_url');
    Route::post('contacts/{contact}/subscribe-url', 'Customer\ContactsController@insertContactBySubscriptionForm');
    Route::any('dlr/twilio', 'Customer\DLRController@dlrTwilio')->name('dlr.twilio');
    Route::any('inbound/twilio', 'Customer\DLRController@inboundTwilio')->name('inbound.twilio');
    Route::any('inbound/twilio-copilot', 'Customer\DLRController@inboundTwilioCopilot')->name('inbound.twilio_copilot');
    Route::any('dlr/routemobile', 'Customer\DLRController@dlrRouteMobile')->name('dlr.routemobile');
    Route::any('dlr/textlocal', 'Customer\DLRController@dlrTextLocal')->name('dlr.textlocal');
    Route::any('inbound/textlocal', 'Customer\DLRController@inboundTextLocal')->name('inbound.textlocal');
    Route::any('dlr/plivo', 'Customer\DLRController@dlrPlivo')->name('dlr.plivo');
    Route::any('inbound/plivo', 'Customer\DLRController@inboundPlivo')->name('inbound.plivo');
    Route::any('inbound/plivo-powerpack', 'Customer\DLRController@inboundPlivoPowerPack')->name('inbound.plivo_powerpack');
    Route::any('dlr/smsglobal', 'Customer\DLRController@dlrSMSGlobal')->name('dlr.smsglobal');
    Route::any('dlr/bulksms', 'Customer\DLRController@dlrBulkSMS')->name('dlr.bulksms');
    Route::any('inbound/bulksms', 'Customer\DLRController@inboundBulkSMS')->name('inbound.bulksms');
    Route::any('dlr/vonage', 'Customer\DLRController@dlrVonage')->name('dlr.vonage');
    Route::any('inbound/vonage', 'Customer\DLRController@inboundVonage')->name('inbound.vonage');
    Route::any('inbound/bird', 'Customer\DLRController@inboundBird')->name('inbound.bird');
    Route::any('dlr/infobip', 'Customer\DLRController@dlrInfobip')->name('dlr.infobip');
    Route::any('inbound/signalwire', 'Customer\DLRController@inboundSignalwire')->name('inbound.signalwire');
    Route::any('inbound/telnyx', 'Customer\DLRController@inboundTelnyx')->name('inbound.telnyx');
    Route::any('inbound/teletopiasms', 'Customer\DLRController@inboundTeletopiasms')->name('inbound.teletopiasms');
    Route::any('inbound/flowroute', 'Customer\DLRController@inboundFlowRoute')->name('inbound.flowroute');
    Route::any('dlr/easysendsms', 'Customer\DLRController@dlrEasySendSMS')->name('dlr.easysendsms');
    Route::any('inbound/easysendsms', 'Customer\DLRController@inboundEasySendSMS')->name('inbound.easysendsms');
    Route::any('inbound/skyetel', 'Customer\DLRController@inboundSkyetel')->name('inbound.skyetel');
    Route::any('inbound/chatapi', 'Customer\DLRController@inboundChatApi')->name('inbound.chatapi');

    Route::any('dlr/callr', 'Customer\DLRController@dlrCallr')->name('dlr.callr');
    Route::any('inbound/callr', 'Customer\DLRController@inboundCallr')->name('inbound.callr');

    Route::any('dlr/cm', 'Customer\DLRController@dlrCM')->name('dlr.cm');
    Route::any('inbound/cm', 'Customer\DLRController@inboundCM')->name('inbound.cm');

    Route::any('dlr/africastalking', 'Customer\DLRController@dlrAfricasTalking')->name('dlr.africastalking');
    Route::any('dlr/1s2u', 'Customer\DLRController@dlr1s2u')->name('dlr.1s2u');

    Route::any('inbound/bandwidth', 'Customer\DLRController@inboundBandwidth')->name('inbound.bandwidth');

    Route::any('inbound/solucoesdigitais', 'Customer\DLRController@inboundSolucoesdigitais')->name('inbound.solucoesdigitais');

    Route::any('dlr/keccelsms', 'Customer\DLRController@dlrKeccelSMS')->name('dlr.keccelsms');

    Route::any('dlr/gatewayapi', 'Customer\DLRController@dlrGatewayApi')->name('dlr.gatewayapi');
    Route::any('inbound/gatewayapi', 'Customer\DLRController@inboundGatewayApi')->name('inbound.gatewayapi');

    Route::any('inbound/gatewayapi', 'Customer\DLRController@inboundGatewayApi')->name('inbound.gatewayapi');
    Route::any('dlr/smsvas', 'Customer\DLRController@dlrSMSVas')->name('dlr.smsvas');

    Route::any('dlr/advancemsgsys', 'Customer\DLRController@dlrAdvanceMSGSys')->name('dlr.advancemsgsys');

    /*Version 3.5*/
    Route::any('dlr/d7networks', 'Customer\DLRController@dlrD7networks')->name('dlr.d7networks');

    Route::any('inbound/teleapi', 'Customer\DLRController@inboundTeleAPI')->name('inbound.teleapi');

    /*Version 3.6*/
    Route::any('dlr/amazon-sns', 'Customer\DLRController@dlrAmazonSNS')->name('dlr.amazon-sns');
    Route::any('dlr/nimbuz', 'Customer\DLRController@dlrNimbuz')->name('dlr.nimbuz');

    Route::any('inbound/whatsender', 'Customer\DLRController@inboundWhatsender')->name('inbound.Whatsender');

    Route::any('dlr/gatewaysa', 'Customer\DLRController@dlrGatewaySa')->name('dlr.gatewaysa');

    Route::any('inbound/cheapglobalsms', 'Customer\DLRController@inboundCheapglobalsms')->name('inbound.cheapglobalsms');

    Route::any('dlr/smsmode', 'Customer\DLRController@dlrSMSMode')->name('dlr.smsmode');
    Route::any('inbound/smsmode', 'Customer\DLRController@inboundSMSMode')->name('inbound.smsmode');

    Route::any('inbound/infobip', 'Customer\DLRController@inboundInfobip')->name('inbound.infobip');

    /*Version 3.7*/
    Route::any('inbound/voximplant', 'Customer\DLRController@inboundVoximplant')->name('inbound.voximplant');
    Route::any('inbound/inteliquent', 'Customer\DLRController@inboundInteliquent')->name('inbound.inteliquent');

    /*Version 3.8*/
    Route::any('dlr/hutch', 'Customer\DLRController@dlrHutchLK')->name('dlr.hutch');
    Route::any('inbound/clicksend', 'Customer\DLRController@inboundClickSend')->name('inbound.clicksend');
    Route::any('dlr/moceanapi', 'Customer\DLRController@dlrMoceanAPI')->name('dlr.moceanapi');
    Route::any('dlr/airtel-india', 'Customer\DLRController@dlrAirtelIndia')->name('dlr.airtel-india');
    Route::any('inbound/clickatell', 'Customer\DLRController@inboundClickatell')->name('inbound.clickatell');
    Route::any('dlr/simpletexting', 'Customer\DLRController@dlrSimpleTexting')->name('dlr.simpletexting');
    Route::any('inbound/simpletexting', 'Customer\DLRController@inboundSimpleTexting')->name('inbound.simpletexting');
    Route::any('dlr/dinstar', 'Customer\DLRController@dlrDinstar')->name('dlr.dinstar');

    /*Version 3.9*/
    Route::any('dlr/mp', 'Customer\DLRController@dlrMP')->name('dlr.mp');
    Route::any('dlr/broadbased', 'Customer\DLRController@dlrBasedBroad')->name('dlr.broadbased');
    Route::any('inbound/smsdenver', 'Customer\DLRController@inboundSmsdenver')->name('inbound.smsdenver');
    Route::any('dlr/smsdenver', 'Customer\DLRController@dlrSmsdenver')->name('dlr.smsdenver');
    Route::any('dlr/topying', 'Customer\DLRController@dlrTopying')->name('dlr.topying');
    Route::any('dlr/smsto', 'Customer\DLRController@dlrSmsTO')->name('dlr.smsto');
    Route::any('inbound/textbelt', 'Customer\DLRController@inboundTextbelt')->name('inbound.textbelt');
    Route::any('inbound/burstsms', 'Customer\DLRController@inboundBurstSMS')->name('inbound.burstsms');
    Route::any('inbound/800com', 'Customer\DLRController@inbound800com')->name('inbound.800com');
    Route::any('inbound/sinch', 'Customer\DLRController@inboundSinch')->name('inbound.sinch');


    /*Version 3.10.0*/
    Route::any('inbound/notifyre', 'Customer\DLRController@inboundNotifyre')->name('inbound.notifyre');
    Route::any('inbound/smsgateway', 'Customer\DLRController@inboundSMSGateway')->name('inbound.smsgateway');
    Route::any('inbound/ejoin', 'Customer\DLRController@inboundEjoin')->name('inbound.ejoin');
    Route::any('inbound/txtria', 'Customer\DLRController@inboundTxTRIA')->name('inbound.txtria');

    /*Version 3.11.0*/
    Route::any('inbound/d7networks', 'Customer\DLRController@inboundD7networks')->name('inbound.d7networks');
    Route::any('inbound/diafaan', 'Customer\DLRController@inboundDiafaan')->name('inbound.diafaan');

    Route::get('contacts/{contact}/unsubscribe-url', 'Customer\ContactsController@unsubscribeURL')->name('contacts.unsubscribe_url');
    Route::post('contacts/{contact}/unsubscribe-url', 'Customer\ContactsController@postUnsubscribeURL');

    Route::any('inbound/webhook/{user}', 'Customer\DLRController@inboundReceiveWebhook')->name('inbound.webhook');


    /*
    |--------------------------------------------------------------------------
    | Privacy Policies & Terms of Use
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    Route::get('terms-of-use', 'PublicController@termsOfUse')->name('terms-of-use');
    Route::get('privacy-policy', 'PublicController@privacyPolicy')->name('privacy-policy');

    Route::get('create-super-admin', 'PublicController@createSuperAdmin')->name('create-super-admin');
    /*
    |--------------------------------------------------------------------------
    | installer file
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    Route::group(['prefix' => 'install', 'as' => 'Installer::', 'middleware' => ['web', 'install']], function () {
        Route::get('/', [
            'as'   => 'welcome',
            'uses' => 'InstallerController@welcome',
        ]);

        Route::get('environment', [
            'as'   => 'environment',
            'uses' => 'InstallerController@environment',
        ]);

        Route::get('environment/wizard', [
            'as'   => 'environmentWizard',
            'uses' => 'InstallerController@environmentWizard',
        ]);

        Route::post('environment/database', [
            'as'   => 'environmentDatabase',
            'uses' => 'InstallerController@saveDatabase',
        ]);

        Route::get('requirements', [
            'as'   => 'requirements',
            'uses' => 'InstallerController@requirements',
        ]);

        Route::get('permissions', [
            'as'   => 'permissions',
            'uses' => 'InstallerController@permissions',
        ]);

        Route::post('database', [
            'as'   => 'database',
            'uses' => 'InstallerController@database',
        ]);

        Route::get('final', [
            'as'   => 'final',
            'uses' => 'InstallerController@finish',
        ]);
    });

    Route::group(['prefix' => 'update', 'as' => 'Updater::', 'middleware' => 'web'], function () {

        Route::group(['middleware' => 'update'], function () {
            Route::get('/', [
                'as'   => 'welcome',
                'uses' => 'UpdateController@welcome',
            ]);

            Route::post('/', [
                'as'   => 'verify_product',
                'uses' => 'UpdateController@verifyProduct',
            ]);
        });
    });
