<?php

namespace Domain\Model\Translation;

use Domain\Model\Locale\LocaleModel;


/**
 * Translation model
 */
class TranslationModel
{
    /**
     * @var LocaleModel
     */
    private $locale;
    /**
     * @var string
     */
    private $key;
    /**
     * @var string
     */
    private $translation;

    /**
     * TranslationModel constructor.
     * @param LocaleModel $locale
     * @param string $key
     * @param string $translation
     */
    public function __construct(LocaleModel $locale, $key, $translation)
    {
        $this->locale = $locale;
        $this->key = $key;
        $this->translation = $translation;
    }

    /**
     * @return LocaleModel
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return mb_strtolower($this->key, 'UTF-8');
    }

    /**
     * @return string
     */
    public function getTranslation()
    {
        return $this->translation;
    }
}