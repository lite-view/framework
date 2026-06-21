<?php

namespace Test;

use LiteView\Support\ApiToken;
use PHPUnit\Framework\TestCase;

class ApiTokenTest extends TestCase
{
    public function testCreateAndAuthSuccess()
    {
        $token = ApiToken::create(['user_id' => 42], 3600, 'api');
        $result = ApiToken::auth($token, 'api', $info);

        $this->assertEquals(0, $result);
        $this->assertEquals(42, $info['user_id']);
        $this->assertEquals('api', $info['guard']);
    }

    public function testAuthEmptyToken()
    {
        $result = ApiToken::auth('', 'api');
        $this->assertEquals(1, $result);
    }

    public function testAuthTamperedSign()
    {
        $token = ApiToken::create(['user_id' => 1], 3600);
        $token = substr($token, 0, -2) . 'XX';
        $result = ApiToken::auth($token, 'api');

        $this->assertEquals(2, $result);
    }

    public function testAuthInvalidBase64()
    {
        $result = ApiToken::auth('!!!invalid!!!', 'api');
        $this->assertEquals(2, $result);
    }

    public function testAuthExpiredToken()
    {
        $token = ApiToken::create(['user_id' => 1], -1);
        $result = ApiToken::auth($token, 'api');

        $this->assertEquals(3, $result);
    }

    public function testAuthGuardMismatch()
    {
        $token = ApiToken::create(['user_id' => 1], 3600, 'api');
        $result = ApiToken::auth($token, 'admin');

        $this->assertEquals(4, $result);
    }

    public function testInfoPassedByReference()
    {
        $token = ApiToken::create(['role' => 'admin'], 3600, 'api');
        $info = null;
        $result = ApiToken::auth($token, 'api', $info);

        $this->assertEquals(0, $result);
        $this->assertEquals('admin', $info['role']);
        $this->assertArrayHasKey('timestamp', $info);
        $this->assertArrayHasKey('nonce', $info);
    }

    public function testPasswdMakeAndAuth()
    {
        $hashed = ApiToken::passwdMake('my_password');
        $this->assertTrue(ApiToken::passwdAuth('my_password', $hashed));
        $this->assertFalse(ApiToken::passwdAuth('wrong_password', $hashed));
    }
}