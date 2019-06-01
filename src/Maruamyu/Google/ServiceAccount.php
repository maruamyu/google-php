<?php

namespace Maruamyu\Google;

use Maruamyu\Core\OAuth2\JsonWebKey;

/**
 * service-account configuration class
 */
class ServiceAccount
{
    /** @var array */
    private $config;

    /** @var JsonWebKey */
    private $jsonWebKey;

    /**
     * @param array $config service-account configuration (after json_decode())
     * @throws \Exception if invalid parameter
     */
    public function __construct(array $config)
    {
        if (empty($config) || isset($config['type']) == false) {
            throw new \InvalidArgumentException('config is empty.');
        }
        if ($config['type'] !== 'service_account') {
            throw new \RuntimeException('invalid type=' . $config['type']);
        }
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return strval($this->config['client_id']);
    }

    /**
     * @return string
     */
    public function getClientEmail()
    {
        return strval($this->config['client_email']);
    }

    /**
     * @return string
     */
    public function getPrivateKey()
    {
        return strval($this->config['private_key']);
    }

    /**
     * @return string
     */
    public function getPrivateKeyId()
    {
        return strval($this->config['private_key_id']);
    }

    /**
     * @return JsonWebKey
     * @throws \Exception if service account config not set yet
     */
    public function getJsonWebKey()
    {
        if (isset($this->jsonWebKey) == false) {
            $privateKey = $this->getPrivateKey();
            $privateKeyId = $this->getPrivateKeyId();
            if ((strlen($privateKey) < 1) || (strlen($privateKeyId) < 1)) {
                throw new \RuntimeException('private key not set yet.');
            }
            $this->jsonWebKey = JsonWebKey::createFromPrivateKey($privateKey, null, $privateKeyId);
        }
        return $this->jsonWebKey;
    }
}
