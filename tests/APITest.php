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
	public function Get_ValidInput_RegisterUser()
	{
		$uniqueId = "test_" . rand(0,9999999);
		$response = $this->client->get('/rest/public/api.php/RegisterUser/', [
            'query' => [
                'username' => $uniqueId,
				'password' => 'securepassword',
				'email' => $uniqueId . '@greenboard.com'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('success', $data['args']);
        $this->assertEquals("Registered Successfully", $data['args']['success']);
	}


	/**
	* @test
	*/
	public function Get_ValidInput_RegisterUserWithExistingEmail()
	{
		$response = $this->client->get('/rest/public/api.php/RegisterUser/', [
            'query' => [
                'username' => 'NeverGoingToRegister',
				'password' => 'securepassword',
				'email' => 'test@greenboard.com'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('email', $data['args']);
        $this->assertEquals("Email is already registered.", $data['args']['email']);

	}

	/**
	* @test
	*/
	public function Get_AlreadyRegistered_Username()
	{
		$response = $this->client->get('/rest/public/api.php/RegisterUser/', [
				'query' => [
					'username' => 'Brian',
					'password' => 'dddddddd',
					'email' => 'bdamico33@gmail.com'
				]
			]);

		$this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('username', $data['args']);
        $this->assertEquals("Username is already registered.", $data['args']['username']);
	}

	/**
	* @test
	*/
	public function Get_Login_Successfully()
	{
		$response = $this->client->get('/rest/public/api.php/Login/', [
				'query' => [
					'username' => 'Brian',
					'password' => 'securepass',
				]
			]);

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals("abc123", $response->getBody());
	}

	/**
	* @test
	*/
	public function Get_Login_Password_Empty()
	{
		$response = $this->client->get('/rest/public/api.php/Login/', [
				'query' => [
					'username' => 'testerr',
					'password' => '',
				]
			]);

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals("Invalid login credentials", $response->getBody());
	}

	/**
	* @test
	*/
	public function Get_Login_Empty_Username()
	{
		$response = $this->client->get('/rest/public/api.php/Login/', [
				'query' => [
					'username' => '',
					'password' => 'dddddddd',
				]
			]);

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals("Invalid login credentials", $response->getBody());
	}

	/**
	* @test
	*/
	public function Get_ValidInput_Login()
	{
		$response = $this->client->get('/rest/public/api.php/Login/', [
            'query' => [
                'username' => 'Brian',
				'password' => 'securepass'
            ]
        ]);

    $this->assertEquals(200, $response->getStatusCode());
		$this->assertNotEquals("Invalid login credentials", $response->getBody());

	}

	/**
	* @test
	*/
	public function Get_ValidInput_RegisterUserWithFB()
	{
		$uniqueId = "test_" . rand(0,9999999);
		$response = $this->client->get('/rest/public/api.php/RegisterUserWithFB/', [
            'query' => [
                'username' => $uniqueId,
				'email' => $uniqueId . '@greenboard.com',
				'fbid' => rand(0,9999999)
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);

		//$this->expectOutputString('');
		//print_r($data);
        $this->assertArrayHasKey('success', $data['args']);
        $this->assertEquals("Registered Successfully", $data['args']['success']);


	}

	/**
	* @test
	*/
	public function Get_ValidInput_LoginWithFB()
	{
		$response = $this->client->get('/rest/public/api.php/LoginWithFB/', [
            'query' => [
                'fbid' => '1234567890'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
		$this->assertNotEquals("Invalid login credentials", $response->getBody());

	}

	/**
	* @test
	*/
    public function Get_ValidInput_GetTrailById()
    {
        $response = $this->client->get('/rest/public/api.php/GetTrailById/25', [
            'query' => [
                'key' => 'abc123'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

		$this->assertNotEquals("Invalid API key", $response->getBody());
		//$data = json_decode($response->getBody(), true);
        //$this->assertArrayHasKey('trail', $data);
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


	/**
	* @test
	*/
	public function Get_ValidInput_GetTrailInArea()
	{
		$response = $this->client->get('/rest/public/api.php/GetTrailInArea/', [
            'query' => [
                'key' => 'abc123',
				'minLat' => 0,
				'maxLat' => 40,
				'minLng' => 30,
				'maxLng' => 40
            ]
        ]);

		$data = json_decode($response->getBody(),true);
        $this->assertEquals(200, $response->getStatusCode());
		$this->assertNotEquals("Invalid parameters", $response->getBody());
		$this->assertNotEquals("Invalid API key", $response->getBody());
		//$this->assertEquals("Registered Successfully", $response->getBody());


	}

}
