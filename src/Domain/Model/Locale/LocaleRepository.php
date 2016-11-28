<?php

namespace Domain\Model\Locale;


interface LocaleRepository
{
    /**
     * Find all available locales
     *
     * @return LocaleModel[]
     */
    public function findAll();

    /**
     * Find one locale by name
     *
     * @param string $name
     *
     * @return LocaleModel
     */
    public function findOneByName($name);

    /**
     * Save model
     *
     * @param LocaleModel $model
     */
    public function save(LocaleModel $model);

    /**
     * Delete model
     *
     * @param LocaleModel $model
     */
    public function delete(LocaleModel $model);
}