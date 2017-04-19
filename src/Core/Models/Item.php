<?php
/**
 * @file Item.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 */

namespace Airmee\PhpSdk\Core\Models;


use Airmee\PhpSdk\Core\Exceptions\InvalidArgumentException;
use Money\Money;

/**
 * A class representing one physical package (or several identical packages) that will be
 * delivered as part of an Order
 */
class Item
{
    /** @var string */
    private $length;

    /** @var string */
    private $width;

    /** @var string */
    private $height;

    /** @var string */
    private $weight;

    /** @var Money */
    private $value;

    /** @var string */
    private $name;

    /** @var int */
    private $quantity;

    /**
     * Item constructor.
     * @param int $length The length of the item in centimetres
     * @param int $width  The width of the item in centimetres
     * @param int $height The height of the item in centimetres
     * @param int $weight The weight of the item in grams
     * @param Money $value  The unit price of the item
     * @param string $name   The name of the item
     * @param int $quantity The number of items of this type in the delivery
     * @throws InvalidArgumentException
     */
    public function __construct($length, $width, $height, $weight, Money $value, $name, $quantity = 1)
    {
        if (empty($length)) {
            throw new InvalidArgumentException('$length parameter is required');
        }
        $this->length = $length;

        if (empty($width)) {
            throw new InvalidArgumentException('$width parameter is required');
        }
        $this->width = $width;

        if (empty($height)) {
            throw new InvalidArgumentException('$height parameter is required');
        }
        $this->height = $height;

        if (empty($weight)) {
            throw new InvalidArgumentException('$weight parameter is required');
        }
        $this->weight = $weight;

        $this->value = $value;

        if (empty($name)) {
            throw new InvalidArgumentException('$name parameter is required');
        }
        $this->name = $name;

        $this->quantity = (int)$quantity != 0
            ? (int)$quantity
            : 1;
    }

    /**
     * Get the length
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Get the width
     * @return string
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Get the height
     * @return string
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Get the weight
     * @return string
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Get the value
     * @return Money
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the quantity
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}