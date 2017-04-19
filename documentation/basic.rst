Basic Usage
===========

The :php:class:`AirmeeApi` object is used to generate requests to the Airmee API

..  code-block:: php

    use Airmee\PhpSdk\Core;

    // Array
    $config = [
        'sandbox' => true,
    ];
    $api = new AirmeeApi($config);

    $placeId = '14cab45e-1392-486e-bb83-cfdaf53d92b5'; // Example, not actually a recognised place
    $address = new Address(11428, 'Sweden');

    $schedules = $api->deliveryIntervalsForZipCode($placeId, $address);

    var_dump($schedules);

Produces output like:

..  code-block:: none

    {
      [0]=>
      object(Airmee\PhpSdk\Core\Models\Schedule)#712 (2) {
        ["pickup":"Airmee\PhpSdk\Core\Models\Schedule":private]=>
        object(Airmee\PhpSdk\Core\Models\TimeRange)#714 (2) {
          ["start":"Airmee\PhpSdk\Core\Models\TimeRange":private]=>
          object(DateTime)#713 (3) {
            ["date"]=>
            string(26) "2017-04-02 14:00:00.000000"
            ["timezone_type"]=>
            int(1)
            ["timezone"]=>
            string(6) "+00:00"
          }
          ["end":"Airmee\PhpSdk\Core\Models\TimeRange":private]=>
          object(DateTime)#30 (3) {
            ["date"]=>
            string(26) "2017-04-02 16:00:00.000000"
            ["timezone_type"]=>
            int(1)
            ["timezone"]=>
            string(6) "+00:00"
          }
        }
        ["dropoff":"Airmee\PhpSdk\Core\Models\Schedule":private]=>
        object(Airmee\PhpSdk\Core\Models\TimeRange)#29 (2) {
          ["start":"Airmee\PhpSdk\Core\Models\TimeRange":private]=>
          object(DateTime)#28 (3) {
            ["date"]=>
            string(26) "2017-04-02 08:00:00.000000"
            ["timezone_type"]=>
            int(1)
            ["timezone"]=>
            string(6) "+00:00"
          }
          ["end":"Airmee\PhpSdk\Core\Models\TimeRange":private]=>
          object(DateTime)#649 (3) {
            ["date"]=>
            string(26) "2017-04-02 14:00:00.000000"
            ["timezone_type"]=>
            int(1)
            ["timezone"]=>
            string(6) "+00:00"
          }
        }
      }
    }
