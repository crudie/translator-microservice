<?php

namespace Application\Controller;

use Application\Transformer\LocaleTransformer;
use Application\Transformer\TranslationTransformer;
use Domain\Model\Locale\LocaleRepository;
use Domain\Model\Translation\TranslationRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Translation controller
 */
class TranslationController
{
    /**
     * @var TranslationRepository
     */
    private $translationRepository;
    /**
     * @var LocaleRepository
     */
    private $localeRepository;

    /**
     * TranslationController constructor.
     *
     * @param TranslationRepository $translationRepository
     *
     * @param LocaleRepository $localeRepository
     * @internal param TranslationRepository $repository
     */
    public function __construct(TranslationRepository $translationRepository, LocaleRepository $localeRepository)
    {
        $this->translationRepository = $translationRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * Return a list of available translations
     *
     * @param string $localeName
     */
    public function listAction($localeName)
    {
        $locale = $this->localeRepository->findOneByName(
            $localeName
        );

        if (null === $locale) {
            throw new NotFoundHttpException(sprintf('Locale %s was not found', $localeName));
        }

        return TranslationTransformer::transformAll($this->translationRepository->findAllByLocale($locale));
    }

    /**
     * Translate given words (array of keys) to given locale
     *
     * @param string $localeName
     * @param array $words
     */
    public function translateAction($localeName, array $words)
    {
        $locale = $this->localeRepository->findOneByName(
            $localeName
        );

        if (null === $locale) {
            throw new NotFoundHttpException(sprintf('Locale %s was not found', $localeName));
        }

        $translations = [];

        foreach ($words as $word) {
            $translations[] = $this->translationRepository->findOneByLocaleAndKey($locale, $word);
        }

        return TranslationTransformer::transformAll($translations);
    }
}