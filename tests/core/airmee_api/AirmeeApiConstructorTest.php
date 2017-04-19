<?php
use Airmee\PhpSdk\Core\AirmeeApi;
use Airmee\PhpSdk\Core\Exceptions\ApiConfigurationException;

/**
 * @file AirmeeApiConstructorTest.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 *
 * @coversDefaultClass \Airmee\PhpSdk\Core\Models\AirmeeApi
 */
class AirmeeApiConstructorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @before
     */
    public function initialise()
    {
    }

    public function invalidPlaceIdDataProvider()
    {
        return [
            [null],
            [42],
            ['notafilepath'],
            ['/path/to/nowhere'],
        ];
    }

    /**
     * @group airmee_api
     * @group constructor
     * @dataProvider invalidPlaceIdDataProvider
     * @param $config
     */
    public function testInvalidConfigurationOption($config)
    {
        $this->expectException(ApiConfigurationException::class);
        $airmeeApi = new AirmeeApi($config);
        $this->assertNull($airmeeApi);
    }

    /**
     * @group airmee_api
     * @group constructor
     * Test that it's possible to create the AirmeeApi object by passing in a configuration array
     */
    public function testArrayConfigurationOption()
    {
        $airmeeApi = new AirmeeApi(['sandbox' => true]);
        $this->assertInstanceOf(AirmeeApi::class, $airmeeApi);
        $this->assertEquals(true, $airmeeApi->isSandbox());

        $airmeeApi = new AirmeeApi(['sandbox' => false]);
        $this->assertInstanceOf(AirmeeApi::class, $airmeeApi);
        $this->assertEquals(false, $airmeeApi->isSandbox());
    }

    /**
     * @group airmee_api
     * @group constructor
     * Check that an error is thrown if supplying a configuration file that's not readable
     */
    public function testUnreadableIniConfigurationOption()
    {
        $iniData = <<<ENDL
sandbox=true
ENDL;

        $tempfile = tmpfile();
        $path = stream_get_meta_data($tempfile)['uri']; // eg: /tmp/phpFx0513a
        fwrite($tempfile, $iniData);
        chmod($path, 0);

        $this->expectException(ApiConfigurationException::class);
        $airmeeApi = new AirmeeApi($path);
        $this->assertNull($airmeeApi);

        fclose($tempfile);
    }

    /**
     * @group airmee_api
     * @group constructor
     * Check that an error is thrown if supplying a configuration file that's not readable
     */
    public function testCorruptedIniConfigurationOption()
    {
        $iniData = <<<ENDL
[invalid
ENDL;

        $tempfile = tmpfile();
        $path = stream_get_meta_data($tempfile)['uri']; // eg: /tmp/phpFx0513a
        fwrite($tempfile, $iniData);

        // This scenario *should* throw a warning
        PHPUnit_Framework_Error_Warning::$enabled = false;

        $this->expectException(ApiConfigurationException::class);
        try {
            $airmeeApi = new AirmeeApi($path);
            $this->assertNull($airmeeApi);
        } finally {
            // Need to re-enable
            PHPUnit_Framework_Error_Warning::$enabled = true;
        }

        fclose($tempfile);
    }

    /**
     * @group airmee_api
     * @group constructor
     * Test that it's possible to create the AirmeeApi object by passing the path to a configuration .ini file
     */
    public function testIniConfigurationOption1()
    {
        $iniData = <<<ENDL
sandbox=true
ENDL;

        $tempfile = tmpfile();
        $path = stream_get_meta_data($tempfile)['uri']; // eg: /tmp/phpFx0513a
        fwrite($tempfile, $iniData);

        $airmeeApi = new AirmeeApi($path);
        $this->assertInstanceOf(AirmeeApi::class, $airmeeApi);
        $this->assertEquals(true, $airmeeApi->isSandbox());

        fclose($tempfile);
    }

    /**
     * @group airmee_api
     * @group constructor
     * Test that it's possible to create the AirmeeApi object by passing the path to a configuration .ini file
     */
    public function testIniConfigurationOption2()
    {
        $iniData = <<<ENDL
sandbox=false
ENDL;

        $tempfile = tmpfile();
        $path = stream_get_meta_data($tempfile)['uri']; // eg: /tmp/phpFx0513a
        fwrite($tempfile, $iniData);

        $airmeeApi = new AirmeeApi($path);
        $this->assertInstanceOf(AirmeeApi::class, $airmeeApi);
        $this->assertEquals(false, $airmeeApi->isSandbox());

        fclose($tempfile);
    }
}