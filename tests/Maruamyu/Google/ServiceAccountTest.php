<?php

namespace Maruamyu\Google;

class ServiceAccountTest extends \PHPUnit\Framework\TestCase
{
    const FIXTURE = [
        'type' => 'service_account',
        'project_id' => 'project-imas',
        'private_key_id' => 'jvjgOx52gHMX4PkkZZ2RHaUDqui_vHItTOAvpT4rH6o',
        'private_key' => "-----BEGIN RSA PRIVATE KEY-----\nMIICXAIBAAKBgQC+n6T/My/T8qJfQKfUv9VL3nmnj/Z6xwf4BLh3k5RgRVAANDe7\nYMcnwBmSbueRHe1sXPpDB33dxc6xv9+ZKLafukyUjkb1yQHoUzz1e3dXxybnf7eS\nGmXHDtJw5LP4zVoETRzwIeA0bS2e/su2UEextg2GfcyiFUEf0mIBle1CEQIDAQAB\nAoGAXtAfDEQUdPJJKuGI2Lv1xn/IuLxVV2opn4YRjoBcG6o+CWvvkIapaC8XSQta\nqIZfMjfoznAqfaVGkoiiGZbzhhEtaXHVfx7QBg6aqDJ0K/EXyCqdaUUyWv8gX7z4\nVkkdUXRpLdgDdriq4JOM+FE5tW2I7eO6WFPQpTOFSWpQHDECQQDdySQSaoupAXG6\nPUhaXH4Z+ZfW6kD00KV7pudJBco2FMn1T4voNGsw0+TiY6JK+ho5a/B35R+OJhRX\n0TGuy7rlAkEA3AfWHSP91rhGa2FN87t/gC2/ubjD67KMfQk6CrJXozP2IzniqDDR\nfR0Mj7MCY1swWFpoFTd/RnC14+SL5RO7vQJAA6bHIEJ+0CaE79MIeOxi6xyP4mry\n7NTulI2X6zzcKm3HMXHA1O7gAOrMLuoDBwb9HYroZ6DvFxELbrK0BbO2/QJAcN7o\nPGyhI4vGPAFfbp+JaWSOjKQ2hOtD3ERmbORNxp+6LRndpq/cVxUWw4RtvjAiHcDK\n0c91T9ozxGMSTIIR9QJBAMLPtvZ0H/aeO22YWvzlwPBk/9vpgMC15KuSMk0UbjNc\nBOYIs/Cp2SbpSn6R++/XJtlKkfVLRfBygukjXP3I/h4=\n-----END RSA PRIVATE KEY-----",
        'client_email' => 'orange-ojisan@project-imas.iam.gserviceaccount.com',
        'client_id' => '765876346315283765876',
        'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
        'token_uri' => 'https://accounts.google.com/o/oauth2/token',
        'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
        'client_x509_cert_url' => 'https://www.googleapis.com/robot/v1/metadata/x509/project-imas.iam.gserviceaccount.com',
    ];

    public function test_initialize()
    {
        $serviceAccount = new ServiceAccount(self::FIXTURE);
        $this->assertEquals(self::FIXTURE['client_id'], $serviceAccount->getClientId());
        $this->assertEquals(self::FIXTURE['client_email'], $serviceAccount->getClientEmail());
        $this->assertEquals(self::FIXTURE['private_key_id'], $serviceAccount->getPrivateKeyId());
        $this->assertEquals(self::FIXTURE['private_key'], $serviceAccount->getPrivateKey());
    }

    public function test_getJsonWebKey()
    {
        $serviceAccount = new ServiceAccount(self::FIXTURE);
        $jsonWebKey = $serviceAccount->getJsonWebKey();

        $this->assertEquals(self::FIXTURE['private_key_id'], $jsonWebKey->getKeyId());

        $this->assertEquals('RSA', $jsonWebKey->getKeyType());

        $actualPrivateKeyPem = strval($jsonWebKey->getSignatureInterface());
        $actualPrivateKey = openssl_pkey_get_private($actualPrivateKeyPem);
        $actualPrivateKeyDetails = openssl_pkey_get_details($actualPrivateKey);
        $expectPrivateKey = openssl_pkey_get_private(self::FIXTURE['private_key']);
        $expectPrivateKeyDetails = openssl_pkey_get_details($expectPrivateKey);
        $this->assertEquals($expectPrivateKeyDetails['rsa'], $actualPrivateKeyDetails['rsa']);
    }
}
