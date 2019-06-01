<?php

namespace Maruamyu\Google\Calendar\Data;

class EventTest extends \PHPUnit\Framework\TestCase
{
    const FIXTURE = [
        'kind' => 'calendar#event',
        'id' => 'uniqueid',
        'iCalUID' => 'uniqueid@google.com',
        'status' => 'confirmed',
        'created' => '2019-01-01T00:00:00.000Z',
        'updated' => '2019-01-01T00:00:00.000Z',
        'summary' => 'summary_of_event',
        'description' => 'description of event',
        'location' => 'location_of_event',
        'start' => ['date' => '2019-01-01'],
        'end' => ['date' => '2019-01-02'],
        'htmlLink' => 'https://example.jp/calendar/event/uniqueid',
    ];

    public function test_initialize()
    {
        $event = new Event(self::FIXTURE);
        $this->assertFalse($event->isDeleted());
        $this->assertEquals(self::FIXTURE['summary'], $event->getSummary());
        $this->assertEquals(self::FIXTURE['description'], $event->getDescription());
        $this->assertEquals(self::FIXTURE['htmlLink'], $event->getHtmlLink());
    }

    public function test_location()
    {
        # iOS style
        $data_ios = self::FIXTURE;
        $data_ios['location'] = 'location_name' . "\n" . 'location_address';
        $event_ios = new Event($data_ios);
        $this->assertEquals($data_ios['location'], $event_ios->getLocation());
        $this->assertEquals('location_name', $event_ios->getLocationName());

        # Google style
        $data_google = self::FIXTURE;
        $data_google['location'] = 'location_name, location_address';
        $event_google = new Event($data_google);
        $this->assertEquals('location_name', $event_google->getLocationName());
    }

    public function test_created_at_updated_at()
    {
        $event_utc = new Event(self::FIXTURE);
        $this->assertEquals(strtotime('2019-01-01 00:00:00 +00:00'), $event_utc->getCreatedAt()->getTimestamp());
        $this->assertEquals(strtotime('2019-01-01 00:00:00 +00:00'), $event_utc->getUpdatedAt()->getTimestamp());

        $dateTimeZone = new \DateTimeZone('Asia/Tokyo');
        $event_jst = new Event(self::FIXTURE, $dateTimeZone);
        $this->assertEquals(strtotime('2019-01-01 09:00:00 +09:00'), $event_jst->getCreatedAt()->getTimestamp());
        $this->assertEquals(strtotime('2019-01-01 09:00:00 +09:00'), $event_jst->getUpdatedAt()->getTimestamp());
    }

    public function test_start_at_end_at()
    {
        $dateTimeZone = new \DateTimeZone('Asia/Tokyo');
        $event_jst = new Event(self::FIXTURE, $dateTimeZone);
        $this->assertEquals(strtotime('2019-01-01 00:00:00 +09:00'), $event_jst->getStartAt()->getTimestamp());
        $this->assertEquals(strtotime('2019-01-02 00:00:00 +09:00'), $event_jst->getEndAt()->getTimestamp());
    }
}
