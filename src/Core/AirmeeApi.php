<?php

/**
 * @file AirmeeApi.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 */

namespace Airmee\PhpSdk\Core;

use Airmee\PhpSdk\Core\Exceptions\AddressParsingErrorException;
use Airmee\PhpSdk\Core\Exceptions\ApiConfigurationException;
use Airmee\PhpSdk\Core\Exceptions\AuthorizationException;
use Airmee\PhpSdk\Core\Exceptions\DeliveryCannotBeRequestedException;
use Airmee\PhpSdk\Core\Exceptions\DeliveryCannotBeScheduledException;
use Airmee\PhpSdk\Core\Exceptions\InvalidArgumentException;
use Airmee\PhpSdk\Core\Exceptions\ServerErrorException;
use Airmee\PhpSdk\Core\Exceptions\UnknownPlaceException;
use Airmee\PhpSdk\Core\Models\Address;
use Airmee\PhpSdk\Core\Models\Item;
use Airmee\PhpSdk\Core\Models\Order;
use Airmee\PhpSdk\Core\Models\Recipient;
use Airmee\PhpSdk\Core\Models\Schedule;
use Airmee\PhpSdk\Core\Models\TimeRange;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\HandlerStack;
use Money\Currency;
use Money\Money;
use Psr\Http\Message\StreamInterface;

/**
 * The class which handles interaction with the Airmee API
 */
class AirmeeApi
{
    /** @var bool default true */
    private $sandbox = true;

    /** @var HandlerStack */
    private $guzzleHandler;

    /** @var string */
    private $endpoint;

    /** @var string */
    private $jwtToken;

    /**
     * AirmeeApi constructor.
     * @param string|array $config either a path to an .ini configuration file, or an array of configuration options
     * @throws ApiConfigurationException
     */
    public function __construct($config)
    {
        if (is_array($config)) {
            $this->initialiseFromArray($config);
        } elseif (is_string($config)) {
            $this->initialiseFromIniFile($config);
        } else {
            throw new ApiConfigurationException('$config must be specified as an array or path to an .ini file');
        }
    }

    /**
     * Load configuration from an .ini config file
     * @param string $filepath
     * @throws ApiConfigurationException
     */
    private function initialiseFromIniFile($filepath)
    {
        if (!file_exists($filepath)) {
            throw new ApiConfigurationException("Configuration file '$filepath' does not exist");
        }

        if (!is_readable($filepath)) {
            throw new ApiConfigurationException("Configuration file '$filepath' is not readable");
        }

        $config = parse_ini_file($filepath, false, INI_SCANNER_TYPED);
        if (!is_array($config)) {
            throw new ApiConfigurationException('Badly-formatted config file');
        }

        $this->initialiseFromArray($config);
    }

    /**
     * Populate the object's configuration from an array
     * @param array $config
     */
    private function initialiseFromArray(array $config)
    {
        if (array_key_exists('sandbox', $config)) {
            $this->sandbox = $config['sandbox'];
        }

        if (array_key_exists('guzzle.handler', $config)) {
            $this->guzzleHandler = $config['guzzle.handler'];
        }

        if (array_key_exists('auth.jwt', $config)) {
            $this->jwtToken = $config['auth.jwt'];
        }

        if ($this->isSandbox()) {
            if (array_key_exists('endpoint.sandbox', $config)) {
                $this->endpoint = $config['endpoint.sandbox'];
            }
            else {
                $this->endpoint = 'https://staging-api.airmee.com/integration';
            }
        } else {
            if (array_key_exists('endpoint.production', $config)) {
                $this->endpoint = $config['endpoint.production'];
            } else {
                $this->endpoint = 'https://api.airmee.com/integration';
            }
        }
    }

    /**
     * Get whether the Api is in sandbox mode (ie it will call the staging API server)
     * @return bool
     */
    public function isSandbox()
    {
        return $this->sandbox;
    }

    /**
     * Get the Guzzle Client for making requests
     * @return Client
     */
    private function getGuzzleClient()
    {
        return new Client(['handler' => $this->guzzleHandler]);
    }


