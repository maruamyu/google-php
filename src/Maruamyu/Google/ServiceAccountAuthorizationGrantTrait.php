<?php

namespace Maruamyu\Google;

/**
 * Google Service Account Authorization Grant Logic
 */
trait ServiceAccountAuthorizationGrantTrait
{
    /**
     * @param int $expireSec expire(seconds)
     * @return boolean return true if succeeded
     */
    public function requestReadOnlyAccessForServiceAccount($expireSec = 3600)
    {
        $scopes = static::getReadOnlyScopes();
        try {
            $accessToken = $this->requestServiceAccountAuthorizationGrant($scopes, $expireSec);
        } catch (\Exception $exception) {
            return false;
        }
        return !!($accessToken);
    }

    /**
     * @param int $expireSec expire(seconds)
     * @return boolean return true if succeeded
     */
    public function requestReadAndWriteAccessForServiceAccount($expireSec = 3600)
    {
        $scopes = static::getReadAndWriteScopes();
        try {
            $accessToken = $this->requestServiceAccountAuthorizationGrant($scopes, $expireSec);
        } catch (\Exception $exception) {
            return false;
        }
        return !!($accessToken);
    }
}
