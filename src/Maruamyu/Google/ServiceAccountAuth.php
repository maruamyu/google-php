<?php

namespace Maruamyu\Google;

use Maruamyu\Core\Http\Client as HttpClient;
use Maruamyu\Core\Http\Message\QueryString;
use Maruamyu\Core\OAuth2\AccessToken;
use Maruamyu\Core\OAuth2\JsonWebKey;
use Maruamyu\Core\OAuth2\JsonWebToken;

/**
 * Google Service Account Authentication
 */
class ServiceAccountAuth
{
    const ENDPOINT_URL = 'https://www.googleapis.com/oauth2/v4/token';

    private $authConfig;

    private $latestResponse;

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
        if ($authConfig['type'] !== 'service_account') {
            throw new \RuntimeException('invalid type=' . $authConfig['type']);
        }
        $this->authConfig = $authConfig;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return strval($this->authConfig['client_id']);
    }

    /**
     * @param string[] $scopes list of scopes
     * @param int $expireSec expire(seconds)
     * @param string $sub sub account mail address
     * @return AccessToken|null AccessToken (return null if failed)
     * @throws \Exception if invalid auth-config
     */
    public function fetchAccessToken(array $scopes, $expireSec = 3600, $sub = '')
    {
        $scope = join(' ', $scopes);
        $nowTimestamp = time();
        $expireAtTimestamp = $nowTimestamp + $expireSec;

        $jsonWebKey = JsonWebKey::createFromRsaPrivateKey($this->authConfig['private_key'],
            null, $this->authConfig['private_key_id'], 'RS256');

        $jwtClaimSet = [
            'iss' => $this->authConfig['client_email'],
            'scope' => $scope,
            'aud' => static::ENDPOINT_URL,
            'exp' => $expireAtTimestamp,
            'iat' => $nowTimestamp,
        ];
        if ($sub) {
            $jwtClaimSet['sub'] = $sub;
        }

        $queryParameters = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => JsonWebToken::build($jwtClaimSet, $jsonWebKey),
        ];
        $requestBody = QueryString::build($queryParameters);

        $httpClient = new HttpClient();
        $response = $httpClient->request('POST', static::ENDPOINT_URL, ['body' => $requestBody]);
        $this->latestResponse = $response;
        if ($response->statusCodeIsOk() == false) {
            return null;
        }
        $responseBody = strval($response->getBody());
        if (strlen($responseBody) < 1) {
            return null;
        }

        $tokenData = json_decode($responseBody, true);
        if (empty($tokenData)) {
            return null;
        }
        $tokenData['scope'] = $scope;
        return new AccessToken($tokenData);
    }
}
