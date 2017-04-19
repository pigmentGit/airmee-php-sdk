<?php
use Airmee\PhpSdk\Core\Models\Order;

/**
 * @file OrderTest.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 *
 * @coversDefaultClass \Airmee\PhpSdk\Core\Models\Schedule
 */
class OrderTest extends PHPUnit_Framework_TestCase
{

    /**
     * @group models
     * @group order
     */
    public function testCreate()
    {
        $order = new Order(123456, 'tracking.airmee.com/track?foobar');
        
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(123456, $order->getId());
        $this->assertEquals('tracking.airmee.com/track?foobar', $order->getTrackingUrl());
    }
}