    /**
     * Get the endpoint to target
     * @param $endpoint
     * @return string
     */
    private function getTargetUri($endpoint)
    {
        return $this->endpoint . '/' . $endpoint;
    }

    /**
     * Get a JWT token to authenticate a request
     * @param string $place_id
     * @return string
     */
    private function getJwtToken($place_id)
    {
        return $this->jwtToken;
    }

    /**
     * Get the delivery Schedules that could be offered to the customer
     *
     * @depreciated deliveryIntervalsForAddress is preferred and makes additional address details optional for validation.
     *
     * @param string $placeId
     * @param Address $address
     * @return Schedule[]
     * @throws AuthorizationException
     * @throws UnknownPlaceException
     * @throws InvalidArgumentException
     * @throws ServerErrorException
     */
    public function deliveryIntervalsForZipCode($placeId, Address $address)
    {
        // Only the zip code and country is passed on to maintain compatibility.
        $zipOnlyAddress = new Address($address->getZipCode(), $address->getCountryCode());

        return $this->deliveryIntervalsForAddress($placeId, $zipOnlyAddress);
    }

    /**
     * Get the delivery Schedules that could be offered to the customer
     * @param string $placeId
     * @param Address $address
     * @return Schedule[]
     * @throws AuthorizationException
     * @throws UnknownPlaceException
     * @throws InvalidArgumentException
     * @throws ServerErrorException
     */
    public function deliveryIntervalsForAddress($placeId, Address $address)
    {
        if (empty($placeId)) {
            throw new InvalidArgumentException('$placeId must be specified');
        }

        try {
            $query = [
                'place_id' => $placeId,
                'country' => $address->getCountryCode(),
                'zip_code' => $address->getZipCode(),
                'date' => $this->getDate(),
                'offset' => '120'
            ];

            if($address->getStreetAndNumber() && $address->getCity()) {
                $streetAndNumberAndCity = [
                    'street_and_number' => $address->getStreetAndNumber(),
                    'city' => $address->getCity()
                ];
                $query = array_merge($query, $streetAndNumberAndCity);
            }

            $response = $this->getGuzzleClient()->get($this->getTargetUri('checkout_delivery_intervals_for_zip_code'), [
                'headers' => ['Authorization' => $this->getJwtToken($placeId)],
                'query' => $query
            ]);

            $responseObject = json_decode($response->getBody(), true);
            if (!is_array($responseObject) || !array_key_exists('list_of_schedules', $responseObject)) {
                throw new ServerErrorException('A server error occurred', 500);
            }

            return array_map([self::class, 'buildSchedule'], $responseObject['list_of_schedules']);

        } catch (BadResponseException $e) {
            $errorMessage = self::buildErrorMessage($e->getResponse()->GetBody());

            switch ($e->getCode()) {
                case 401:
                    throw new AuthorizationException($errorMessage, $e->getCode(), $e);
                case 404:
                    throw new UnknownPlaceException($errorMessage, $e->getCode(), $e);
                case 500:
                    throw new ServerErrorException($errorMessage, $e->getCode(), $e);
                default:
                    throw new ServerErrorException($errorMessage, 500, $e);
            }
        }
    }

    public function getDate()
    {
        date_default_timezone_set("Europe/Stockholm");
        $startTime = date("Y-m-d H:i");
        $offset = '120';
        $date = date('Y-m-d H:i',strtotime('+' . $offset . 'minutes',strtotime($startTime)));
        return $date;
    }

