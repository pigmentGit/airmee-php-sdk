AirmeeApi
=========
..  php:namespace:: Airmee\PhpSdk


..  php:class:: AirmeeApi

    AirmeeApi class

    ..  php:method:: deliveryIntervalsForZipCode($placeId, Address $dropoffAddress)

        Get the delivery Schedules that could be offered to the customer for delivery from the origin store to the
        specified address

        :param UUID $placeId: The UUID for the place from which the items are to be collected
        :param Address $dropoffAddress: The :php:class:`Address` which the delivery is to be made to.

        :returns: An array of :php:class:`Schedule` objects

    ..  php:method:: requestDelivery($placeId, $ecommId, Recipient $recipient, Address $dropoffAddress, array $items, TimeRange $pickupInterval, TimeRange $dropoffInterval)

        Set the date.

        :param UUID $placeId: The UUID for the place from which the items are to be collected
        :param string $ecommId: An identifier for the corresponding order in your e-commerce system
        :param Recipient $recipient: The :php:class:`Recipient` of the order
        :param Address $dropoffAddress: The :php:class:`Address` which the delivery is to be made to.  This must be a fully-specified address including city and street-and-number values
        :param Item[] $items: An array of :php:class:`Item` to be delivered
        :param TimeRange $pickupInterval: A :php:class:`TimeRange` in which the items in the delivery will be available for collection
        :param TimeRange $dropoffInterval: A :php:class:`TimeRange` in which the recipient will be available

        :returns: An :php:class:`Order` object