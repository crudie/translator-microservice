<?php

namespace Domain\Model\Translation;

use Domain\Model\Locale\LocaleModel;


interface TranslationRepository
{
    /**
     * Find all translations by locale
     *
     * @param LocaleModel $localeModel
     *
     * @return TranslationModel[]
     */
    public function findAllByLocale(LocaleModel $localeModel);

    /**
     * Find one translation by locale and key
     *
     * @param LocaleModel $localeModel
     * @param string $key
     *
     * @return TranslationModel|null
     */
    public function findOneByLocaleAndKey(LocaleModel $localeModel, $key);

    /**
     * Save model
     *
     * @param TranslationModel $model
     *
     * @return string
     */
    public function save(TranslationModel $model);

    /**
     * Delete model
     *
     * @param TranslationModel $model
     */
    public function delete(TranslationModel $model);
}