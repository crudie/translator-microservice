<?php

namespace Application\Test\Unit;

use Mockery as m;

use PHPUnit\Framework\TestCase;
use Predis\Client;


/**
 * Base unit test
 */
abstract class BaseUnitTest extends TestCase
{
    /**
     * Close mockery
     */
    public function tearDown()
    {
        parent::tearDown();

        m::close();
    }


    /**
     * Create redis stub for tests
     *
     * @return \Mockery\MockInterface
     */
    protected function createRedisStub()
    {
        return m::mock(Client::class);
    }
}