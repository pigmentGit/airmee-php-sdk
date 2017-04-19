<?php
use Airmee\PhpSdk\Core\Exceptions\AuthorizationException;
use Airmee\PhpSdk\Core\Exceptions\InvalidArgumentException;
use Airmee\PhpSdk\Core\Exceptions\ServerErrorException;
use Airmee\PhpSdk\Core\Exceptions\UnknownPlaceException;
use Airmee\PhpSdk\Core\Models\Item;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * @file ProductThresholdForPlaceTest.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 *
 * @coversDefaultClass \Airmee\PhpSdk\Core\Models\AirmeeApi
 */
class ProductThresholdForPlaceTest extends AirmeeApiTest
{
    /**
     * Possible invalid place_id values
     * @return array
     */
    public function noPlaceIdDataProvider()
    {
        return [
            [null],
            [''],
        ];
    }

    /**
     * @group airmee_api
     * @group productthresholdforplace
     * @dataProvider noPlaceIdDataProvider
     * @param $placeId
     */
    public function testNoPlaceId($placeId)
    {
        $this->expectException(InvalidArgumentException::class);
        $airmeeApi = $this->getAirmeeApiMock(new Response(418, [], "I'm a teapot"));
        $response = $airmeeApi->productThresholdForPlace($placeId);
        $this->assertNull($response);
    }

    /**
     * Various responses the server could give if it goes completely haywire
     * @return array
     */
    public function serverExplosionDataProvider(){
        return [
            [500, ''],
            [500, 'An error occurred'],
            [500, '{"errorr":"JSON object with typo in key"}'],
            [200, '200 response but not an array'],
            [418, "I'm a teapot"]
        ];
    }

