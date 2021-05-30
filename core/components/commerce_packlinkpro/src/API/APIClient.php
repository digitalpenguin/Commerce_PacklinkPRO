<?php
namespace DigitalPenguin\Commerce_PacklinkPRO\API;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class APIClient {

    /** @var Client */
    private $client;

    public function __construct(bool $useSandbox = false, string $apiKey = '')
    {
        // Sandbox uses a different base_uri and requires there to be no authorisation header param.
        if ($useSandbox) {
            $properties = [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'base_uri' => 'https://apisandbox.packlink.com',
            ];
        }
        // Use live API default
        else {
            $properties = [
                'headers' => [
                    'Authorization' => $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'base_uri' => 'https://api.packlink.com',
            ];
        }

        $this->client = new Client($properties);
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
            if($method === 'GET') {
                $query = http_build_query($data);
                $response = $this->client->request('GET', $resource, [
                    'query' => $query,
                ]);
            }
            else {
                $response = $this->client->request($method, $resource, [
                    'json' => $data,
                ]);
            }

            return Response::from($response);
        } catch (GuzzleException $e) {
            $errorResponse = new Response(false, 0);
            $errorResponse->addError(get_class($e), $e->getMessage());
            return $errorResponse;
        }
    }
}