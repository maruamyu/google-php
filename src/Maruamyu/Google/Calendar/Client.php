<?php

namespace Maruamyu\Google\Calendar;

use Maruamyu\Core\Http\Message\Uri;
use Maruamyu\Google\OAuth2Client;

/**
 * Google Calendar Client
 */
class Client extends OAuth2Client
{
    const API_ENDPOINT_ROOT = 'https://www.googleapis.com/calendar/';

    const SCOPE = 'https://www.googleapis.com/auth/calendar';

    /**
     * @return String[] scopes
     */
    public static function getScopes()
    {
        return [static::SCOPE];
    }

    /**
     * @return String[] readonly scopes
     */
    public static function getReadOnlyScopes()
    {
        return [static::SCOPE . '.readonly'];
    }

    /**
     * @param string $calendarId
     * @param \DateTimeInterface $startAt
     * @param \DateTimeInterface $endAt
     * @param string $pageToken
     * @param array $options
     * @return string response (JSON string)
     * @throws \RuntimeException if failed
     */
    public function getEvents($calendarId, \DateTimeInterface $startAt, \DateTimeInterface $endAt, $pageToken = '', array $options = null)
    {
        $parameters = [
            'timeMin' => $startAt->format(\DateTime::RFC3339),
            'timeMax' => $endAt->format(\DateTime::RFC3339),
        ];
        if ($pageToken) {
            $parameters['pageToken'] = $pageToken;
        }
        if (!empty($options)) {
            $parameters = array_merge($parameters, $options);
        }

        $endpointUri = static::getEndpointUri('v3/calendars/' . rawurlencode($calendarId) . '/events');
        $response = $this->request('GET', $endpointUri->withQueryString($parameters));
        if ($response->statusCodeIsOk() == false) {
            throw new \RuntimeException($response->getBody(), $response->getStatusCode());
        }
        $responseBody = strval($response->getBody());
        if (strlen($responseBody) < 1) {
            throw new \RuntimeException('response body is empty.');
        }
        return $responseBody;
    }

    /**
     * @param string $calendarId
     * @param \DateTimeInterface $updatedMin
     * @param string $pageToken
     * @param array $options
     * @return string response (JSON string)
     * @throws \RuntimeException if failed
     */
    public function getEventsByUpdatedMin($calendarId, \DateTimeInterface $updatedMin, $pageToken = '', array $options = null)
    {
        $parameters = [
            'updatedMin' => $updatedMin->format(\DateTime::RFC3339),
        ];
        if ($pageToken) {
            $parameters['pageToken'] = $pageToken;
        }
        if (!empty($options)) {
            $parameters = array_merge($parameters, $options);
        }

        $endpointUri = static::getEndpointUri('v3/calendars/' . rawurlencode($calendarId) . '/events');
        $response = $this->request('GET', $endpointUri->withQueryString($parameters));
        if ($response->statusCodeIsOk() == false) {
            throw new \RuntimeException($response->getBody(), $response->getStatusCode());
        }
        $responseBody = strval($response->getBody());
        if (strlen($responseBody) < 1) {
            throw new \RuntimeException('response body is empty.');
        }
        return $responseBody;
    }

    /**
     * @param string $path
     * @return Uri
     */
    protected static function getEndpointUri($path)
    {
        return new Uri(static::API_ENDPOINT_ROOT . $path);
    }
}
