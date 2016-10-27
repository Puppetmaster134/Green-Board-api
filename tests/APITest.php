<?php

require('vendor/autoload.php');

class APITest extends PHPUnit_Framework_TestCase
{
    protected $client;

    protected function setUp()
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'http://bzimmerman.me'
        ]);
    }
	
	/**
	* @test
	*/
	public function Get_ValidInput_CreateAccount()
	{
		$uniqueId = "test_" . substr(uniqid(),0,-5);
		$response = $this->client->get('/rest/public/api.php/RegisterUser/', [
            'query' => [
                'username' => $uniqueId,
				'password' => 'securepassword',
				'email' => $uniqueId . '@greenboard.com'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals("Registered Successfully", $response->getBody());

        
	}
	
	/**
	* @test
	*/
	public function Get_ValidInput_Login()
	{
		$response = $this->client->get('/rest/public/api.php/Login/', [
            'query' => [
                'username' => 'beezy',
				'password' => 'securepass'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
		$this->assertNotEquals("Invalid login credentials", $response->getBody());
		
	}
	
	/**
	* @test
	*/
    public function Get_ValidInput_TrailObject()
    {
        $response = $this->client->get('/rest/public/api.php/GetTrailById/25', [
            'query' => [
                'key' => 'abc123'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('trail', $data);
    }
	
	/**
	* @test
	*/
    public function Get_ValidInput_CreateTrail()
    {
        $response = $this->client->get('/rest/public/api.php/WriteTrailToDB/', [
            'query' => [
				'key' => 'abc123',
                'trailInfo' => 'A really cool test trail',
				'lat' => 29.1234567,
				'lng' => 42.4567890,
				'trailObj' => "{'trail':[{'beaconName':'BIT Building', 'latitude':36.6523321,'longitude':-121.7969833}]}"
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
		$this->assertNotEquals("Invalid parameters", $response->getBody());
		$this->assertNotEquals("Invalid API key", $response->getBody());
        //$data = json_decode($response->getBody(), true);
        //$this->assertArrayHasKey('trail', $data);
    }
}