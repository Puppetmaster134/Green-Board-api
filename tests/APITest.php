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

  public function Get_AlreadyRegistered_User()
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
	public function Get_ValidInput_RegisterUserWithExistingEmail()
	{
		$response = $this->client->get('/rest/public/api.php/RegisterUser/', [
            'query' => [
                'username' => 'NeverGoingToRegister',
				'password' => 'securepassword',
				'email' => 'tester@gmail.com'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals("Email is already registered.", $response->getBody());

	}

  public function Get_AlreadyRegistered_Username()
  {
    $response = $this->client->get('/rest/public/api.php/RegisterUser/', [
            'query' => [
                'username' => 'testerr',
        'password' => 'dddddddd',
        'email' => 'bdamico33@gmail.com'
            ]
        ]);

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals("Username is already registered.", $response->getBody());
  }

  public function Get_Login_Successfully()
  {
    $response = $this->client->get('/rest/public/api.php/Login/', [
            'query' => [
                'username' => 'testerr',
                'password' => 'dddddddd',
            ]
        ]);

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals("U4c2f2b8759b49c28e6f32d9fe41d81a4", $response->getBody());
  }

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
	public function Get_ValidInput_RegisterUserWithFB()
	{
		$uniqueId = "test_" . substr(uniqid(),0,-5);
		$response = $this->client->get('/rest/public/api.php/RegisterUserWithFB/', [
            'query' => [
                'username' => $uniqueId,
				'email' => $uniqueId . '@greenboard.com',
				'fbid' => rand(0,1000000)
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals("Registered Successfully", $response->getBody());


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
