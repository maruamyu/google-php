<?php

namespace Maruamyu\Google;

/**
 * Google Service Account Authorization Grant Logic
 */
trait ServiceAccountAuthorizationGrantTrait
{
    /**
     * @param int $expiresIn expire(seconds)
     * @return bool return true if succeeded
     */
    public function requestReadOnlyAccessForServiceAccount($expiresIn = 3600)
    {
        $scopes = static::getReadOnlyScopes();
        try {
            $accessToken = $this->requestServiceAccountAuthorizationGrant($scopes, $expiresIn);
        } catch (\Exception $exception) {
            return false;
        }
        return !!($accessToken);
    }

    /**
     * @param int $expiresIn expire(seconds)
     * @return bool return true if succeeded
     */
    public function requestReadAndWriteAccessForServiceAccount($expiresIn = 3600)
    {
        $scopes = static::getReadAndWriteScopes();
        try {
            $accessToken = $this->requestServiceAccountAuthorizationGrant($scopes, $expiresIn);
        } catch (\Exception $exception) {
            return false;
        }
        return !!($accessToken);
    }
}
