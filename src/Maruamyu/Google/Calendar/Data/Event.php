<?php

namespace Maruamyu\Google\Calendar\Data;

class Event
{
    const KIND = 'calendar#event';

    const STATUS_CANCELLED = 'cancelled';

    /** @var array */
    protected $data;

    /** @var \DateTimeZone|null */
    protected $calendarTimeZone;

    /** @var \DateTimeImmutable */
    private $startAt;

    /** @var \DateTimeImmutable */
    private $endAt;

    /**
     * @param array $data
     * @param \DateTimeZone $calendarTimeZone
     */
    public function __construct(array $data = null, \DateTimeZone $calendarTimeZone = null)
    {
        if (is_null($data)) {
            $data = static::getDefaultValues();
        } else {
            static::validate($data);
        }
        $this->data = $data;
        $this->calendarTimeZone = $calendarTimeZone;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * @return string iCalUID
     */
    public function getUniqueId()
    {
        return $this->data['iCalUID'];
    }

    /**
     * @return boolean true if canceled
     */
    public function isCancelled()
    {
        return (
            isset($this->data['status'])
            && (strcasecmp($this->data['status'], static::STATUS_CANCELLED) == 0)
        );
    }

    /**
     * alias of isCanceled()
     *
     * @return boolean true if canceled
     * @see isCanceled()
     */
    public function isDeleted()
    {
        return $this->isCancelled();
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->data['summary'];
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        if (isset($this->data['description'])) {
            return $this->data['description'];
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        if (isset($this->data['location'])) {
            return $this->data['location'];
        } else {
            return '';
        }
    }

    /**
     * @return string place name without address
     */
    public function getLocationName()
    {
        $locationName = $this->getLocation();
        list($locationName) = explode("\n", $locationName, 2);  # iOS "{location_name}\n{address}"
        list($locationName) = explode(', ', $locationName, 2);  # Google "{location_name}, {address}"
        return $locationName;
    }

    /**
     * @return string
     */
    public function getHtmlLink()
    {
        if (isset($this->data['htmlLink'])) {
            $url = $this->data['htmlLink'];
            if ($this->calendarTimeZone) {
                $url .= '&ctz=' . rawurlencode($this->calendarTimeZone->getName());
            }
            return $url;
        } else {
            return '';
        }
    }

    /**
     * get created at on calendar timezone
     *
     * @return \DateTimeImmutable
     * @throws \Exception if invalid data
     */
    public function getCreatedAt()
    {
        return new \DateTimeImmutable($this->data['created'], $this->calendarTimeZone);
    }

    /**
     * get updated at on calendar timezone
     *
     * @return \DateTimeImmutable
     * @throws \Exception if invalid data
     */
    public function getUpdatedAt()
    {
        return new \DateTimeImmutable($this->data['updated'], $this->calendarTimeZone);
    }

    /**
     * start time
     * if date only, then use calendar timezone
     *
     * example1: start.date = '2018-01-01' -> return 2018-01-01 00:00:00 in $this->calendarTimeZone
     * example2: start.dateTime = '2018-01-01T00:00:00+09:00' -> return 2018-01-01T00:00:00+09:00
     *
     * @return \DateTimeImmutable
     * @throws \Exception if invalid data
     */
    public function getStartAt()
    {
        if (!$this->startAt) {
            if (isset($this->data['start']['date'])) {
                $this->startAt = new \DateTimeImmutable($this->data['start']['date'], $this->calendarTimeZone);
            } else {
                $this->startAt = new \DateTimeImmutable($this->data['start']['dateTime']);
            }
        }
        return $this->startAt;
    }

    /**
     * end time
     * if date only, then use calendar timezone
     *
     * example1: end.date = '2018-01-01' -> return 2018-01-01 00:00:00 in $this->calendarTimeZone
     * example2: end.dateTime = '2018-01-01T00:00:00+09:00' -> return 2018-01-01T00:00:00+09:00
     *
     * @return \DateTimeImmutable
     * @throws \Exception if invalid data
     */
    public function getEndAt()
    {
        if (!$this->endAt) {
            if (isset($this->data['end']['date'])) {
                $this->endAt = new \DateTimeImmutable($this->data['end']['date'], $this->calendarTimeZone);
            } else {
                $this->endAt = new \DateTimeImmutable($this->data['end']['dateTime']);
            }
        }
        return $this->endAt;
    }

    /**
     * @return boolean true if date only
     */
    public function isDateOnly()
    {
        return (
            isset($this->data['start']['date'])
            && isset($this->data['end']['date'])
            && (isset($this->data['start']['dateTime']) == false)
            && (isset($this->data['end']['dateTime']) == false)
        );
    }

    /**
     * @param array $data calendar Event item
     * @throws \UnexpectedValueException if invalid data
     */
    protected static function validate($data)
    {
        if ((is_array($data) == false) || empty($data)) {
            throw new \UnexpectedValueException('data is invalid. (empty or not array)');
        }

        # if cancelled
        if ((isset($data['status']) == false) || (strcasecmp($data['status'], static::STATUS_CANCELLED) == 0)) {
            return;
        }

        if ((isset($data['kind']) == false) || (strcasecmp($data['kind'], static::KIND) != 0)) {
            throw new \UnexpectedValueException('kind is invalid.');
        }
        if (
            (isset($data['start']) == false) || (is_array($data['start']) == false) || empty($data['start'])
            || ((isset($data['start']['date']) == false) && (isset($data['start']['dateTime']) == false))
        ) {
            throw new \UnexpectedValueException('start is invalid. (empty or not array)');
        }
        if (
            (isset($data['end']) == false) || (is_array($data['end']) == false) || empty($data['end'])
            || ((isset($data['end']['date']) == false) && (isset($data['end']['dateTime']) == false))
        ) {
            throw new \UnexpectedValueException('end is invalid. (empty or not array)');
        }
        if ((isset($data['summary']) == false) || (strlen($data['summary']) < 1)) {
            throw new \UnexpectedValueException('summary is blank.');
        }
    }

    /**
     * @return array
     */
    protected static function getDefaultValues()
    {
        $nowTimestamp = time();
        $nowDateTime = date(\DateTime::RFC3339, $nowTimestamp);
        $nowDate = date('Y-m-d', $nowTimestamp);
        return [
            'kind' => static::KIND,
            'iCalUID' => '',
            'created' => $nowDateTime,
            'updated' => $nowDateTime,
            'summary' => '',
            'description' => '',
            'location' => '',
            'start' => ['date' => $nowDate],
            'end' => ['date' => $nowDate],
        ];
    }
}
