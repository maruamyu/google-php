<?php

namespace Maruamyu\Google;

use Maruamyu\Core\Http\Message\Uri;
use Maruamyu\Core\OAuth2\AccessToken;

/**
 * Google OAuth2 client
 */
class OAuth2Client extends \Maruamyu\Core\OAuth2\Client
{
    /** @var ServiceAccount */
    private $serviceAccount;

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param AccessToken $accessToken
     */
    public function __construct($clientId, $clientSecret, AccessToken $accessToken = null)
    {
        $oAuth2Settings = new \Maruamyu\Core\OAuth2\Settings();
        $oAuth2Settings->clientId = $clientId;
        $oAuth2Settings->clientSecret = $clientSecret;
        $oAuth2Settings->authorizationEndpoint = 'https://accounts.google.com/o/oauth2/v2/auth';
        $oAuth2Settings->tokenEndpoint = 'https://www.googleapis.com/oauth2/v4/token';
        $oAuth2Settings->revocationEndpoint = 'https://accounts.google.com/o/oauth2/revoke';
        $oAuth2Settings->isRequiredClientCredentialsOnRevocationRequest = false;
        $oAuth2Settings->isUseBasicAuthorizationOnClientCredentialsRequest = false;

        parent::__construct($oAuth2Settings, $accessToken);
        $this->serviceAccount = null;
    }

    /**
     * @param array $serviceAccountConfig service-account configuration (after json_decode())
     * @return static
     * @throws \Exception if invalid parameter
     */
    public static function createForServiceAccount(array $serviceAccountConfig)
    {
        $serviceAccount = new ServiceAccount($serviceAccountConfig);

        $client = new static($serviceAccount->getClientId(), null);
        $client->serviceAccount = $serviceAccount;
        return $client;
    }

    /**
     * @return boolean
     */
    public function isServiceAccount()
    {
        return isset($this->serviceAccount);
    }

    /**
     * @param string[] $scopes list of scopes
     * @param int $expireSec expire(seconds)
     * @param string $subject account mail address (optional)
     * @return AccessToken|null AccessToken (return null if failed)
     * @throws \Exception if invalid auth-config
     */
    public function requestServiceAccountAuthorizationGrant(array $scopes, $expireSec = 3600, $subject = '')
    {
        if (!($this->isServiceAccount())) {
            throw new \RuntimeException('service account config not set yet.');
        }
        $jsonWebKey = $this->serviceAccount->getJsonWebKey();
        $issuer = $this->serviceAccount->getClientEmail();

        $nowTimestamp = time();
        $expireAtTimestamp = $nowTimestamp + $expireSec;

        $optionalParameters = [
            'iat' => $nowTimestamp,
        ];

        return $this->requestJwtBearerGrant($jsonWebKey, $issuer, $subject, $expireAtTimestamp, $scopes, $optionalParameters);
    }

    /**
     * revoke access token
     *
     * @return boolean true if revoked
     * @throws \Exception if invalid settings
     */
    public function revokeAccessToken()
    {
        if (isset($this->settings->revocationEndpoint) == false) {
            throw new \RuntimeException('revocationEndpoint not set yet.');
        }
        if ($this->accessToken) {
            $parameters = [
                'token' => $this->accessToken->getToken(),
            ];
            $uri = new Uri($this->settings->revocationEndpoint);
            $this->httpClient->request('GET', $uri->withQueryString($parameters));
        }
        $this->accessToken = null;
        return true;
    }
}
