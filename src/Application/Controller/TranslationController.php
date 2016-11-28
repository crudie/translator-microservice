<?php

namespace Application\Controller;

use Application\Transformer\LocaleTransformer;
use Application\Transformer\TranslationTransformer;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Translation controller
 */
class TranslationController implements ControllerProviderInterface
{
    /**
     * Create controller collection
     *
     * @param Application $app
     *
     * @return ControllerCollection
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        // Get all translations by needle locale
        $controllers->get('/{localeName}', function ($localeName) use ($app) {
            $locale = $app['repository.locale']->findOneByName(
                $localeName
            );

            if (null === $locale) {
                throw new NotFoundHttpException(sprintf('Locale %s was not found', $localeName));
            }

            $translations = $app['repository.translation']->findAllByLocale($locale);

            return TranslationTransformer::transformAll($translations);
        });

        // Translate words for needle locale
        $controllers->get('/{localeName}/{words}', function ($localeName, array $words) use ($app) {
            $locale = $app['repository.locale']->findOneByName(
                $localeName
            );

            if (null === $locale) {
                throw new NotFoundHttpException(sprintf('Locale %s was not found', $localeName));
            }

            $translations = [];

            foreach ($words as $word) {
                $translations[] = $app['repository.translation']->findOneByLocaleAndKey($locale, $word);
            }

            return TranslationTransformer::transformAll($translations);
        })->convert('words', function ($string) {
            return explode('&', $string);
        });

        return $controllers;
    }
}