<?php

namespace Application\Test\Functional;


use Domain\Model\Locale\LocaleModel;
use Domain\Model\Translation\TranslationModel;

class TranslationControllerTest extends BaseFunctionalTest
{
    /**
     * @var LocaleModel
     */
    private $locale;
    /**
     * @var TranslationModel
     */
    private $translation;
    /**
     * @var TranslationModel
     */
    private $secondTranslation;

    /**
     * Add test locale on setUp
     */
    public function setUp()
    {
        parent::setUp();

        $this->locale = new LocaleModel('test');
        $this->translation = new TranslationModel($this->locale, 'test', 'translated');
        $this->secondTranslation = new TranslationModel($this->locale, 'test2', 'translated2');

        $this->app['repository.locale']->save($this->locale);
        $this->app['repository.translation']->save($this->translation);
        $this->app['repository.translation']->save($this->secondTranslation);
    }

    /**
     * Test /translations/{locale} GET method
     */
    public function testTranslationList()
    {
        $client = $this->createClient();
        $response = $client->get(sprintf('%s/translations/%s', $_ENV['site_url'], $this->locale->getName()));
        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains(
            ['key' => $this->translation->getKey(), 'translation' => $this->translation->getTranslation()],
            $body['response']
        );
        $this->assertContains(
            ['key' => $this->secondTranslation->getKey(), 'translation' => $this->secondTranslation->getTranslation()],
            $body['response']
        );
    }

    /**
     * Test /translations/{locale}/{words} GET method with single word
     */
    public function testSingleTranslation()
    {
        $client = $this->createClient();
        $response = $client->get(sprintf('%s/translations/%s/%s', $_ENV['site_url'], $this->locale->getName(), $this->translation->getKey()));
        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains(
            ['key' => $this->translation->getKey(), 'translation' => $this->translation->getTranslation()],
            $body['response']
        );
    }

    /**
     * Test /translations/{locale}/{words} GET method with multiple words
     */
    public function testMultipleTranslations()
    {
        $client = $this->createClient();
        $response = $client->get(
            sprintf(
                '%s/translations/%s/%s&%s',
                $_ENV['site_url'],
                $this->locale->getName(),
                $this->translation->getKey(),
                $this->secondTranslation->getKey()
            ));
        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains(
            ['key' => $this->translation->getKey(), 'translation' => $this->translation->getTranslation()],
            $body['response']
        );
        $this->assertContains(
            ['key' => $this->secondTranslation->getKey(), 'translation' => $this->secondTranslation->getTranslation()],
            $body['response']
        );
    }

    /**
     * Remove test locale on tearDown
     */
    public function tearDown()
    {
        parent::tearDown();

        $this->app['repository.locale']->delete($this->locale);
        $this->app['repository.translation']->delete($this->translation);
        $this->app['repository.translation']->delete($this->secondTranslation);
    }
}