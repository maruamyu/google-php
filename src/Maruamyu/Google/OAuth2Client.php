<?php

namespace Maruamyu\Google;

use Maruamyu\Core\Http\Message\Request;
use Maruamyu\Core\Http\Message\Uri;
use Maruamyu\Core\OAuth2\AccessToken;
use Maruamyu\Core\OAuth2\AuthorizationServerMetadata;

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
    public static function createInstance($clientId, $clientSecret, AccessToken $accessToken = null)
    {
        $metadataValues = [
            'issuer' => 'https://accounts.google.com',
            'authorization_endpoint' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_endpoint' => 'https://oauth2.googleapis.com/token',
            'revocation_endpoint' => 'https://accounts.google.com/o/oauth2/revoke',
            'token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic'],
            'jwks_uri' => 'https://www.googleapis.com/oauth2/v3/certs',
        ];
        $metadata = new AuthorizationServerMetadata($metadataValues);

        $client = new static($metadata, $clientId, $clientSecret, $accessToken);
        $client->serviceAccount = null;
        return $client;
    }

    /**
     * @param array $serviceAccountConfig service-account configuration (after json_decode())
     * @return static
     * @throws \Exception if invalid parameter
     */
    public static function createForServiceAccount(array $serviceAccountConfig)
    {
        $serviceAccount = new ServiceAccount($serviceAccountConfig);
        $client = static::createInstance($serviceAccount->getClientId(), null);
        $client->serviceAccount = $serviceAccount;
        return $client;
    }

    /**
     * @return bool
     */
    public function isServiceAccount()
    {
        return isset($this->serviceAccount);
    }

    /**
     * @param string[] $scopes list of scopes
     * @param int $expiresIn expire(seconds)
     * @param string $subject account mail address (optional)
     * @return AccessToken|null AccessToken (return null if failed)
     * @throws \Exception if invalid auth-config
     */
    public function requestServiceAccountAuthorizationGrant(array $scopes, $expiresIn = 3600, $subject = '')
    {
        if (!($this->isServiceAccount())) {
            throw new \RuntimeException('service account config not set yet.');
        }
        $jsonWebKey = $this->serviceAccount->getJsonWebKey();
        $issuer = $this->serviceAccount->getClientEmail();

        $nowTimestamp = time();
        $expiresAtTimestamp = $nowTimestamp + $expiresIn;

        $optionalParameters = [
            'iat' => $nowTimestamp,
        ];

        return $this->requestJwtBearerGrant($jsonWebKey, $issuer, $subject, $expiresAtTimestamp, $scopes, $optionalParameters);
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
}
