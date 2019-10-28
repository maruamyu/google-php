<?php

namespace Maruamyu\Google\OpenID;

use Maruamyu\Core\Http\Message\Request;
use Maruamyu\Core\Http\Message\Uri;
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
    public static function createInstance($clientId, $clientSecret, AccessToken $accessToken = null)
    {
        # $metadata = static::fetchOpenIDProviderMetadata(static::OPENID_ISSUER);
        $metadataValues = [
            'issuer' => static::OPENID_ISSUER,
            'authorization_endpoint' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_endpoint' => 'https://oauth2.googleapis.com/token',
            'userinfo_endpoint' => 'https://openidconnect.googleapis.com/v1/userinfo',
            'revocation_endpoint' => 'https://accounts.google.com/o/oauth2/revoke',
            'jwks_uri' => 'https://www.googleapis.com/oauth2/v3/certs',
            'response_types_supported' => ['code', 'token', 'id_token', 'code token', 'code id_token', 'token id_token', 'code token id_token', 'none'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'scopes_supported' => ['openid', 'email', 'profile'],
            'token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic'],
            'claims_supported' => ['aud', 'email', 'email_verified', 'exp', 'family_name', 'given_name', 'iat', 'iss', 'locale', 'name', 'picture', 'sub'],
            'code_challenge_methods_supported' => ['plain', 'S256'],
        ];
        $metadata = new OpenIDProviderMetadata($metadataValues);

        return new static($metadata, $clientId, $clientSecret, $accessToken);
    }

    /**
     * token revocation request (not use client_credentials)
     *
     * @param string $token
     * @param string $tokenTypeHint 'access_token' or 'refresh_token'
     * @return Request
     * @throws \Exception if invalid settings
     */
    public function makeTokenRevocationRequest($token, $tokenTypeHint = '')
    {
        if (isset($this->metadata->revocationEndpoint) == false) {
            throw new \RuntimeException('revocationEndpoint not set yet.');
        }
        $parameters = [
            'token' => $token,
        ];
        if (strlen($tokenTypeHint) > 0) {
            $parameters['token_type_hint'] = strval($tokenTypeHint);
        }
        $uri = new Uri($this->metadata->revocationEndpoint);
        return new Request('GET', $uri->withQueryString($parameters));
    }

    /**
     * @return Data\Userinfo|null
     * @throws \Exception if invalid settings or access_token
     * @see requestGetUserinfo()
     */
    public function getUserinfo()
    {
        $userinfo = $this->requestGetUserinfo();
        if (empty($userinfo) || isset($userinfo['error'])) {
            return null;
        } else {
            return new Data\Userinfo($userinfo);
        }
    }
}
