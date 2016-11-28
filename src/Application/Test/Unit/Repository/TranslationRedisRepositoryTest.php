<?php

namespace Application\Test\Unit\Repository;

use Mockery as m;
use Predis\Client;

use Application\Helper\HashHelper;
use Application\Repository\TranslationRedisRepository;
use Application\Test\Unit\BaseUnitTest;

use Domain\Model\Locale\LocaleModel;
use Domain\Model\Translation\TranslationModel;
use Predis\Pipeline\Pipeline;
use Predis\Transaction\MultiExec;


/**
 * Test of TranslationRedisRepository
 */
class TranslationRedisRepositoryTest extends BaseUnitTest
{
    /**
     * Test findAllByLocale method when no translations exists
     * Method should return empty array
     */
    public function testFindAllByLocaleWhenNoTranslationsExists()
    {
        $redis = $this->createRedisStub();
        $locale = $this->createLocaleModel('en');

        $redis->shouldReceive('smembers')
            ->with(sprintf(TranslationRedisRepository::LOCALE_TRANSLATIONS_KEY, $locale->getName()))
            ->once()
            ->andReturn([]);

        $this->assertEmpty(
            $this->createRepository($redis)->findAllByLocale($locale),
            'Method should return empty array'
        );
    }

    /**
     * Test findAllByLocale method when translations exists
     * Method should return array of TranslationModels
     */
    public function testFindAllByLocaleWhenTranslationsExists()
    {
        $redis = $this->createRedisStub();
        $locale = $this->createLocaleModel('ru');

        $redis->shouldReceive('smembers')
            ->with(sprintf(TranslationRedisRepository::LOCALE_TRANSLATIONS_KEY, $locale->getName()))
            ->once()
            ->andReturn(['{"key": "hello", "translation": "привет"}', '{"key": "how are you?", "translation": "как дела?"}']);

        $this->assertEquals(
            [new TranslationModel($locale, "hello", "привет"), new TranslationModel($locale, "how are you?", "как дела?")],
            $this->createRepository($redis)->findAllByLocale($locale),
            'Method should return array of TranslationModels'
        );
    }

    /**
     * Test findAllByLocale method when translations exists, but one of them is corrupted
     * Method should return array of TranslationModels, except corrupted one
     */
    public function testFindAllByLocaleWhenTranslationsExistsButOneOfThemIsCorrupted()
    {
        $redis = $this->createRedisStub();
        $locale = $this->createLocaleModel('ru');

        $redis->shouldReceive('smembers')
            ->with(sprintf(TranslationRedisRepository::LOCALE_TRANSLATIONS_KEY, $locale->getName()))
            ->once()
            ->andReturn(['{"key": "hello", "translation": "привет"}', '[{zxcv"key": "how are you?", vv"translation": "как дела?"}']);

        $this->assertEquals(
            [new TranslationModel($locale, "hello", "привет")],
            $this->createRepository($redis)->findAllByLocale($locale),
            'Method should return TranslationModel'
        );
    }

    /**
     * Test findOneByLocaleAndKey method when translation does not exists
     * Method should return null
     */
    public function testFindOneByLocaleAndKeyWhenTranslationNotExist()
    {
        $redis = $this->createRedisStub();
        $locale = $this->createLocaleModel('ru');
        $key = 'hello';

        $redis->shouldReceive('get')
            ->with(sprintf(TranslationRedisRepository::TRANSLATION_KEY, $locale->getName(), HashHelper::hash($key)))
            ->once()
            ->andReturn(null);

        $this->assertNull(
            $this->createRepository($redis)->findOneByLocaleAndKey($locale, $key),
            'Method should return null'
        );
    }

    /**
     * Test findOneByLocaleAndKey method when translation exist
     * Method should return TranslationModel
     */
    public function testFindOneByLocaleAndKeyWhenTranslationExist()
    {
        $redis = $this->createRedisStub();
        $locale = $this->createLocaleModel('ru');
        $key = 'hello';

        $redis->shouldReceive('get')
            ->with(sprintf(TranslationRedisRepository::TRANSLATION_KEY, $locale->getName(), HashHelper::hash($key)))
            ->once()
            ->andReturn('{"key": "hello", "translation": "привет"}');

        $this->assertEquals(
            new TranslationModel($locale, "hello", "привет"),
            $this->createRepository($redis)->findOneByLocaleAndKey($locale, $key),
            'Method should return TranslationModel'
        );
    }

    /**
     * Test findOneByLocaleAndKey method when translation exist and key exist only in lowercase
     * Method should lower key and return TranslationModel
     */
    public function testFindOneByLocaleAndKeyWhenTranslationExistAndKeyExistInLowerCase()
    {
        $redis = $this->createRedisStub();
        $locale = $this->createLocaleModel('ru');
        $key = 'heLLo';

        $redis->shouldReceive('get')
            ->with(sprintf(TranslationRedisRepository::TRANSLATION_KEY, $locale->getName(), HashHelper::hash('hello')))
            ->once()
            ->andReturn('{"key": "hello", "translation": "привет"}');

        $this->assertEquals(
            new TranslationModel($locale, "hello", "привет"),
            $this->createRepository($redis)->findOneByLocaleAndKey($locale, $key),
            'Method should return TranslationModel'
        );
    }