    /**
     * Get the largest/heaviest Item which can be delivered via Airmee for a given place.  The client
     * should not try to request a delivery of an Item where any dimension is greater than the
     * thresholds given here.
     * @param $placeId
     * @return array
     * @throws Exceptions\AuthorizationException
     * @throws Exceptions\UnknownPlaceException
     * @throws Exceptions\ServerErrorException
     * @throws Exceptions\InvalidArgumentException
     */
    public function productThresholdForPlace($placeId)
    {
        if (empty($placeId)) {
            throw new InvalidArgumentException('$placeId must be specified');
        }

        try {
            $response = $this->getGuzzleClient()->get($this->getTargetUri('product_threshold_for_place'), [
                'headers' => ['Authorization' => $this->getJwtToken($placeId)],
                'query' => [
                    'place_id' => $placeId,
                ]
            ]);

            $responseObject = json_decode($response->getBody(), true);
            if (!is_array($responseObject) || !array_key_exists('threshold_values', $responseObject)) {
                throw new ServerErrorException(self::buildErrorMessage($response->getBody()), 500);
            }

            return self::buildItem($responseObject['threshold_values']);

        } catch (BadResponseException $e) {
            $errorMessage = self::buildErrorMessage($e->getResponse()->GetBody());

            switch ($e->getCode()) {
                case 401:
                    throw new AuthorizationException($errorMessage, $e->getCode(), $e);
                case 404:
                    throw new UnknownPlaceException($errorMessage, $e->getCode(), $e);
                case 500:
                    throw new ServerErrorException($errorMessage, $e->getCode(), $e);
                default:
                    throw new ServerErrorException($errorMessage, 500, $e);
            }
        }
    }

    /**
     * Request that a delivery be fulfilled by Airmee
     * @param String    $placeId
     * @param String    $ecommId
     * @param Recipient $recipient
     * @param Address   $dropoffAddress
     * @param Item[]    $items
     * @param TimeRange $pickupInterval
     * @param TimeRange $dropoffInterval
     * @return Order
     * @throws AddressParsingErrorException
     * @throws AuthorizationException
     * @throws DeliveryCannotBeRequestedException
     * @throws InvalidArgumentException
     * @throws ServerErrorException
     */
    public function requestDelivery($placeId, $ecommId, Recipient $recipient, Address $dropoffAddress, array $items, TimeRange $pickupInterval, TimeRange $dropoffInterval)
    {
        if (empty($placeId)) {
            throw new InvalidArgumentException('$placeId must be specified');
        }

        if (empty($ecommId)) {
            throw new InvalidArgumentException('$ecommId must be specified');
        }

        if (empty($dropoffAddress->getCity()) || empty($dropoffAddress->getStreetAndNumber())) {
            throw new InvalidArgumentException('Delivery address must specify city and street');
        }

        if (count($items) == 0) {
            throw new InvalidArgumentException('$items must contain at least one member');
        }

        try {
            $response = $this->getGuzzleClient()->post($this->getTargetUri('request_delivery'), [
                'headers' => ['Authorization' => $this->getJwtToken($placeId)],
                'json' => [
                    'place_id' => $placeId,
                    'ecomm_id' => $ecommId,
                    'recipient' => [
                        'name' => $recipient->getName(),
                        'phone_number' => $recipient->getPhoneNumber()->getNationalNumber(),
                        'phone_number_country_code' => $recipient->getPhoneNumber()->getCountryCode(),
                        'email' => $recipient->getEmail(),
                    ],
                    'dropoff_address' => [
                        'street_and_number' => $dropoffAddress->getStreetAndNumber(),
                        'city' => $dropoffAddress->getCity(),
                        'zip_code' => $dropoffAddress->getZipCode(),
                        'country' => $dropoffAddress->getCountryCode(),
                    ],
                    'items' => array_map(
                        function (Item $item) {
                            return [
                                'length' => $item->getLength(),
                                'width' => $item->getWidth(),
                                'height' => $item->getHeight(),
                                'weight' => $item->getWeight(),
                                'name' => $item->getName(),
                                'quantity' => $item->getQuantity(),
                                'unit_price' => [
                                    'currency' => $item->getValue()->getCurrency(),
                                    'amount' => $item->getValue()->getAmount(),
                                ],
                            ];
                        },
                        $items
                    ),
                    'pickup_interval' => [
                        'start' => $pickupInterval->getStart()->format('U'),
                        'end' => $pickupInterval->getEnd()->format('U'),
                    ],
                    'dropoff_interval' => [
                        'start' => $dropoffInterval->getStart()->format('U'),
                        'end' => $dropoffInterval->getEnd()->format('U'),
                    ],
                ]
            ]);

            $responseObject = json_decode((string)$response->getBody(), true);
            if (!is_array($responseObject) || !array_key_exists('order', $responseObject)) {
                throw new ServerErrorException(self::buildErrorMessage($response->getBody()), 500);
            }

            return self::buildOrder($responseObject['order']);

        } catch (BadResponseException $e) {
            $errorMessage = self::buildErrorMessage($e->getResponse()->GetBody());

            switch ($e->getCode()) {
                case 401:
                    throw new AuthorizationException($errorMessage, $e->getCode(), $e);
                case 404:
                    throw new DeliveryCannotBeRequestedException($errorMessage, $e->getCode(), $e);
                case 412:
                    throw new AddressParsingErrorException($errorMessage, $e->getCode(), $e);
                case 500:
                    throw new ServerErrorException($errorMessage, $e->getCode(), $e);
                default:
                    throw new ServerErrorException($errorMessage, 500, $e);
            }
        }
    }

