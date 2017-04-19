<?php
use Airmee\PhpSdk\Core\Exceptions\AuthorizationException;
use Airmee\PhpSdk\Core\Exceptions\DeliveryCannotBeScheduledException;
use Airmee\PhpSdk\Core\Exceptions\InvalidArgumentException;
use Airmee\PhpSdk\Core\Exceptions\ServerErrorException;
use Airmee\PhpSdk\Core\Exceptions\UnknownPlaceException;
use Airmee\PhpSdk\Core\Models\Address;
use Airmee\PhpSdk\Core\Models\Schedule;
use Airmee\PhpSdk\Core\Models\TimeRange;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * @file DeliveryIntervalsForZipCodeTest.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 *
 * @coversDefaultClass \Airmee\PhpSdk\Core\Models\AirmeeApi
 */
class DeliveryIntervalsForZipCodeTest extends AirmeeApiTest
{
    /**
     * Possible invalid place_id values
     * @return array
     */
    public function noPlaceIdDataProvider()
    {
        $address = new Address(123456, 'Sweden');
        return [
            [null, $address],
            ['', $address],
        ];
    }

    /**
     * @group airmee_api
     * @group deliveryintervalsforzipcode
     * @dataProvider noPlaceIdDataProvider
     * @param $placeId
     * @param $address
     */
    public function testNoPlaceId($placeId, $address)
    {
        $this->expectException(InvalidArgumentException::class);
        $airmeeApi = $this->getAirmeeApiMock(new Response(418, [], "I'm a teapot"));
        $response = $airmeeApi->deliveryIntervalsForZipCode($placeId, $address);
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
     * @group deliveryintervalsforzipcode
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
        $response = $airmeeApi->deliveryIntervalsForZipCode('123456', new Address(123456, 'Sweden'));

        $this->assertNull($response);
    }

    /**
     * Test that the SDK responds correctly when the server returns a 500 error
     * @group airmee_api
     * @group deliveryintervalsforzipcode
     */
    public function testServerError()
    {
        $this->expectException(ServerErrorException::class);
        $this->expectExceptionMessage('Our server encountered an error, please retry or contact support.');

        $airmeeApi = $this->getAirmeeApiMock(new Response(500, [], '{"message": "Our server encountered an error, please retry or contact support."}'));
        $response = $airmeeApi->deliveryIntervalsForZipCode('123456', new Address(123456, 'Sweden'));

        $this->assertNull($response);
    }

    /**
     * Test that the SDK responds correctly when the server returns an InvalidPlace error
     * @group airmee_api
     * @group deliveryintervalsforzipcode
     */
    public function testDeliveryInvalidPlaceError()
    {
        $this->expectException(UnknownPlaceException::class);
        $this->expectExceptionMessage('Unrecognised place_id');

        $airmeeApi = $this->getAirmeeApiMock(new Response(404, [], '{"message": "Unrecognised place_id"}'));
        $response = $airmeeApi->deliveryIntervalsForZipCode('123456', new Address(123456, 'Sweden'));

        $this->assertNull($response);
    }

    /**
     * Test that the SDK responds correctly to a mocked successful response
     * @group airmee_api
     * @group deliveryintervalsforzipcode
     */
    public function testSuccessfulResponseNoSchedules()
    {
        $responseBody = <<<ENDL
{
	"list_of_schedules": []
}
ENDL;
        $airmeeApi = $this->getAirmeeApiMock(new Response(200, [], $responseBody));
        $results = $airmeeApi->deliveryIntervalsForZipCode('123456', new Address(123456, 'Sweden'));

        $this->assertTrue($results === []);
    }

