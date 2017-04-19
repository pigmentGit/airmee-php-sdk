Configuration
=============

The :php:class:`AirmeeApi` object can be configured in two ways, by passing a configuration array,
or by passing the path to a .ini-formatted configuration file.  This supports the scenarios where you want to have a
separate configuration for Airmee, and where you want to combine the parameters with the configuration of the rest of
your application.

..  code-block:: php

    use Airmee\PhpSdk\Core;

    // Ini file
    $api = new AirmeeApi('/path/to/config.ini');

    // Array
    $config = [
        'sandbox' => false,
    ];
    $api = new AirmeeApi($config);

Parameters
----------

*   ``sandbox`` :php:class:`bool` Whether to act in sandbox mode.  In sandbox mode, requests are submitted to the staging
    API endpoint at https://staging-api.airmee.com rather than the production API.  Useful for testing without incurring
    real costs or having real vans turn up at your stores for pickups!  **Note that this is true by default**.

*   ``auth.jwt`` :php:class:`string` The authentication token that authorises you to access the API.

*   ``endpoint.staging`` :php:class:`string` To override the API endpoint for the staging server.  Useful if you want to
    mock the API entirely for testing purposes.

*   ``endpoint.production`` :php:class:`string` Allows you to override the API endpoint for the production environment.
    You should only set this if your Airmee account manager advises you to.