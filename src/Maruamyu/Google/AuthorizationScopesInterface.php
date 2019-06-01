<?php

namespace Maruamyu\Google;

/**
 * get OAuth2 scope
 */
interface AuthorizationScopesInterface
{
    /**
     * @return String[] scopes
     */
    public static function getScopes();

    /**
     * @return String[] readonly scopes
     */
    public static function getReadOnlyScopes();

    /**
     * @return String[] read and write scopes
     */
    public static function getReadAndWriteScopes();
}
