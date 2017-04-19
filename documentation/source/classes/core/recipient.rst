Recipient
=========
..  php:namespace:: Airmee\PhpSdk


..  php:class:: Recipient

    The Recipient class

    ..  php:method:: __construct($name, $phoneNumber, $email)

        Construct a new Recipient object.

        :param string $name: The name of the recipient
        :param Phonenumber $phoneNumber: A contact phone number for the recipient.  This must be given as a :php:class:`Phonenumber` object.
        :param string $email: A contact email address for the recipient.

        :returns: A Recipient


    ..  php:method:: getName()

        Get the recipient's name.

        :returns: :php:class:`string`


    ..  php:method:: getPhoneNumber()

        Get the contact :php:class:`PhoneNumber` for the recipient.

        :returns: :php:class:`libphonenumber\\PhoneNumber`


    ..  php:method:: getEmail()

        Get the contact email address for the recipient.

        :returns: :php:class:`string`