    /**
     * Test that the SDK responds correctly to a mocked successful response
     * @group airmee_api
     * @group deliveryintervalsforzipcode
     */
    public function testSuccessfulResponseOneSchedule()
    {
        $responseBody = <<<ENDL
{
	"list_of_schedules": [{
		"pickup_interval": {
			"start": "1490680800",
			"end": "1490724000",
			"formatted_as_schedule": "08:00 - 20:00"
		},
		"dropoff_interval": {
			"start": "1490702400",
			"end": "1490709600",
			"formatted_as_schedule": "14:00 - 16:00"
		}
	}]
}
ENDL;
        $airmeeApi = $this->getAirmeeApiMock(new Response(200, [], $responseBody));
        $results = $airmeeApi->deliveryIntervalsForZipCode('123456', new Address(123456, 'Sweden'));

        $this->assertTrue(is_array($results));
        $this->assertTrue(count($results) === 1);

        $this->assertInstanceOf(Schedule::class, $results[0]);
        $this->assertInstanceOf(TimeRange::class, $results[0]->getPickup());
        $this->assertEquals(1490680800, $results[0]->getPickup()->getStart()->format('U'));
        $this->assertEquals(1490724000, $results[0]->getPickup()->getEnd()->format('U'));
        $this->assertInstanceOf(TimeRange::class, $results[0]->getDropoff());
        $this->assertEquals(1490702400, $results[0]->getDropoff()->getStart()->format('U'));
        $this->assertEquals(1490709600, $results[0]->getDropoff()->getEnd()->format('U'));
    }

