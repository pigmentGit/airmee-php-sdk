<?php
/**
 * @file Order.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 */

namespace Airmee\PhpSdk\Core\Models;

/**
 * A class representing a successfully-registered order that will be fulfilled
 * by Airmee.
 */
class Order
{
    /** @var String */
    private $id;

    /** @var String */
    private $trackingUrl;

    /**
     * Order constructor.
     * @param string $id
     * @param string $trackingUrl
     */
    public function __construct($id, $trackingUrl)
    {
        $this->id = $id;
        $this->trackingUrl = $trackingUrl;
    }

    /**
     * Get the unique order id in the Airmee system
     * @return String
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get a URL at which the user can find tracking information for the delivery
     * @return String
     */
    public function getTrackingUrl()
    {
        return $this->trackingUrl;
    }
}