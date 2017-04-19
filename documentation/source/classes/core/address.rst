Address
=======
..  php:namespace:: Airmee\PhpSdk


..  php:class:: Address

    The Address class

    ..  php:method:: __construct($zipCode, $country[, $streetAndNumber = null, $city = null]])

        Construct a new Address object.

        :param string $zipCode: The Zip Code for the address.
        :param string $country: A two-digit `ISO 3166-2 <https://en.wikipedia.org/wiki/ISO_3166-2>`_ country code.  Some English full names of countries (eg "Sweden") are also accepted.
        :param string $streetAndNumber: The first line of the address.
        :param string $city: The city where the address is located.  Must be specified together with $streetAndNumber.

        :returns: An :php:class:`Address`


    ..  php:method:: getStreetAndNumber()

        Get the first line of the address

        :returns: :php:class:`string`


    ..  php:method:: getCity()

        Get the City

        :returns: :php:class:`string`


    ..  php:method:: getZipCode()

        Get the Zip Code

        :returns: :php:class:`string`


    ..  php:method:: getCountryCode()

        Get the `ISO 3166-2 <https://en.wikipedia.org/wiki/ISO_3166-2>`_ Country Code

        :returns: :php:class:`string`