<?php
use Airmee\PhpSdk\Core\Exceptions\AddressParsingErrorException;
use Airmee\PhpSdk\Core\Exceptions\AuthorizationException;
use Airmee\PhpSdk\Core\Exceptions\DeliveryCannotBeRequestedException;
use Airmee\PhpSdk\Core\Exceptions\InvalidArgumentException;
use Airmee\PhpSdk\Core\Exceptions\ServerErrorException;
use Airmee\PhpSdk\Core\Models\Address;
use Airmee\PhpSdk\Core\Models\Item;
use Airmee\PhpSdk\Core\Models\Order;
use Airmee\PhpSdk\Core\Models\Recipient;
use Airmee\PhpSdk\Core\Models\TimeRange;
use GuzzleHttp\Psr7\Response;
use libphonenumber\PhoneNumberUtil;
use Money\Currency;
use Money\Money;
use Psr\Http\Message\RequestInterface;

/**
 * @file RequestDeliveryTest.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 *
 * @coversDefaultClass \Airmee\PhpSdk\Core\Models\AirmeeApi
 */
class RequestDeliveryTest extends AirmeeApiTest
{
    private $placeId;
    private $ecommId;
    private $phonenumber;
    private $recipient;
    private $address;
    private $items;
    private $pickupInterval;
    private $dropoffInterval;

    /**
     * @before
     */
    public function initialiseDefaults()
    {
        $this->placeId = '123456';
        $this->ecommId = '456789';

        $phoneUtil = PhoneNumberUtil::getInstance();
        $this->phonenumber = $phoneUtil->parse('767123123', 'SE');

        $this->recipient = new Recipient('John Smith', $this->phonenumber, 'john@smith.com');

        $this->address = new Address(11157, 'Sweden', 'Sergelgatan 22', 'Stockholm');

        $this->items = [
            new Item(1, 2, 3, 4, new Money(123, new Currency('SEK')), 'Epic Box'),
            new Item(11, 12, 13, 14, new Money(456, new Currency('SEK')), 'Multiple Epic Boxes', 4),
            new Item(21, 22, 23, 24, new Money(789, new Currency('SEK')), 'A non-epic box'),
        ];

        $this->pickupInterval = new TimeRange(1490680800, 1490724000);
        $this->dropoffInterval = new TimeRange(1490702400, 1490709600);
    }

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
     * @group requestdelivery
     * @dataProvider noPlaceIdDataProvider
     * @param $placeId
     */
    public function testNoPlaceId($placeId)
    {
        $this->expectException(InvalidArgumentException::class);
        $airmeeApi = $this->getAirmeeApiMock(new Response(418, [], "I'm a teapot"));
        $response = $airmeeApi->requestDelivery(
            $placeId,
            $this->ecommId,
            $this->recipient,
            $this->address,
            $this->items,
            $this->pickupInterval,
            $this->dropoffInterval
        );
        $this->assertNull($response);
    }

    /**
     * Possible invalid ecomm_id values
     * @return array
     */
    public function noEcommIdDataProvider()
    {
        return [
            [null],
            [''],
        ];
    }

    /**
     * @group airmee_api
     * @group requestdelivery
     * @dataProvider noEcommIdDataProvider
     * @param $ecommId
     */
    public function testNoEcommId($ecommId)
    {
        $this->expectException(InvalidArgumentException::class);
        $airmeeApi = $this->getAirmeeApiMock(new Response(418, [], "I'm a teapot"));
        $response = $airmeeApi->requestDelivery(
            $this->placeId,
            $ecommId,
            $this->recipient,
            $this->address,
            $this->items,
            $this->pickupInterval,
            $this->dropoffInterval
        );
        $this->assertNull($response);
    }

    /**
     * Possible invalid ecomm_id values
     * @return array
     */
    public function incompleteAddressDataProvider()
    {
        return [
            [new Address(123456, 'Sweden')],
        ];
    }