    /**
     * Extract the error message from the (hopefully JSON-formatted) response
     * @param StreamInterface $responseBody
     * @return string
     */
    private static function buildErrorMessage(StreamInterface $responseBody)
    {
        $response = json_decode($responseBody, true);
        if (!is_array($response) || !array_key_exists('message', $response)) {
            return "A server error occurred, and the message body could not be processed:\n\n$responseBody";
        }

        $errorMessage = $response['message'];

        if (array_key_exists('extraMessage', $response)) {
            $errorMessage .= "\n\n" . $response['extraMessage'];
        }

        return $errorMessage;
    }

    /**
     * Build a Schedule object from a server response
     * @param array $element
     * @return Schedule
     * @throws ServerErrorException
     */
    private static function buildSchedule(array $element)
    {
        if (!array_key_exists('pickup_interval', $element) || !is_array($element['pickup_interval'])) {
            throw new ServerErrorException('A server error occurred', 500);
        }
        $pickup = self::buildTimeRange($element['pickup_interval']);

        if (!array_key_exists('dropoff_interval', $element) || !is_array($element['dropoff_interval'])) {
            throw new ServerErrorException('A server error occurred', 500);
        }
        $dropoff = self::buildTimeRange($element['dropoff_interval']);

        return new Schedule($pickup, $dropoff);
    }

    /**
     * Build an Item object from a server response
     * @param array $element
     * @return Item
     * @throws ServerErrorException
     */
    private static function buildItem(array $element)
    {
        foreach(['length', 'width', 'height', 'weight'] as $param){
            if (!array_key_exists($param, $element) || !is_numeric($element[$param])) {
                throw new ServerErrorException('A server error occurred', 500);
            }
        }

        return new Item(
            $element['length'],
            $element['width'],
            $element['height'],
            $element['weight'],
            new Money(0, new Currency('SEK')),
            'Threshold item'
        );
    }

    /**
     * Build a Schedule object from a server response
     * @param array $element
     * @return TimeRange
     * @throws ServerErrorException
     */
    private static function buildTimeRange(array $element)
    {
        if (!array_key_exists('start', $element)) {
            throw new ServerErrorException('A server error occurred', 500);
        }
        $start = $element['start'];

        if (!array_key_exists('end', $element)) {
            throw new ServerErrorException('A server error occurred', 500);
        }
        $end = $element['end'];

        if (array_key_exists('formatted_as_schedule', $element)) {
            $formatted = $element['formatted_as_schedule'];
        } else {
            $formatted = null;
        }

        return new TimeRange($start, $end, $formatted);
    }

    /**
     * Build an Order object from a server response
     * @param array $element
     * @return Order
     * @throws ServerErrorException
     */
    private static function buildOrder(array $element)
    {
        if (!array_key_exists('order_id', $element)) {
            throw new ServerErrorException('A server error occurred', 500);
        }
        $id = $element['order_id'];

        if (!array_key_exists('tracking_url', $element)) {
            throw new ServerErrorException('A server error occurred', 500);
        }
        $trackingUrl = $element['tracking_url'];

        return new Order($id, $trackingUrl);
    }
}
