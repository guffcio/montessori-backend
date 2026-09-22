<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenPayU_Configuration;

class PayuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        OpenPayU_Configuration::setEnvironment(
            env('PAYU_ENVIRONMENT', 'secure'),
        );

        OpenPayU_Configuration::setMerchantPosId(
            env('PAYU_MERCHANT_POS_ID')
        );

        OpenPayU_Configuration::setSignatureKey(
            env('PAYU_SIGNATURE_KEY')
        );

        OpenPayU_Configuration::setOauthClientId(
            env('PAYU_OAUTH_CLIENT_ID')
        );

        OpenPayU_Configuration::setOauthClientSecret(
            env('PAYU_OAUTH_CLIENT_SECRET')
        );
    }
}
