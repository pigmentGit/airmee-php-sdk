<?php
use Airmee\PhpSdk\Core\Models\Address;
use Airmee\PhpSdk\Core\Exceptions\InvalidArgumentException;

/**
 * @file AddressTest.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 *
 * @coversDefaultClass \Airmee\PhpSdk\Core\Models\Address
 */
class AddressTest extends PHPUnit_Framework_TestCase
{
    public function createCantCreateEmptyDataProvider()
    {
        return [
            [null, null],
            [null, 'Sweden'],
            ['123456', null],
            ['', ''],
            ['', 'Sweden'],
            ['123456', ''],
        ];
    }

    /**
     * @group models
     * @group address
     * @dataProvider createCantCreateEmptyDataProvider
     * @param $zipCode
     * @param $country
     */
    public function testCreateCantCreateEmpty($zipCode, $country)
    {
        $this->expectException(InvalidArgumentException::class);
        $address = new Address($zipCode, $country);
        $this->assertNull($address);
    }

    public function createInvalidCountryDataProvider()
    {
        return [
            [null],
            [''],
            ['Atlantis'],
            ['USSR'],
            ['Bosnia'],
            [123456],
            ['XK'],
            ['EU'],
            ['DD'], // East Germany
        ];
    }

    /**
     * @group models
     * @group address
     * @dataProvider createInvalidCountryDataProvider
     * @param $country
     */
    public function testCreateInvalidCountry($country)
    {
        $this->expectException(InvalidArgumentException::class);
        $address = new Address('123456', $country);
        $this->assertNull($address);
    }

    public function createValidCountryDataProvider()
    {
        return [
            ['Sweden'],
            ['SE'],
            ['United Kingdom'],
            ['South Sudan'], // Newest country
            ['GB'],
        ];
    }

    /**
     * @group models
     * @group address
     * @dataProvider createValidCountryDataProvider
     * @param $country
     */
    public function testCreateCanCreateMinimal($country)
    {
        $address = new Address('123456', $country);
        $this->assertNotNull($address);
    }

    public function createBothStreetAndCityMustBeSpecifiedDataProvider()
    {
        return [
            [null, 'Stockholm'],
            ['42 Somewhere Street', null],
            ['', 'Stockholm'],
            ['42 Somewhere Street', '']
        ];
    }

    /**
     * @group models
     * @group address
     * @dataProvider createBothStreetAndCityMustBeSpecifiedDataProvider
     * @param $streetAndNumber
     * @param $city
     */
    public function testCreateBothStreetAndCityMustBeSpecified($streetAndNumber, $city)
    {
        $this->expectException(InvalidArgumentException::class);
        $address = new Address('123456', 'Sweden', $streetAndNumber, $city);
        $this->assertNull($address);
    }

    /**
     * @group models
     * @group address
     */
    public function testCreateCanCreateFull()
    {
        $address = new Address('123456', 'Sweden', '42 Somewhere Street', 'Stockholm');
        $this->assertNotNull($address);
    }
}