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
    public string $igAccountId = '';

    /**
     * Meta Base Graph API Endpoint
     */
    public string $graphUrl = 'https://graph.facebook.com/';

    /**
     * User Access Token (Short-lived or Long-lived from .env)
     */
    public string $userAccessToken = '';

    public function __construct()
    {
        parent::__construct();

        $this->appId           = getenv('META_APP_ID') ?: $this->appId;
        $this->appSecret        = getenv('META_APP_SECRET') ?: $this->appSecret;
        $this->apiVersion       = getenv('META_API_VERSION') ?: $this->apiVersion;
        $this->igUsername       = getenv('META_IG_USERNAME') ?: $this->igUsername;
        $this->igAccountId     = getenv('META_IG_ACCOUNT_ID') ?: $this->igAccountId;
        $this->userAccessToken = getenv('META_USER_ACCESS_TOKEN') ?: getenv('META_ACCESS_TOKEN') ?: $this->userAccessToken;
    }
}
