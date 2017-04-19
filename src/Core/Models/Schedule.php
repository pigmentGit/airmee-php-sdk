<?php
/**
 * @file Schedule.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 */

namespace Airmee\PhpSdk\Core\Models;

/**
 * A class representing a possible delivery schedule, with a time range in which the items could
 * be collected from their origin, and a time range in which they would be delivered to their
 * destination.
 */
class Schedule
{
    /** @var TimeRange */
    private $pickup;

    /** @var TimeRange */
    private $dropoff;

    /**
     * Schedule constructor.
     * @param TimeRange $pickup
     * @param TimeRange $dropoff
     */
    public function __construct(TimeRange $pickup, TimeRange $dropoff)
    {
        $this->pickup = $pickup;
        $this->dropoff = $dropoff;
    }

    /**
     * @return TimeRange
     */
    public function getPickup()
    {
        return $this->pickup;
    }

    /**
     * @return TimeRange
     */
    public function getDropoff()
    {
        return $this->dropoff;
    }
}