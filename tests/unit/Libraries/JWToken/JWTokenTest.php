<?php

namespace Quantum\Test\Unit;

use PHPUnit\Framework\TestCase;
use Quantum\Libraries\JWToken\JWToken;

class JWTokenTest extends TestCase
{

    private $jwtToken;
    
    private $key = 'appkey';

    private $userData = [
        'userId' => 'b08f86af-35da-48f2-8fab-cef3904660bd',
        'userFirstName' => 'John',
        'userLastName' => 'Doe',
    ];

    public function setUp(): void
    {
        $claims = [
            'jti' => uniqid(),
            'iss' => 'issuer',
            'aud' => 'audience',
            'iat' => time(),
            'nbf' => time() + 1,
            'exp' => time() + 300
        ];

        $this->jwtToken = new JWToken($this->key);

        $this->jwtToken
            ->setLeeway(1)
            ->setClaims($claims);
    }

    public function testCompose()
    {
        $this->assertStringMatchesFormat('%s.%s.%s', $this->jwtToken->compose());
    }

    public function testSetAlgorithm()
    {
        $jwtEncoded = $this->jwtToken->setAlgorithm('HS512')->compose();

        $this->jwtToken->retrieve($jwtEncoded, ['HS512']);

        $this->assertNotEmpty($this->jwtToken->fetchPayload());

        $jwtEncoded = $this->jwtToken->setAlgorithm('HS384')->compose();

        $this->jwtToken->retrieve($jwtEncoded, ['HS384']);

        $this->assertNotEmpty($this->jwtToken->fetchPayload());

        $jwtEncoded = $this->jwtToken->setAlgorithm('HS512')->compose();

        $this->expectException(\UnexpectedValueException::class);

        $this->expectExceptionMessage('Algorithm not allowed');

        $this->jwtToken->retrieve($jwtEncoded, ['HS384']);

    }

    public function testRetrieveFetchPayload()
    {
        $this->assertEmpty($this->jwtToken->fetchPayload());

        $jwtEncoded = $this->jwtToken->setAlgorithm('HS256')->compose();

        $this->jwtToken->retrieve($jwtEncoded, ['HS256']);

        $this->assertNotEmpty($this->jwtToken->fetchPayload());
    }


    public function testSetFetchData()
    {
        $this->assertNull($this->jwtToken->fetchData());

        $jwtEncoded = $this->jwtToken->setAlgorithm('HS256')->setData($this->userData)->compose();

        $this->jwtToken->retrieve($jwtEncoded, ['HS256']);

        $this->assertNotNull($this->jwtToken->fetchData());

        $this->assertEquals($this->userData, $this->jwtToken->fetchData());


    }

    public function testSetFetchClaim()
    {
        $jwtEncoded = $this->jwtToken->setAlgorithm('HS256')->compose();

        $this->jwtToken->retrieve($jwtEncoded, ['HS256']);

        $this->assertNotNull($this->jwtToken->fetchClaim('iss'));

        $this->assertEquals('issuer', $this->jwtToken->fetchClaim('iss'));

        $this->assertNull($this->jwtToken->fetchClaim('sub'));

        $this->jwtToken->setClaim('sub', 'subject');

        $jwtEncoded = $this->jwtToken->setAlgorithm('HS256')->compose();

        $this->jwtToken->retrieve($jwtEncoded, ['HS256']);

        $this->assertNotNull($this->jwtToken->fetchClaim('sub'));

        $this->assertEquals('subject', $this->jwtToken->fetchClaim('sub'));

    }

    public function testSetClaims()
    {
        $jwtToken = new JWToken($this->key);

        $claims = [
            'jti' => uniqid(),
            'iat' => time(),
            'nbf' => time() + 1,
            'exp' => time() + 500
        ];

        $jwtToken->setLeeway(1)->setClaims($claims);

        $jwtEncoded = $jwtToken->setAlgorithm('HS512')->compose();

        $this->assertEmpty($jwtToken->fetchPayload());

        $jwtToken->retrieve($jwtEncoded, ['HS512']);

        $this->assertNotEmpty($jwtToken->fetchPayload());

        $this->assertIsObject($jwtToken->fetchPayload());

        $this->assertEquals($claims, (array)$jwtToken->fetchPayload());

    }


}