Item
====
..  php:namespace:: Airmee\PhpSdk


..  php:class:: Item

    The Item class

    ..  php:method:: __construct($length, $width, $height, $weight, Money $value, $name[, $quantity = 1])

        Construct a new Item object.

        :param int $length: The length of the parcel in centimetres
        :param int $width: The width of the parcel in centimetres
        :param int $height: The height of the parcel in centimetres
        :param int $weight: The height of the parcel in grams
        :param Money $value: The unit price of the item.  This must be a :php:class:`Money\\Money` object.
        :param string $name: A description of the item
        :param int $quantity: The number of items of this type

        :returns: An Address


    ..  php:method:: getLength()

        Get the length of the item.

        :returns: :php:class:`int`


    ..  php:method:: getWidth()

        Get the width of the item.

        :returns: :php:class:`int`


    ..  php:method:: getHeight()

        Get the height of the item.

        :returns: :php:class:`int`


    ..  php:method:: getWeight()

        Get the weight of the item.

        :returns: :php:class:`int`


    ..  php:method:: getName()

        Get the name given to the item.

        :returns: :php:class:`string`


    ..  php:method:: getValue()

        Get the unit price of the item, as a :php:class:`Money` object.

        :returns: :php:class:`Money\\Money`