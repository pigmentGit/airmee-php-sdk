<?php
/**
 * @file Recipient.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 */

namespace Airmee\PhpSdk\Core\Models;

use Airmee\PhpSdk\Core\Exceptions\InvalidArgumentException;
use libphonenumber\PhoneNumber;

/**
 * A class representing a person who will receive the order
 */
class Recipient
{
    /** @var string */
    private $name;

    /** @var PhoneNumber */
    private $phoneNumber;

    /** @var string */
    private $email;

    /**
     * Recipient constructor.
     * @param string      $name
     * @param PhoneNumber $phoneNumber
     * @param string      $email
     * @throws InvalidArgumentException
     */
    public function __construct($name, $phoneNumber, $email)
    {
        if (empty($name)) {
            throw new InvalidArgumentException('$name parameter is required');
        }
        $this->name = $name;
        
        if (!$phoneNumber instanceof PhoneNumber) {
            throw new InvalidArgumentException('$phonenumber parameter is required and must be a PhoneNumber');
        }
        $this->phoneNumber = $phoneNumber;
        
        if (empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('$email parameter is required and must be a valid email address');
        }
        $this->email = $email;
        
    }

    /**
     * Get the recipient's name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the phone number
     * @return PhoneNumber
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Get the email address
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
}