    /**
     * @group airmee_api
     * @group requestdelivery
     * @dataProvider incompleteAddressDataProvider
     * @param $address
     */
    public function testIncompleteAddress($address)
    {
        $this->expectException(InvalidArgumentException::class);
        $airmeeApi = $this->getAirmeeApiMock(new Response(418, [], "I'm a teapot"));
        $response = $airmeeApi->requestDelivery(
            $this->placeId,
            $this->ecommId,
            $this->recipient,
            $address,
            $this->items,
            $this->pickupInterval,
            $this->dropoffInterval
        );
        $this->assertNull($response);
    }

    /**
     * Test trying to submit a request with no items
     * @group airmee_api
     * @group requestdelivery
     */
    public function testNoItems()
    {
        $this->expectException(InvalidArgumentException::class);
        $airmeeApi = $this->getAirmeeApiMock(new Response(418, [], "I'm a teapot"));
        $response = $airmeeApi->requestDelivery(
            $this->placeId,
            $this->ecommId,
            $this->recipient,
            $this->address,
            [],
            $this->pickupInterval,
            $this->dropoffInterval
        );
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
     * @group requestdelivery
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
        $response = $airmeeApi->requestDelivery(
            $this->placeId,
            $this->ecommId,
            $this->recipient,
            $this->address,
            $this->items,
            $this->pickupInterval,
            $this->dropoffInterval
        );

        $this->assertNull($response);
    }

    /**
     * Test that the SDK responds correctly when the server returns a 500 error
     * @group airmee_api
     * @group requestdelivery
     */
    public function testServerError()
    {
        $this->expectException(ServerErrorException::class);
        $this->expectExceptionMessage('Our server encountered an error, please retry or contact support.');

        $airmeeApi = $this->getAirmeeApiMock(new Response(500, [], '{"message": "Our server encountered an error, please retry or contact support."}'));
        $response = $airmeeApi->requestDelivery(
            $this->placeId,
            $this->ecommId,
            $this->recipient,
            $this->address,
            $this->items,
            $this->pickupInterval,
            $this->dropoffInterval
        );

        $this->assertNull($response);
    }

    /**
     * Test that the SDK responds correctly when the server returns a DeliveryCannotBeScheduled error
     * @group airmee_api
     * @group requestdelivery
     */
    public function testDeliveryCannotBeRequestedError()
    {
        $this->expectException(DeliveryCannotBeRequestedException::class);
        $this->expectExceptionMessage('Delivery cannot be requested due to invalid pickup place id');

        $airmeeApi = $this->getAirmeeApiMock(new Response(404, [], '{"message": "Delivery cannot be requested due to invalid pickup place id"}'));
        $response = $airmeeApi->requestDelivery(
            $this->placeId,
            $this->ecommId,
            $this->recipient,
            $this->address,
            $this->items,
            $this->pickupInterval,
            $this->dropoffInterval
        );

        $this->assertNull($response);
    }

    /**
     * Test that the SDK responds correctly when the server returns a DeliveryCannotBeScheduled error
     * @group airmee_api
     * @group requestdelivery
     */
    public function testDeliveryCannotBeScheduledError()
    {
        $this->expectException(AddressParsingErrorException::class);
        $this->expectExceptionMessage('Address could not be parsed');

        $airmeeApi = $this->getAirmeeApiMock(new Response(412, [], '{"message": "Address could not be parsed"}'));
        $response = $airmeeApi->requestDelivery(
            $this->placeId,
            $this->ecommId,
            $this->recipient,
            $this->address,
            $this->items,
            $this->pickupInterval,
            $this->dropoffInterval
        );

        $this->assertNull($response);
    }

    /**
     * Test that the SDK responds correctly to a mocked successful response
     * @group airmee_api
     * @group requestdelivery
     */
    public function testSuccessfulResponses()
    {
        $responseBody = <<<ENDL
{
   "order": {
       "order_id": "69a84ba0-089d-11e7-b1a6-1f29a6237061",
       "tracking_url": "tracking.airmee.com/?tracking_url=Wy5AEP5z"
   }
}
ENDL;
        $airmeeApi = $this->getAirmeeApiMock(new Response(200, [], $responseBody));
        $order = $airmeeApi->requestDelivery(
            $this->placeId,
            $this->ecommId,
            $this->recipient,
            $this->address,
            $this->items,
            $this->pickupInterval,
            $this->dropoffInterval
        );

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('69a84ba0-089d-11e7-b1a6-1f29a6237061', $order->getId());
        $this->assertEquals('tracking.airmee.com/?tracking_url=Wy5AEP5z', $order->getTrackingUrl());
    }

