<?php

namespace Maruamyu\Google;

use Maruamyu\Core\OAuth2\AccessToken;
use Maruamyu\Core\OAuth2\Client as VanillaOAuth2Client;
use Maruamyu\Core\OAuth2\Settings as OAuth2Settings;

/**
 * Google Service Account Authentication
 */
class ServiceAccountAuth
{
    const DEFAULT_TOKEN_ENDPOINT_URL = 'https://www.googleapis.com/oauth2/v4/token';

    /** @var ServiceAccount */
    private $serviceAccount;

    /** @var VanillaOAuth2Client */
    private $oAuth2Client;

    /**
     * @param string|array $authConfigSrc auth parameter or JSON file path
     * @throws \Exception if invalid parameter
     */
    public function __construct($authConfigSrc)
    {
        if (is_array($authConfigSrc)) {
            $authConfig = $authConfigSrc;
        } elseif (is_string($authConfigSrc)) {
            $buffer = file_get_contents($authConfigSrc);
            if (!$buffer) {
                throw new \RuntimeException('file error: ' . $authConfigSrc);
            }
            $authConfig = json_decode($buffer, true);
            if (!$authConfig) {
                throw new \RuntimeException('file error: ' . $authConfigSrc);
            }
        } else {
            throw new \InvalidArgumentException('invalid auth config data: ' . gettype($authConfigSrc));
        }

        $this->serviceAccount = new ServiceAccount($authConfig);

        $oAuth2Settings = new OAuth2Settings();
        $oAuth2Settings->clientId = $authConfig['client_id'];
        if (isset($authConfig['token_uri'])) {
            $oAuth2Settings->tokenEndpoint = $authConfig['token_uri'];
        } else {
            $oAuth2Settings->tokenEndpoint = static::DEFAULT_TOKEN_ENDPOINT_URL;
        }
        $this->oAuth2Client = new VanillaOAuth2Client($oAuth2Settings);
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->serviceAccount->getClientId();
    }

    /**
     * @param string[] $scopes list of scopes
     * @param int $expireSec expire(seconds)
     * @param string $subject account mail address
     * @return AccessToken|null AccessToken (return null if failed)
     * @throws \Exception if invalid auth-config
     */
    public function fetchAccessToken(array $scopes, $expireSec = 3600, $subject = '')
    {
        $jsonWebKey = $this->serviceAccount->getJsonWebKey();
        $issuer = $this->serviceAccount->getClientEmail();

        $nowTimestamp = time();
        $expireAtTimestamp = $nowTimestamp + $expireSec;

        $optionalParameters = [
            'iat' => $nowTimestamp,
        ];

        return $this->oAuth2Client->requestJwtBearerGrant($jsonWebKey, $issuer, $subject, $expireAtTimestamp, $scopes, $optionalParameters);
    }
}
