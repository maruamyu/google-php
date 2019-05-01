<?php

namespace Maruamyu\Google;

use Maruamyu\Core\Http\Message\Uri;
use Maruamyu\Core\OAuth2\AccessToken;

/**
 * Google OAuth2 client
 */
class OAuth2Client extends \Maruamyu\Core\OAuth2\Client
{
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
