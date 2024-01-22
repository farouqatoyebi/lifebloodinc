<?php
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestCase;

class PlatformCheckTest extends FeatureTestCase {
    public function testLoginLanding()
    {
        $response = $this->call("get", base_url("/login"));
        $response->assertStatus(200);
    }

    public function testLoginSubmit()
    {
        $response = $this->get(("/page-not-exist"), ["email", "password"]);
        $response->assertStatus(200);
    }
}