<?php
use Airmee\PhpSdk\Core\Exceptions\InvalidArgumentException;
use Airmee\PhpSdk\Core\Models\Item;
use Money\Currency;
use Money\Money;

/**
 * @file ItemTest.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 *
 * @coversDefaultClass \Airmee\PhpSdk\Core\Models\Item
 */
class ItemTest extends PHPUnit_Framework_TestCase
{
    /**
     * Generate all possible combinations of incomplete properties
     * @return array[]
     */
    public function createCantCreateEmptyDataProvider()
    {
        $lengths = [42, null, ''];
        $widths = [44, null, ''];
        $heights = [46, null, ''];
        $weights = [48, null, ''];
        $names = ['Epic Box', null, ''];

        $count = 0;
        foreach ($lengths as $length) {
            foreach ($widths as $width) {
                foreach ($heights as $height) {
                    foreach ($weights as $weight) {
                        foreach ($names as $name) {
                            // The very first combination is valid, don't return it
                            if ($count > 0) {
                                yield [$length, $width, $height, $weight, $name];
                            }
                            $count++;
                        }
                    }
                }
            }
        }
    }

    /**
     * @group models
     * @group item
     * @dataProvider createCantCreateEmptyDataProvider
     * @param $length
     * @param $width
     * @param $height
     * @param $weight
     * @param $name
     */
    public function testCreateCantCreateEmpty($length, $width, $height, $weight, $name)
    {
        $this->expectException(InvalidArgumentException::class);
        $item = new Item($length, $width, $height, $weight, new Money(123, new Currency('SEK')), $name);
        $this->assertNull($item);
    }

    /**
     * @group models
     * @group item
     */
    public function testCreateCanCreateFull()
    {
        $item = new Item(
            42,
            44,
            46,
            48,
            new Money(4200, new Currency('SEK')),
            'Epic Box'
        );
        $this->assertNotNull($item);

        $this->assertEquals(42, $item->getLength());
        $this->assertEquals(44, $item->getWidth());
        $this->assertEquals(46, $item->getHeight());
        $this->assertEquals(48, $item->getWeight());
        $this->assertEquals('Epic Box', $item->getName());

        $this->assertInstanceOf(Money::class, $item->getValue());
        $this->assertEquals(4200, $item->getValue()->getAmount());
        $this->assertEquals('SEK', $item->getValue()->getCurrency()->getCode());

        $this->assertEquals(1, $item->getQuantity());
    }

    public function createMultipleQuantityDataProvider()
    {
        return [
            [null, 1],
            [1, 1],
            [2, 2],
            [42, 42],
            ['42', 42],
            [3.14, 3],
            ['eleven', 1]
        ];
    }

    /**
     * Test creating Items with quantities
     * @group models
     * @group item
     * @dataProvider createMultipleQuantityDataProvider
     */
    public function testCreateMultipleQuantity($input, $output)
    {
        $item = new Item(
            42,
            44,
            46,
            48,
            new Money(4200, new Currency('SEK')),
            'Epic Box',
            $input
        );
        $this->assertNotNull($item);
        $this->assertEquals($output, $item->getQuantity());
    }
}