    /**
     * Test that the SDK responds correctly when the server returns a 500 error and an invalid JSON response
     * @group airmee_api
     * @group productthresholdforplace
     * @dataProvider serverExplosionDataProvider
     * @param $code
     * @param $message
     */
    public function testServerExplosion($code, $message)
    {
        $this->expectException(ServerErrorException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('A server error occurred');

        $airmeeApi = $this->getAirmeeApiMock(new Response($code, [], $message));
        $response = $airmeeApi->productThresholdForPlace('123456');

        $this->assertNull($response);
    }

    /**
     * Test that the SDK responds correctly when the server returns a 500 error
     * @group airmee_api
     * @group productthresholdforplace
     */
    public function testServerError()
    {
        $this->expectException(ServerErrorException::class);
        $this->expectExceptionMessage('Our server encountered an error, please retry or contact support.');

        $airmeeApi = $this->getAirmeeApiMock(new Response(500, [], '{"message": "Our server encountered an error, please retry or contact support."}'));
        $response = $airmeeApi->productThresholdForPlace('123456');

        $this->assertNull($response);
    }

    /**
     * Test that the SDK responds correctly when the server returns an InvalidPlace error
     * @group airmee_api
     * @group productthresholdforplace
     */
    public function testDeliveryInvalidPlaceError()
    {
        $this->expectException(UnknownPlaceException::class);
        $this->expectExceptionMessage('Unrecognised place_id');

        $airmeeApi = $this->getAirmeeApiMock(new Response(404, [], '{"message": "Unrecognised place_id"}'));
        $response = $airmeeApi->productThresholdForPlace('123456');

        $this->assertNull($response);
    }

    /**
     * Test that the SDK responds correctly to a mocked successful response
     * @group airmee_api
     * @group productthresholdforplace
     */
    public function testSuccessfulResponseOneSchedule()
    {
        $responseBody = <<<ENDL
{
	"threshold_values": {
		"length": 42.0,
		"width": 44.0,
		"height": 46.0,
		"weight": 48.0
	}
}
ENDL;
        $airmeeApi = $this->getAirmeeApiMock(new Response(200, [], $responseBody));
        $result = $airmeeApi->productThresholdForPlace('123456');

        $this->assertInstanceOf(Item::class, $result);
        $this->assertEquals(42, $result->getLength());
        $this->assertEquals(44, $result->getWidth());
        $this->assertEquals(46, $result->getHeight());
        $this->assertEquals(48, $result->getWeight());
    }

    /**
     * DataProvider for different combinations of sandbox-ness and endpoints
     * @return array
     */
    public function successfulResponseUrlEndpointsDataProvider()
    {
        return [
            [
                ['sandbox' => true],
                'https://staging-api.airmee.com/integration/product_threshold_for_place'
            ],
            [
                ['sandbox' => false],
                'https://api.airmee.com/integration/product_threshold_for_place'
            ],
            [
                [
                    'sandbox' => true,
                    'endpoint.sandbox' => 'http://foo.bar.com',
                    'endpoint.production' => 'https://baz.quok.com'
                ],
                'http://foo.bar.com/product_threshold_for_place'
            ],
            [
                [
                    'sandbox' => false,
                    'endpoint.sandbox' => 'http://foo.bar.com',
                    'endpoint.production' => 'https://baz.quok.com'
                ],
                'https://baz.quok.com/product_threshold_for_place'
            ],
        ];
    }

    /**
     * Test that the SDK calls the right endpoint
     * @group airmee_api
     * @group productthresholdforplace
     * @dataProvider successfulResponseUrlEndpointsDataProvider
     * @param array $config
     * @param $expectedEndpoint
     */
    public function testSuccessfulResponseUrlEndpoints(array $config, $expectedEndpoint)
    {
        $responseBody = <<<ENDL
{
	"threshold_values": {
		"length": 42.0,
		"width": 44.0,
		"height": 46.0,
		"weight": 48.0
	}
}
ENDL;
        $request = [];
        $airmeeApi = $this->getAirmeeApiMock(new Response(200, [], $responseBody), $config, $request);
        $airmeeApi->productThresholdForPlace('123456');

        $this->assertTrue(count($request) == 1);
        /** @var RequestInterface $request */
        $request = $request[0]['request'];
        $this->assertInstanceOf(RequestInterface::class, $request);

        $this->assertEquals('GET', $request->getMethod());

        $this->assertStringStartsWith($expectedEndpoint, (string)$request->getUri());
    }

    /**
     * Test that the SDK responds correctly when there's an authorization error
     * @group airmee_api
     * @group productthresholdforplace
     */
    public function testAuthorizationError()
    {
        $responseBody = <<<ENDL
{"message": "Unauthorized"}
ENDL;
        $request = [];
        $airmeeApi = $this->getAirmeeApiMock(
            new Response(401, [], $responseBody),
            ['auth.jwt' => 'abcdef'],
            $request
        );

        try {
            $airmeeApi->productThresholdForPlace('123456');
            $this->fail('Should raise an exception');
        } catch (AuthorizationException $e) {

        }

        $this->assertTrue(count($request) == 1);
        /** @var RequestInterface $request */
        $request = $request[0]['request'];
        $this->assertInstanceOf(RequestInterface::class, $request);

        $this->assertArrayHasKey('Authorization', $request->getHeaders());
        $this->assertEquals('abcdef', $request->getHeader('Authorization')[0]);
    }

    /********************************************************************************************************/
    // LIVE TESTS
    /********************************************************************************************************/

    /**
     * Test the SDK against a live endpoint, expecting a successful request
     * @group airmee_api
     * @group live
     * @group productthresholdforplace
     */
    public function testProductThresholdForPlaceTestSuccessfulResponse()
    {
        list($placeId, $jwtToken) = $this->getCredentials();
        $airmeeApi = $this->getAirmeeApi(['auth.jwt' => $jwtToken]);
        $schedules = $airmeeApi->productThresholdForPlace($placeId);

        $this->assertNotNull($schedules);
    }

    public function deliveryIntervalsAuthMismatchDataProvider()
    {
        list($placeId, $jwtToken) = $this->getCredentials();
        return [
            [$jwtToken . 'foo', $placeId],
            [null, $placeId],
            [$jwtToken . 'foo', $placeId . '1'],
        ];
    }

    /**
     * Test the SDK against a live endpoint
     * @group airmee_api
     * @group productthresholdforplace
     * @group live
     * @dataProvider deliveryIntervalsAuthMismatchDataProvider
     * @param $token
     * @param $placeId
     */
    public function testProductThresholdForPlaceAuthMismatch($token, $placeId)
    {
        $this->expectException(\Airmee\PhpSdk\Core\Exceptions\AuthorizationException::class);
        $airmeeApi = $this->getAirmeeApi(['auth.jwt' => $token]);
        $schedules = $airmeeApi->productThresholdForPlace($placeId);

        $this->assertNotNull($schedules);
    }

//    /**
//     * Test the SDK against a live endpoint
//     * @group live
//     */
//    public function testDeliveryIntervalsInvalidPlaceId()
//    {
//        list($placeId, $jwtToken) = $this->getCredentials();
//        $this->expectException(\Airmee\PhpSdk\Core\Exceptions\ServerErrorException::class);
//        $this->expectExceptionCode(400);
//        $airmeeApi = $this->getAirmeeApi(['auth.jwt' => $jwtToken]);
//        $schedules = $airmeeApi->deliveryIntervalsForZipCode($placeId . '1', new Address(11428, 'Sweden'));
//
//        $this->assertNotNull($schedules);
//    }

    /**
     * Test the SDK against a live endpoint
     * @group airmee_api
     * @group productthresholdforplace
     * @group live
     */
    public function testProductThresholdForPlaceUnrecognisedPlaceId()
    {
        list($placeId, $jwtToken) = $this->getCredentials();
        $this->expectException(\Airmee\PhpSdk\Core\Exceptions\UnknownPlaceException::class);
        $this->expectExceptionCode(404);
        $airmeeApi = $this->getAirmeeApi(['auth.jwt' => $jwtToken]);
        $schedules = $airmeeApi->productThresholdForPlace(
            str_replace('a', 'b', $placeId)
        );

        $this->assertNotNull($schedules);
    }
}