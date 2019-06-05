<?php

namespace Maruamyu\Google\OpenID;

use Maruamyu\Core\OAuth2\AccessToken;
use Maruamyu\Core\OAuth2\OpenIDProviderMetadata;

/**
 * Google OpenID client
 */
class Client extends \Maruamyu\Core\OAuth2\Client
{
    const OPENID_ISSUER = 'https://accounts.google.com';

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param AccessToken $accessToken
     */
    public function __construct($clientId, $clientSecret, AccessToken $accessToken = null)
    {
        # $metadata = static::fetchOpenIDProviderMetadata(static::OPENID_ISSUER);
        $metadata = [
            'issuer' => static::OPENID_ISSUER,
            'authorization_endpoint' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_endpoint' => 'https://oauth2.googleapis.com/token',
            'userinfo_endpoint' => 'https://openidconnect.googleapis.com/v1/userinfo',
            'revocation_endpoint' => 'https://oauth2.googleapis.com/revoke',
            'jwks_uri' => 'https://www.googleapis.com/oauth2/v3/certs',
            'response_types_supported' => ['code', 'token', 'id_token', 'code token', 'code id_token', 'token id_token', 'code token id_token', 'none'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'scopes_supported' => ['openid', 'email', 'profile'],
            'token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic'],
            'claims_supported' => ['aud', 'email', 'email_verified', 'exp', 'family_name', 'given_name', 'iat', 'iss', 'locale', 'name', 'picture', 'sub'],
            'code_challenge_methods_supported' => ['plain', 'S256']
        ];
        $openIDSettings = new OpenIDProviderMetadata($metadata);
        $openIDSettings->clientId = $clientId;
        $openIDSettings->clientSecret = $clientSecret;

        parent::__construct($openIDSettings, $accessToken);
    }

    /**
     * @return Data\Userinfo|null
     * @throws \Exception if invalid settings
     */
    public function getUserinfo()
    {
        $settings = $this->getOpenIDSettings();
        $response = $this->request('GET', $settings->userinfoEndpoint);
        if ($response->statusCodeIsOk() == false) {
            return null;
        }
        $responseBody = strval($response->getBody());
        $userinfo = json_decode($responseBody, true);
        return new Data\Userinfo($userinfo);
    }
}