    /**
     * Test that the SDK responds correctly to a mocked successful response
     * @group airmee_api
     * @group deliveryintervalsforzipcode
     */
    public function testSuccessfulResponseMultipleSchedules()
    {
        $responseBody = <<<ENDL
{
	"list_of_schedules": [{
		"pickup_interval": {
			"start": "1490680800",
			"end": "1490724000",
			"formatted_as_schedule": "08:00 - 20:00"
		},
		"dropoff_interval": {
			"start": "1490702400",
			"end": "1490709600",
			"formatted_as_schedule": "14:00 - 16:00"
		}
	}, {
		"pickup_interval": {
			"start": "1490767200",
			"end": "1490810400",
			"formatted_as_schedule": "(tomorrow) 08:00 - 20:00"
		},
		"dropoff_interval": {
			"start": "1490767200",
			"end": "1490781600",
			"formatted_as_schedule": "(tomorrow) 08:00 - 12:00"
		}
	}, {
		"pickup_interval": {
			"start": "1490767200",
			"end": "1490810400",
			"formatted_as_schedule": "(tomorrow) 08:00 - 20:00"
		},
		"dropoff_interval": {
			"start": "1490788800",
			"end": "1490796000",
			"formatted_as_schedule": "(tomorrow) 14:00 - 16:00"
		}
	}]
}
ENDL;
        $airmeeApi = $this->getAirmeeApiMock(new Response(200, [], $responseBody));
        $results = $airmeeApi->deliveryIntervalsForZipCode('123456', new Address(123456, 'Sweden'));

        $this->assertTrue(is_array($results));
        $this->assertTrue(count($results) === 3);
        $this->assertInstanceOf(Schedule::class, $results[0]);
        $this->assertInstanceOf(TimeRange::class, $results[0]->getPickup());
        $this->assertEquals(1490680800, $results[0]->getPickup()->getStart()->format('U'));
        $this->assertEquals(1490724000, $results[0]->getPickup()->getEnd()->format('U'));
        $this->assertEquals('08:00 - 20:00', $results[0]->getPickup()->getFormatted());
        $this->assertInstanceOf(TimeRange::class, $results[0]->getDropoff());
        $this->assertEquals(1490702400, $results[0]->getDropoff()->getStart()->format('U'));
        $this->assertEquals(1490709600, $results[0]->getDropoff()->getEnd()->format('U'));
        $this->assertEquals('14:00 - 16:00', $results[0]->getDropoff()->getFormatted());

        $this->assertInstanceOf(Schedule::class, $results[1]);
        $this->assertInstanceOf(TimeRange::class, $results[1]->getPickup());
        $this->assertEquals(1490767200, $results[1]->getPickup()->getStart()->format('U'));
        $this->assertEquals(1490810400, $results[1]->getPickup()->getEnd()->format('U'));
        $this->assertEquals('(tomorrow) 08:00 - 20:00', $results[1]->getPickup()->getFormatted());
        $this->assertInstanceOf(TimeRange::class, $results[1]->getDropoff());
        $this->assertEquals(1490767200, $results[1]->getDropoff()->getStart()->format('U'));
        $this->assertEquals(1490781600, $results[1]->getDropoff()->getEnd()->format('U'));
        $this->assertEquals('(tomorrow) 08:00 - 12:00', $results[1]->getDropoff()->getFormatted());

        $this->assertInstanceOf(Schedule::class, $results[2]);
        $this->assertInstanceOf(TimeRange::class, $results[2]->getPickup());
        $this->assertEquals(1490767200, $results[2]->getPickup()->getStart()->format('U'));
        $this->assertEquals(1490810400, $results[2]->getPickup()->getEnd()->format('U'));
        $this->assertEquals('(tomorrow) 08:00 - 20:00', $results[2]->getPickup()->getFormatted());
        $this->assertInstanceOf(TimeRange::class, $results[2]->getDropoff());
        $this->assertEquals(1490788800, $results[2]->getDropoff()->getStart()->format('U'));
        $this->assertEquals(1490796000, $results[2]->getDropoff()->getEnd()->format('U'));
        $this->assertEquals('(tomorrow) 14:00 - 16:00', $results[2]->getDropoff()->getFormatted());
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
                'https://staging-api.airmee.com/integration/delivery_intervals_for_zip_code'
            ],
            [
                ['sandbox' => false],
                'https://api.airmee.com/integration/delivery_intervals_for_zip_code'
            ],
            [
                ['sandbox' => true, 'endpoint.sandbox' => 'http://foo.bar.com', 'endpoint.production' => 'https://baz.quok.com'],
                'http://foo.bar.com/delivery_intervals_for_zip_code'
            ],
            [
                ['sandbox' => false, 'endpoint.sandbox' => 'http://foo.bar.com', 'endpoint.production' => 'https://baz.quok.com'],
                'https://baz.quok.com/delivery_intervals_for_zip_code'
            ],
        ];
    }

    /**
     * Test that the SDK calls the right endpoint
     * @group airmee_api
     * @group deliveryintervalsforzipcode
     * @dataProvider successfulResponseUrlEndpointsDataProvider
     * @param array $config
     * @param $expectedEndpoint
     */
    public function testSuccessfulResponseUrlEndpoints(array $config, $expectedEndpoint)
    {
        $responseBody = <<<ENDL
{
	"list_of_schedules": []
}
ENDL;
        $request = [];
        $airmeeApi = $this->getAirmeeApiMock(new Response(200, [], $responseBody), $config, $request);
        $airmeeApi->deliveryIntervalsForZipCode('123456', new Address(123456, 'Sweden'));

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
     * @group deliveryintervalsforzipcode
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
            $airmeeApi->deliveryIntervalsForZipCode('123456', new Address(123456, 'Sweden'));
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
     * @group deliveryintervalsforzipcode
     * @group live
     */
    public function testDeliveryIntervalsSuccessfulResponse()
    {
        list($placeId, $jwtToken) = $this->getCredentials();
        $airmeeApi = $this->getAirmeeApi(['auth.jwt' => $jwtToken]);
        $schedules = $airmeeApi->deliveryIntervalsForZipCode($placeId, new Address(11428, 'Sweden'));

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
     * @group deliveryintervalsforzipcode
     * @group live
     * @dataProvider deliveryIntervalsAuthMismatchDataProvider
     * @param $token
     * @param $placeId
     */
    public function testDeliveryIntervalsAuthMismatch($token, $placeId)
    {
        $this->expectException(\Airmee\PhpSdk\Core\Exceptions\AuthorizationException::class);
        $airmeeApi = $this->getAirmeeApi(['auth.jwt' => $token]);
        $schedules = $airmeeApi->deliveryIntervalsForZipCode($placeId, new Address(11428, 'Sweden'));

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
     * @group deliveryintervalsforzipcode
     * @group live
     */
    public function testDeliveryIntervalsUnrecognisedPlaceId()
    {
        list($placeId, $jwtToken) = $this->getCredentials();
        $this->expectException(\Airmee\PhpSdk\Core\Exceptions\UnknownPlaceException::class);
        $this->expectExceptionCode(404);
        $airmeeApi = $this->getAirmeeApi(['auth.jwt' => $jwtToken]);
        $schedules = $airmeeApi->deliveryIntervalsForZipCode(
            str_replace('a', 'b', $placeId),
                new Address(11428, 'Sweden')
        );

        $this->assertNotNull($schedules);
    }
}