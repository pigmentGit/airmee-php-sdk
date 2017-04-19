TimeRange
=========
..  php:namespace:: Airmee\PhpSdk


..  php:class:: TimeRange

    The TimeRange class

    ..  php:method:: __construct($start, $end)

        Construct a new TimeRange object.

        :param $start: The start.  Can be a :php:class:`DateTime` object or a Unix timestamp
        :param $end: The start.  Can be a :php:class:`DateTime` object or a Unix timestamp

        :returns: A TimeRange


    ..  php:method:: getStart()

        Get the time at the beginning of the range, as a `PHP DateTime <http://php.net/manual/en/class.datetime.php>`_ object.

        :returns: :php:class:`DateTime`


    ..  php:method:: getEnd()

        Get the time at the end of the range, as a `PHP DateTime <http://php.net/manual/en/class.datetime.php>`_ object.

        :returns: :php:class:`DateTime`