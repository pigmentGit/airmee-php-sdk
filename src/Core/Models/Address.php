<?php
/**
 * @file Address.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 */

namespace Airmee\PhpSdk\Core\Models;


use Airmee\PhpSdk\Core\Exceptions\InvalidArgumentException;
use Jasny\ISO\Countries;

/**
 * A class representing a geographical location identifiable by zip code and country,
 * and optionally additionally specified using a street name and house number, and city.
 */
class Address
{
    /** @var string */
    private $zipCode;

    /** @var string */
    private $country;

    /** @var string */
    private $streetAndNumber;

    /** @var string */
    private $city;

    /**
     * Address constructor.
     * @param string $zipCode
     * @param string $country This must be either an ISO 3166-2 two-letter code, or an English-language
	 *                        full name of the country (eg 'Sweden')
     * @param string $streetAndNumber
     * @param string $city
     * @throws InvalidArgumentException
     */
    public function __construct($zipCode, $country, $streetAndNumber = null, $city = null)
    {
        if(empty($zipCode)){
            throw new InvalidArgumentException('$zipCode parameter is required');
        }
        $this->zipCode = $zipCode;

        if(empty($country)){
            throw new InvalidArgumentException('$country parameter is required');
        }
        if(Countries::getName($country) === null){
            throw new InvalidArgumentException('$country code does not correspond to a real country');
        }
        $this->country = Countries::getCode($country);

        # $streetAndNumber and $city must both be specified
        if(empty($streetAndNumber) xor empty($city)){
            throw new InvalidArgumentException('$streetAndNumber and $city must both be specified');
        }
        $this->streetAndNumber = $streetAndNumber;
        $this->city = $city;
    }

    /**
	 * Get the zip code
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
	 * Get the ISO 3166-2 two-letter country code.
	 * @see https://en.wikipedia.org/wiki/ISO_3166-2
     * @return string
     */
    public function getCountryCode()
    {
        return $this->country;
    }

    /**
	 * Get the street and house number
     * @return string
     */
    public function getStreetAndNumber()
    {
        return $this->streetAndNumber;
    }

    /**
	 * Get the city
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }
}