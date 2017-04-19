<?php
use Airmee\PhpSdk\Core\Exceptions\InvalidArgumentException;
use Airmee\PhpSdk\Core\Models\TimeRange;

/**
 * @file TimeRangeTest.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 *
 * @coversDefaultClass \Airmee\PhpSdk\Core\Models\TimeRange
 */
class TimeRangeTest extends PHPUnit_Framework_TestCase
{
    public function createCantCreateEmptyDataProvider()
    {
        return [
            [null, null],
            [1489388400, null],
            [null, 1489431600],
            ['', ''],
            [1489388400, ''],
            ['', 1489431600],
            [1489431600, 1489388400], // Inverted
        ];
    }

    /**
     * @group models
     * @group timerange
     * @dataProvider createCantCreateEmptyDataProvider
     * @param $start
     * @param $end
     */
    public function testCreateCantCreateEmpty($start, $end)
    {
        $this->expectException(InvalidArgumentException::class);
        $timeRange = new TimeRange($start, $end);
        $this->assertNull($timeRange);
    }

    /**
     * @group models
     * @group timerange
     */
    public function testCreateCanCreateFull()
    {
        $timeRange = new TimeRange(1489388400, 1489431600);
        $this->assertNotNull($timeRange);

        $this->assertInstanceOf(DateTime::class, $timeRange->getStart());
        $this->assertEquals(1489388400, $timeRange->getStart()->format('U'));
        $this->assertInstanceOf(DateTime::class, $timeRange->getEnd());
        $this->assertEquals(1489431600, $timeRange->getEnd()->format('U'));
    }

    /**
     * @group models
     * @group timerange
     */
    public function testCreateCanCreateFullWithFormatted()
    {
        $timeRange = new TimeRange(1489388400, 1489431600, '13th March (07:00 - 19:00)');
        $this->assertNotNull($timeRange);

        $this->assertInstanceOf(DateTime::class, $timeRange->getStart());
        $this->assertEquals(1489388400, $timeRange->getStart()->format('U'));
        $this->assertInstanceOf(DateTime::class, $timeRange->getEnd());
        $this->assertEquals(1489431600, $timeRange->getEnd()->format('U'));
        $this->assertEquals('13th March (07:00 - 19:00)', $timeRange->getFormatted());
    }
}