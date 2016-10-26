<?php

require('vendor/autoload.php');

class APITest extends PHPUnit_Framework_TestCase
{
    protected $client;

    protected function setUp()
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'http://localhost'
        ]);
    }

    public function testGet_ValidInput_TrailObject()
    {
        $response = $this->client->get('/rest/public/api.php/GetTrailById/25', [
            'query' => [
                'key' => 'abc123'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);

        $this->assertArrayHasKey('trail', $data);
        //$this->assertArrayHasKey('title', $data);
        //$this->assertArrayHasKey('author', $data);
        //$this->assertEquals(42, $data['price']);
    }
}