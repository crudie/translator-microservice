<?php

namespace Application\Test\Unit\Repository;

use Mockery as m;
use Predis\Client;

use Application\Repository\LocaleRedisRepository;
use Application\Test\Unit\BaseUnitTest;

use Domain\Model\Locale\LocaleModel;


/**
 * Test of LocaleRedisRepository
 */
class LocaleRedisRepositoryTest extends BaseUnitTest
{
    /**
     * Test findAll method when no locales exists
     * Method should return empty array
     */
    public function testFindAllWhenNoLocalesExists()
    {
        $redis = $this->createRedisStub();

        $redis->shouldReceive('smembers')
            ->with(LocaleRedisRepository::KEY)
            ->once()
            ->andReturn([]);

        $this->assertEmpty($this->createRepository($redis)->findAll(), 'Method should return empty array');
    }

    /**
     * Test findAll method when some locales available
     * Method should return array of LocaleModels
     */
    public function testFindAll()
    {
        $redis = $this->createRedisStub();

        $redis->shouldReceive('smembers')
            ->with(LocaleRedisRepository::KEY)
            ->once()
            ->andReturn(['en', 'ru']);

        $this->assertEquals(
            [new LocaleModel('en'), new LocaleModel('ru')],
            $this->createRepository($redis)->findAll(),
            'Method should return array of LocaleModels'
        );
    }

    /**
     * Test findOneByName method when locale with given name does not exist
     * Method should return null
     */
    public function testFindOneByNameWhenLocaleDoesNotExist()
    {
        $redis = $this->createRedisStub();

        $redis->shouldReceive('sismember')
            ->with(LocaleRedisRepository::KEY, 'locale')
            ->once()
            ->andReturn(false);

        $this->assertNull(
            $this->createRepository($redis)->findOneByName('locale'),
            'Method should return null'
        );

    }

    /**
     * Test findOneByName method when locale with given name exist
     * Method should return LocaleModel
     */
    public function testFindOneByNameWhenLocaleExist()
    {
        $redis = $this->createRedisStub();

        $redis->shouldReceive('sismember')
            ->with(LocaleRedisRepository::KEY, 'en')
            ->once()
            ->andReturn(true);

        $this->assertEquals(
            new LocaleModel('en'),
            $this->createRepository($redis)->findOneByName('en'),
            'Method should return LocaleModel'
        );
    }

    /**
     * Test findOneByName method when locale with given name exist, but in lower case
     * Method should call mb_strtolower function and return LocaleModel
     */
    public function testFindOneByNameWhenLocaleExistInLowerCase()
    {
        $redis = $this->createRedisStub();

        $redis->shouldReceive('sismember')
            ->with(LocaleRedisRepository::KEY, 'en')
            ->once()
            ->andReturn(true);

        $this->assertEquals(
            new LocaleModel('en'),
            $this->createRepository($redis)->findOneByName('eN'),
            'Method should return LocaleModel'
        );
    }

    /**
     * Test save method
     */
    public function testSave()
    {
        $redis = $this->createRedisStub();

        $redis->shouldReceive('sadd')
            ->with(LocaleRedisRepository::KEY, 'en')
            ->once()
            ->andReturn(true);

        $this->createRepository($redis)->save(new LocaleModel('en'));
    }

    /**
     * Test delete method
     */
    public function testDelete()
    {
        $redis = $this->createRedisStub();

        $redis->shouldReceive('srem')
            ->with(LocaleRedisRepository::KEY, 'en')
            ->once()
            ->andReturn(true);

        $this->createRepository($redis)->delete(new LocaleModel('en'));
    }

    /**
     * Create repo
     *
     * @param Client $connection
     *
     * @return LocaleRedisRepository
     */
    protected function createRepository(Client $connection)
    {
        return new LocaleRedisRepository($connection);
    }
}