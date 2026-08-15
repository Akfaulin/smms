<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class MetaApi extends BaseConfig
{
    /**
     * Meta App ID
     */
    public string $appId = '2078669586190235';

    /**
     * Meta App Secret
     */
    public string $appSecret = '9d15be3179a73fa5dade7e4b04599736';

    /**
     * Meta Graph API Version
     */
    public string $apiVersion = 'v26.0';

    /**
     * Instagram Business Account Username
     */
    public string $igUsername = 'smm.localinternal';

    /**
     * Instagram Business Account ID (optional — jika diisi, skip API lookup)
     */
    public string $igAccountId = '27737834732545092';

    /**
     * Meta Base Graph API Endpoint
     */
    public string $graphUrl = 'https://graph.facebook.com/';

    /**
     * User Access Token (Short-lived or Long-lived from .env)
     */
    public string $userAccessToken = 'IGAAsU0LFY39dBZAFpfRExwdUp2a0NXZAFdWNU9KRkRDcS0xekV6MUVXRUtvYjg4SWtzNkROUlptdXYzaHUwRzJHYmdZAdnN2V0JUV2RwdWd2OVpEYUFhTVdZAeUx2bXowd3BFTUVoWWU1cmF5UVRzcXFyaEY3VjIwQnlIWVl6WFNTZAwZDZD';

    public function __construct()
    {
        parent::__construct();

        $this->appId           = trim(env('META_APP_ID') ?: ($_ENV['META_APP_ID'] ?? (getenv('META_APP_ID') ?: $this->appId)));
        $this->appSecret        = trim(env('META_APP_SECRET') ?: ($_ENV['META_APP_SECRET'] ?? (getenv('META_APP_SECRET') ?: $this->appSecret)));
        $this->apiVersion       = trim(env('META_API_VERSION') ?: ($_ENV['META_API_VERSION'] ?? (getenv('META_API_VERSION') ?: $this->apiVersion)));
        $this->igUsername       = trim(env('META_IG_USERNAME') ?: ($_ENV['META_IG_USERNAME'] ?? (getenv('META_IG_USERNAME') ?: $this->igUsername)));
        $this->igAccountId     = trim(env('META_IG_ACCOUNT_ID') ?: ($_ENV['META_IG_ACCOUNT_ID'] ?? (getenv('META_IG_ACCOUNT_ID') ?: $this->igAccountId)));
        $this->userAccessToken = trim(env('META_USER_ACCESS_TOKEN') ?: ($_ENV['META_USER_ACCESS_TOKEN'] ?? (getenv('META_USER_ACCESS_TOKEN') ?: (env('META_ACCESS_TOKEN') ?: ($_ENV['META_ACCESS_TOKEN'] ?? (getenv('META_ACCESS_TOKEN') ?: $this->userAccessToken))))));
    }
}
