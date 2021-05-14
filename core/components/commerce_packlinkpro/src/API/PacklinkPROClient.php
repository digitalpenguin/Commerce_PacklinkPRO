<?php
namespace DigitalPenguin\Commerce_PacklinkPRO\API;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class PacklinkPROClient {

    /** @var Client */
    private $client;

    public function __construct(string $apiKey, bool $testMode = true)
    {
        $this->client = new Client([
            'headers' => [
                'Content-Type'  => 'application/json',
            ],
            'base_uri'      =>  $testMode ? 'https://apisandbox.packlink.com/v1' : 'https://api.packlink.com/v1',
            'http_errors'   =>  false,
            'auth'          =>  [$apiKey, null]
        ]);
    }

    /**
     * Creates an API request and actions it
     * @param string $resource
     * @param array $data
     * @param string $method
     * @return Response
     */
    public function request(string $resource, array $data, string $method = 'POST'): Response
    {
        try {
            $response = $this->client->request($method, $resource, [
                'json' => $data,
            ]);
            return Response::from($response);
        } catch (GuzzleException $e) {
            $errorResponse = new Response(false, 0);
            $errorResponse->addError(get_class($e), $e->getMessage());
            return $errorResponse;
        }
    }
}