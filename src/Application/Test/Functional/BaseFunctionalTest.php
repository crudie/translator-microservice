<?php

namespace Application\Test\Functional;

use PHPUnit\Framework\TestCase;


/**
 * Base functional test
 */
abstract class BaseFunctionalTest extends TestCase
{
    protected $app;

    /**
     * Require $app for functional tests
     */
    public function setUp()
    {
        $this->app = require sprintf('%s/app.php', $_ENV['app_dir']);

        parent::setUp();
    }

    /**
     * Create client
     *
     * @return \GuzzleHttp\Client
     */
    protected function createClient()
    {
        return new \GuzzleHttp\Client();
    }
}