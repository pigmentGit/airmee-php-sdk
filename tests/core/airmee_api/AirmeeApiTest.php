<?php
use Airmee\PhpSdk\Core\AirmeeApi;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * @file AirmeeApiTest.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 *
 * @coversDefaultClass \Airmee\PhpSdk\Core\Models\AirmeeApi
 */
abstract class AirmeeApiTest extends PHPUnit_Framework_TestCase
{

    public function getCredentials()
    {
        if(!file_exists(realpath(__DIR__ . '/credentials.ini'))){
            $this->markTestSkipped('No live server credentials saved in tests/core/airmee_api/credentials.ini');
        }
        $credentials = parse_ini_file('credentials.ini');
        return [$credentials['placeid'], $credentials['jwttoken']];
    }

    /**
     * Get a mock for the AirmeeApi that will respond with a given Response to the first submitted Request
     * @param Response $response
     * @param array $config
     * @param array $requestCatcher
     * @return AirmeeApi
     */
    protected function getAirmeeApiMock(Response $response, array $config = [], array &$requestCatcher = null)
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
            $response,
            new RequestException("Ran out of mock repsonses", new Request('GET', 'test'))
        ]);

        $handlerStack = HandlerStack::create($mock);

        if ($requestCatcher !== null) {
            // Capture the request once it comes in
            $history = Middleware::history($requestCatcher);
            $handlerStack->push($history);
        }

        return new AirmeeApi(array_merge($config, ['guzzle.handler' => $handlerStack]));
    }

    protected function getAirmeeApi($config)
    {
        return new AirmeeApi(array_merge($config, ['sandbox' => true]));
    }
}