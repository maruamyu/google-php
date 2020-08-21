<?php

namespace Maruamyu\Google;

use Maruamyu\Core\OAuth2\AccessToken;
use Maruamyu\Core\OAuth2\AuthorizationServerMetadata;
use Maruamyu\Core\OAuth2\Client as VanillaOAuth2Client;

/**
 * Google Service Account Authentication
 */
class ServiceAccountAuth
{
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

        $metadataValues = [
            'issuer' => 'https://accounts.google.com',
            'token_endpoint' => 'https://www.googleapis.com/oauth2/v4/token',
            'revocation_endpoint' => 'https://accounts.google.com/o/oauth2/revoke',
        ];
        if (isset($authConfig['token_uri'])) {
            $metadataValues['token_endpoint'] = $authConfig['token_uri'];
        }
        $metadata = new AuthorizationServerMetadata($metadataValues);
        $this->oAuth2Client = new VanillaOAuth2Client($metadata, $authConfig['client_id'], null);
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
     * @param int $expiresIn expire(seconds)
     * @param string $subject account mail address
     * @return AccessToken|null AccessToken (return null if failed)
     * @throws \Exception if invalid auth-config
     */
    public function fetchAccessToken(array $scopes, $expiresIn = 3600, $subject = '')
    {
        $jsonWebKey = $this->serviceAccount->getJsonWebKey();
        $issuer = $this->serviceAccount->getClientEmail();

        $nowTimestamp = time();
        $expiresAtTimestamp = $nowTimestamp + $expiresIn;

        $optionalParameters = [
            'iat' => $nowTimestamp,
        ];

        return $this->oAuth2Client->requestJwtBearerGrant($jsonWebKey, $issuer, $subject, $expiresAtTimestamp, $scopes, $optionalParameters);
    }
}
