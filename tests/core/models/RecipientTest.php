<?php
use Airmee\PhpSdk\Core\Exceptions\InvalidArgumentException;
use Airmee\PhpSdk\Core\Models\Recipient;
use libphonenumber\PhoneNumberUtil;

/**
 * @file RecipientTest.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 *
 * @coversDefaultClass \Airmee\PhpSdk\Core\Models\Recipient
 */
class RecipientTest extends PHPUnit_Framework_TestCase
{
    private $phonenumber;

    /**
     * @before
     */
    public function initialise()
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $this->phonenumber = $phoneUtil->parse('767123123', 'SE');
    }

    public function createCantCreateEmptyDataProvider()
    {
        return [
            [null, null, null],
            ['John Smith', null, null],
            [null, true, null],
            [null, null, 'john@smith.com'],
            ['John Smith', true, null],
            ['John Smith', null, 'john@smith.com'],
            [null, true, 'john@smith.com'],
            ['', '', ''],
            ['John Smith', '', ''],
            ['', true, ''],
            ['', '', 'john@smith.com'],
            ['John Smith', true, ''],
            ['John Smith', '', 'john@smith.com'],
            ['', true, 'john@smith.com'],
        ];
    }

    /**
     * @group models
     * @group recipient
     * @dataProvider createCantCreateEmptyDataProvider
     * @param $name
     * @param $phoneNumber
     * @param $email
     */
    public function testCreateCantCreateEmpty($name, $phoneNumber, $email)
    {
        $this->expectException(InvalidArgumentException::class);
        $recipient = new Recipient($name, $phoneNumber === true ? $this->phonenumber : $phoneNumber, $email);
        $this->assertNull($recipient);
    }

    /**
     * @group models
     * @group recipient
     */
    public function testCreateCanCreate()
    {
        $recipient = new Recipient('John Smith', $this->phonenumber, 'john@smith.com');
        $this->assertNotNull($recipient);
    }

    public function createEmailValidDataProvider(){
        // These testcases are taken from https://blogs.msdn.microsoft.com/testing123/2009/02/06/email-address-test-cases/
        // with one exception (we don't try to do any validation of TLDs, as new ones are launching all the time and it
        // would be a fool's errand to try to keep on top of them all with a whitelist).
        return [
            ['email@domain.com', true],              // Valid email
            ['firstname.lastname@domain.com', true], // Email contains dot in the address field
            ['email@subdomain.domain.com', true],    // Email contains dot with subdomain
            ['firstname+lastname@domain.com', true], // Plus sign is considered valid character
            ['email@[123.123.123.123]', true],       // Square brackets around IP address is valid
            ['"email"@domain.com', true],            // Quotes around email is valid
            ['1234567890@domain.com', true],         // Digits in address are valid
            ['email@domain-one.com', true],          // Dash in domain name is valid
            ['_______@domain.com', true],            // Underscore in the address field is valid
            ['email@domain.name', true],             // .name is valid Top Level Domain name
            ['email@domain.co.jp', true],            // Dot in Top Level Domain name also valid (use co.jp as example here)
            ['firstname-lastname@domain.com', true], // Dash in address field is valid

            ['plainaddress', false],                 //  Missing @ sign and domain
            ['#@%^%#$@#$@#.com', false],             //   Garbage
            ['@domain.com', false],                  //   Missing username
            ['Joe Smith <email@domain.com>', false], //   Encoded html within email is invalid
            ['email.domain.com', false],             //   Missing @
            ['email@domain@domain.com', false],      //   Two @ sign
            ['.email@domain.com', false],            //   Leading dot in address is not allowed
            ['email.@domain.com', false],            //   Trailing dot in address is not allowed
            ['email..email@domain.com', false],      //   Multiple dots
            ['あいうえお@domain.com', false],         //   Unicode char as address
            ['email@domain.com (Joe Smith)', false], //   Text followed email is not allowed
            ['email@domain', false],                 //   Missing top level domain (.com/.net/.org/etc)
            ['email@-domain.com', false],            //   Leading dash in front of domain is invalid
            ['email@111.222.333.44444', false],      //   Invalid IP format
            ['email@domain..com', false],            //   Multiple dot in the domain portion is invalid
        ];
    }

    /**
     * @group models
     * @group recipient
     * @dataProvider createEmailValidDataProvider
     * @param $email
     * @param $valid
     */
    public function testCreateEmailValid($email, $valid)
    {
        if ($valid) {
            $recipient = new Recipient('John Smith', $this->phonenumber, $email);
            $this->assertNotNull($recipient);
        } else {
            $this->expectException(InvalidArgumentException::class);
            $recipient = new Recipient('John Smith', $this->phonenumber, $email);
            $this->assertNull($recipient);
        }
    }
}