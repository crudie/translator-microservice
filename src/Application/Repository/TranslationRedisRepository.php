<?php

namespace Application\Repository;

use Application\Helper\HashHelper;
use Domain\Model\Locale\LocaleModel;
use Domain\Model\Translation\TranslationModel;
use Domain\Model\Translation\TranslationRepository;
use Predis\Client;

/**
 * Redis implementation of TranslationRepository
 */
class TranslationRedisRepository implements TranslationRepository
{
    /**
     * @var Client
     */
    private $connection;

    const LOCALE_TRANSLATIONS_KEY = 'locales:%s';
    const TRANSLATION_KEY = 'locales:%s:translation:%s';

    /**
     * LocaleRedisRepository constructor.
     *
     * @param Client $connection
     */
    public function __construct(Client $connection)
    {
        $this->connection = $connection;
    }


    /**
     * Find all translations by locale
     *
     * @todo Inject logger and notify it about corrupted data
     *
     * @param LocaleModel $localeModel
     *
     * @return TranslationModel[]
     */
    public function findAllByLocale(LocaleModel $localeModel)
    {
        $translations = $this->connection->smembers(sprintf(self::LOCALE_TRANSLATIONS_KEY, $localeModel->getName()));
        $result = [];

        foreach ($translations as $translation) {
            try {
                $data = json_decode($translation, true);

                if (null === $data) {
                    throw new \LogicException(sprintf('Can\'t convert string %s to JSON', $translation));
                }

                $result[] = new TranslationModel($localeModel, $data['key'], $data['translation']);
            } catch (\Exception $e) {
            }
        }

        return $result;
    }

    /**
     * Find one translation by locale and key
     *
     * @todo Inject logger and notify it about corrupted data
     *
     * @param LocaleModel $localeModel
     * @param string $key
     *
     * @return TranslationModel|null
     */
    public function findOneByLocaleAndKey(LocaleModel $localeModel, $key)
    {
        $data = $this->connection->get(sprintf(self::TRANSLATION_KEY, $localeModel->getName(), HashHelper::hash(mb_strtolower($key, 'UTF-8'))));

        if ($data !== null) {
            try {
                $jsonData = json_decode($data, true);

                if (null === $jsonData) {
                    throw new \LogicException(sprintf('Can\'t convert string %s to JSON', $data));
                }

                return new TranslationModel($localeModel, $jsonData['key'], $jsonData['translation']);
            } catch (\Exception $e) {
            }
        }

        return null;
    }

    /**
     * save translation model
     *
     * @param TranslationModel $model
     *
     * @return string
     */
    public function save(TranslationModel $model)
    {
        $transaction = $this->connection->transaction();
        $translationKey = sprintf(self::TRANSLATION_KEY, $model->getLocale()->getName(), HashHelper::hash($model->getKey()));
        $localeKey = sprintf(self::LOCALE_TRANSLATIONS_KEY, $model->getLocale()->getName());
        $jsonEncoded = json_encode(['key' => $model->getKey(), 'translation' => $model->getTranslation()]);

        if (null !== ($data = $this->connection->get($translationKey))) {
            $transaction->srem($localeKey, $data);
        }

        $transaction->set($translationKey, $jsonEncoded)
            ->sadd($localeKey, $jsonEncoded);

        $transaction->execute();
    }

    /**
     * Delete model
     *
     * @param TranslationModel $model
     */
    public function delete(TranslationModel $model)
    {
        $translationKey = sprintf(self::TRANSLATION_KEY, $model->getLocale()->getName(), HashHelper::hash($model->getKey()));

        if (null !== ($data = $this->connection->get($translationKey))) {
            $this->connection->transaction()
                ->del($translationKey)
                ->srem(sprintf(self::LOCALE_TRANSLATIONS_KEY, $model->getLocale()->getName()), $data)
                ->execute();
        }
    }
}