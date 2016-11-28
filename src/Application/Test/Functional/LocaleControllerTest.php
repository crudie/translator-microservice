<?php

namespace Application\Test\Functional;


use Application\Repository\LocaleRedisRepository;
use Domain\Model\Locale\LocaleModel;

class LocaleControllerTest extends BaseFunctionalTest
{
    /**
     * @var LocaleModel
     */
    private $locale;

    /**
     * Add test locale on setUp
     */
    public function setUp()
    {
        parent::setUp();

        $this->locale = new LocaleModel('test');

        $this->app['repository.locale']->save($this->locale);
    }

    /**
     * Test /locales GET method
     */
    public function testLocalesList()
    {
        $client = $this->createClient();
        $response = $client->get(sprintf('%s/locales', $_ENV['site_url']));
        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals(200 , $response->getStatusCode());
        $this->assertContains(['name' => 'test'], $body['response']);
    }

    /**
     * Remove test locale on tearDown
     */
    public function tearDown()
    {
        parent::tearDown();

        $this->app['repository.locale']->delete($this->locale);
    }
}