    /**
     * Test findOneByLocaleAndKey method when translation exist but it corrupted
     * Method should return null
     */
    public function testFindOneByLocaleAndKeyWhenTranslationExistButCorrupted()
    {
        $redis = $this->createRedisStub();
        $locale = $this->createLocaleModel('ru');
        $key = 'hello';

        $redis->shouldReceive('get')
            ->with(sprintf(TranslationRedisRepository::TRANSLATION_KEY, $locale->getName(), HashHelper::hash($key)))
            ->once()
            ->andReturn('{x"key": "hello", "translation": "привет"}');

        $this->assertNull(
            $this->createRepository($redis)->findOneByLocaleAndKey($locale, $key),
            'Method should return null'
        );
    }

    /**
     * Test save method when translation is new
     * Method should call set and sadd in pipeline
     */
    public function testSaveWhenTranslationIsNew()
    {
        $redis = $this->createRedisStub();
        $transaction = $this->createTransaction();
        $locale = $this->createLocaleModel('en');
        $translation = new TranslationModel($locale, 'hello', 'hello');
        $translationKey = sprintf(TranslationRedisRepository::TRANSLATION_KEY, $locale->getName(), HashHelper::hash($translation->getKey()));
        $json = '{"key":"hello","translation":"hello"}';

        $redis->shouldReceive('get')
            ->with($translationKey)
            ->once()
            ->andReturn(null);

        $redis->shouldReceive('transaction')
            ->andReturn($transaction);

        $transaction->shouldReceive('set')
            ->with($translationKey, $json)
            ->once()
            ->andReturn($transaction);

        $transaction->shouldReceive('sadd')
            ->with(sprintf(TranslationRedisRepository::LOCALE_TRANSLATIONS_KEY, $locale->getName()), $json)
            ->once()
            ->andReturn($transaction);

        $transaction->shouldReceive('execute')
            ->once()
            ->andReturn(null);

        $this->createRepository($redis)->save($translation);
    }

    /**
     * Test save method when translation is old one, and we need to update it
     * Method should call set, srem and sadd in transaction
     */
    public function testSaveWhenTranslationIsNeedToUpdate()
    {
        $redis = $this->createRedisStub();
        $transaction = $this->createTransaction();
        $locale = $this->createLocaleModel('en');
        $translation = new TranslationModel($locale, 'hello', 'hello');
        $translationKey = sprintf(TranslationRedisRepository::TRANSLATION_KEY, $locale->getName(), HashHelper::hash($translation->getKey()));
        $localeKey = sprintf(TranslationRedisRepository::LOCALE_TRANSLATIONS_KEY, $locale->getName());
        $json = '{"key":"hello","translation":"hello"}';
        $oldJson = '{"key":"hello","translation":"old_hello"}';

        $redis->shouldReceive('get')
            ->with($translationKey)
            ->once()
            ->andReturn($oldJson);

        $redis->shouldReceive('transaction')
            ->andReturn($transaction);

        $transaction->shouldReceive('set')
            ->with($translationKey, $json)
            ->once()
            ->andReturn($transaction);

        $transaction->shouldReceive('srem')
            ->with($localeKey, $oldJson)
            ->once()
            ->andReturn($transaction);

        $transaction->shouldReceive('sadd')
            ->with($localeKey, $json)
            ->once()
            ->andReturn($transaction);

        $transaction->shouldReceive('execute')
            ->once()
            ->andReturn(null);

        $this->createRepository($redis)->save($translation);
    }

    /**
     * Test delete method when translation exist
     * Method should call get, del and srem in transaction
     */
    public function testDeleteWhenTranslationExist()
    {
        $redis = $this->createRedisStub();
        $transaction = $this->createTransaction();
        $locale = $this->createLocaleModel('en');
        $translation = new TranslationModel($locale, 'hello', 'hello');
        $translationKey = sprintf(TranslationRedisRepository::TRANSLATION_KEY, $locale->getName(), HashHelper::hash($translation->getKey()));
        $json = '{"key":"hello","translation":"hello"}';

        $redis->shouldReceive('get')
            ->with($translationKey)
            ->once()
            ->andReturn($json);

        $redis->shouldReceive('transaction')
            ->andReturn($transaction);

        $transaction->shouldReceive('del')
            ->with($translationKey)
            ->once()
            ->andReturn($transaction);

        $transaction->shouldReceive('srem')
            ->with(sprintf(TranslationRedisRepository::LOCALE_TRANSLATIONS_KEY, $locale->getName()), $json)
            ->once()
            ->andReturn($transaction);

        $transaction->shouldReceive('execute')
            ->once()
            ->andReturn(null);

        $this->createRepository($redis)->delete($translation);
    }

    /**
     * Test delete method when translation does not exist
     * Method should call only get
     */
    public function testDeleteWhenTranslationNotExist()
    {
        $redis = $this->createRedisStub();
        $locale = $this->createLocaleModel('en');
        $translation = new TranslationModel($locale, 'hello', 'hello');

        $redis->shouldReceive('get')
            ->with(sprintf(TranslationRedisRepository::TRANSLATION_KEY, $locale->getName(), HashHelper::hash($translation->getKey())))
            ->once()
            ->andReturn(null);

        $this->createRepository($redis)->delete($translation);
    }

    /**
     * Create repo
     *
     * @param Client $connection
     *
     * @return TranslationRedisRepository
     */
    protected function createRepository(Client $connection)
    {
        return new TranslationRedisRepository($connection);
    }

    /**
     * Create locale model
     *
     * @param string $name
     *
     * @return LocaleModel
     */
    protected function createLocaleModel($name)
    {
        return new LocaleModel($name);
    }

    /**
     * Create transaction mock
     *
     * @return m\MockInterface
     */
    protected function createTransaction()
    {
        return m::mock(MultiExec::class);
    }
}