    /**
     * Test that the SDK responds correctly to a mocked successful response
     * @group airmee_api
     * @group requestdelivery
     */
    public function testSuccessfulResponseWithMethod()
    {
        $responseBody = <<<ENDL
{
   "order": {
       "order_id": "69a84ba0-089d-11e7-b1a6-1f29a6237061",
       "tracking_url": "tracking.airmee.com/?tracking_url=Wy5AEP5z"
   }
}
ENDL;
        $request = [];
        $airmeeApi = $this->getAirmeeApiMock(new Response(200, [], $responseBody), [], $request);
        $order = $airmeeApi->requestDelivery(
            $this->placeId,
            $this->ecommId,
            $this->recipient,
            $this->address,
            $this->items,
            $this->pickupInterval,
            $this->dropoffInterval
        );

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('69a84ba0-089d-11e7-b1a6-1f29a6237061', $order->getId());
        $this->assertEquals('tracking.airmee.com/?tracking_url=Wy5AEP5z', $order->getTrackingUrl());

        $this->assertTrue(count($request) == 1);
        /** @var RequestInterface $request */
        $request = $request[0]['request'];
        $this->assertInstanceOf(RequestInterface::class, $request);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertArrayHasKey('Content-Type', $request->getHeaders());
        $this->assertEquals('application/json', $request->getHeader('Content-Type')[0]);
    }

    public function successfulResponseUrlEndpointsDataProvider(){
        return [
            [
                ['sandbox' => true],
                'https://staging-api.airmee.com/integration/request_delivery'
            ],
            [
                ['sandbox' => false],
                'https://api.airmee.com/integration/request_delivery'
            ],
            [
                ['sandbox' => true, 'endpoint.sandbox' => 'http://foo.bar.com', 'endpoint.production' => 'https://baz.quok.com'],
                'http://foo.bar.com/request_delivery'
            ],
            [
                ['sandbox' => false, 'endpoint.sandbox' => 'http://foo.bar.com', 'endpoint.production' => 'https://baz.quok.com'],
                'https://baz.quok.com/request_delivery'
            ],
        ];
    }

    /**
     * Test that the SDK calls the right endpoint
     * @group airmee_api
     * @group requestdelivery
     * @dataProvider successfulResponseUrlEndpointsDataProvider
     * @param array $config
     * @param $expectedEndpoint
     */
    public function testSuccessfulResponseUrlEndpoints(array $config, $expectedEndpoint)
    {
        $responseBody = <<<ENDL
{
   "order": {
       "order_id": "69a84ba0-089d-11e7-b1a6-1f29a6237061",
       "tracking_url": "tracking.airmee.com/?tracking_url=Wy5AEP5z"
   }
}
ENDL;
        $request = [];
        $airmeeApi = $this->getAirmeeApiMock(new Response(200, [], $responseBody), $config, $request);
        $order = $airmeeApi->requestDelivery(
            $this->placeId,
            $this->ecommId,
            $this->recipient,
            $this->address,
            $this->items,
            $this->pickupInterval,
            $this->dropoffInterval
        );

        $this->assertTrue(count($request) == 1);
        /** @var RequestInterface $request */
        $request = $request[0]['request'];
        $this->assertInstanceOf(RequestInterface::class, $request);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertArrayHasKey('Content-Type', $request->getHeaders());
        $this->assertEquals('application/json', $request->getHeader('Content-Type')[0]);

        $this->assertEquals($expectedEndpoint, (string)$request->getUri());
    }

