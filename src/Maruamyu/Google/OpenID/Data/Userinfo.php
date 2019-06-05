<?php

namespace Maruamyu\Google\OpenID\Data;

class Userinfo
{
    /** @var string */
    protected $userId;

    /** @var string */
    protected $pictureUrl;

    /**
     * @param array $response
     */
    public function __construct(array $response = null)
    {
        if (isset($response)) {
            $this->userId = strval($response['sub']);
            $this->pictureUrl = strval($response['picture']);
        } else {
            $this->userId = '';
            $this->pictureUrl = '';
        }
    }

    /**
     * @return string
     */
    public function getuserId()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getpictureUrl()
    {
        return $this->pictureUrl;
    }
}
