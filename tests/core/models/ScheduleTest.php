<?php
use Airmee\PhpSdk\Core\Models\Schedule;
use Airmee\PhpSdk\Core\Models\TimeRange;

/**
 * @file ScheduleTest.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 *
 * @coversDefaultClass \Airmee\PhpSdk\Core\Models\Schedule
 */
class ScheduleTest extends PHPUnit_Framework_TestCase
{

    /**
     * @group models
     * @group schedule
     */
    public function testCreate()
    {
        $pickup = new TimeRange(1490767200, 1490810400);
        $dropoff = new TimeRange(1490767200, 1490781600);
        
        $schedule = new Schedule($pickup, $dropoff);
        
        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertInstanceOf(TimeRange::class, $schedule->getPickup());
        $this->assertEquals(1490767200, $schedule->getPickup()->getStart()->format('U'));
        $this->assertEquals(1490810400, $schedule->getPickup()->getEnd()->format('U'));
        $this->assertInstanceOf(TimeRange::class, $schedule->getDropoff());
        $this->assertEquals(1490767200, $schedule->getDropoff()->getStart()->format('U'));
        $this->assertEquals(1490781600, $schedule->getDropoff()->getEnd()->format('U'));
    }
}