    /**
     * Test that the SDK responds correctly when there's an authorization error
     * @group airmee_api
     * @group requestdelivery
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
            $airmeeApi->requestDelivery(
                $this->placeId,
                $this->ecommId,
                $this->recipient,
                $this->address,
                $this->items,
                $this->pickupInterval,
                $this->dropoffInterval
            );
            $this->fail('Should raise an exception');
        } catch(AuthorizationException $e){

        }

        $this->assertTrue(count($request) == 1);
        /** @var RequestInterface $request */
        $request = $request[0]['request'];
        $this->assertInstanceOf(RequestInterface::class, $request);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertArrayHasKey('Authorization', $request->getHeaders());
        $this->assertEquals('abcdef', $request->getHeader('Authorization')[0]);
    }

    /********************************************************************************************************/
    // LIVE TESTS
    /********************************************************************************************************/

    /**
     * Test the SDK against a live endpoint, expecting a successful response
     * @group airmee_api
     * @group requestdelivery
     * @group live
     */
    public function testRequestDeliverySuccessfulResponse()
    {
        list($placeId, $jwtToken) = $this->getCredentials();
        $airmeeApi = $this->getAirmeeApi(['auth.jwt' => $jwtToken]);
        $order = $airmeeApi->requestDelivery(
            $placeId,
            $this->ecommId,
            $this->recipient,
            $this->address,
            $this->items,
            $this->pickupInterval,
            $this->dropoffInterval
        );

        $this->assertNotNull($order);
    }

    public function requestDeliveryAuthMismatchDataProvider()
    {
        list($placeId, $jwtToken) = $this->getCredentials();
        return [
            [$jwtToken . 'foo', $placeId],
            [null, $placeId],
            [$jwtToken . 'foo', $placeId . '1'],
        ];
    }

    /**
     * Test the SDK against a live endpoint, with various mismatching auth codes
     * @group airmee_api
     * @group requestdelivery
     * @group live
     * @dataProvider requestDeliveryAuthMismatchDataProvider
     * @param $token
     * @param $placeId
     */
    public function testRequestDeliveryAuthMismatch($token, $placeId)
    {
        $this->expectException(\Airmee\PhpSdk\Core\Exceptions\AuthorizationException::class);
        $airmeeApi = $this->getAirmeeApi(['auth.jwt' => $token]);
        $order = $airmeeApi->requestDelivery(
            $placeId,
            $this->ecommId,
            $this->recipient,
            $this->address,
            $this->items,
            $this->pickupInterval,
            $this->dropoffInterval
        );

        $this->assertNull($order);
    }

//    /**
//     * Test the SDK against a live endpoint with an invalid place_id
//     * @group live
//     */
//    public function testRequestDeliveryInvalidPlaceId()
//    {
//        list($placeId, $jwtToken) = $this->getCredentials();
//        $this->expectException(\Airmee\PhpSdk\Core\Exceptions\ServerErrorException::class);
//        $this->expectExceptionCode(400);
//        $airmeeApi = $this->getAirmeeApi(['auth.jwt' => $jwtToken]);
//        $order = $airmeeApi->requestDelivery(
//            $placeId . 'ab',
//            $this->ecommId,
//            $this->recipient,
//            $this->address,
//            $this->items,
//            $this->pickupInterval,
//            $this->dropoffInterval
//        );
//
//        $this->assertNull($order);
//    }

//    /**
//     * Test the SDK against a live endpoint with a nonexistent place_id
//     * @group live
//     */
//    public function testRequestDeliveryNonexistentPlaceId()
//    {
//        list($placeId, $jwtToken) = $this->getCredentials();
//        $this->expectException(\Airmee\PhpSdk\Core\Exceptions\UnknownPlaceException::class);
//        $this->expectExceptionCode(404);
//        $airmeeApi = $this->getAirmeeApi(['auth.jwt' => $jwtToken]);
//        $order = $airmeeApi->requestDelivery(
//            str_replace('a', 'b', $placeId),
//            $this->ecommId,
//            $this->recipient,
//            $this->address,
//            $this->items,
//            $this->pickupInterval,
//            $this->dropoffInterval
//        );
//
//        $this->assertNull($order);
//    }
}