<?php

namespace Application\Repository;

use Domain\Model\Locale\LocaleModel;
use Domain\Model\Locale\LocaleRepository;
use Predis\Client;


/**
 * Redis implementation of LocaleRepository
 */
class LocaleRedisRepository implements LocaleRepository
{
    /**
     * @var Client
     */
    private $connection;

    const KEY = 'locales';

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
     * Find all available locales
     *
     * @return LocaleModel[]
     */
    public function findAll()
    {
        $members = $this->connection->smembers(self::KEY);
        $result = [];

        foreach ($members as $member) {
            $result[] = new LocaleModel($member);
        }

        return $result;
    }

    /**
     * Find one locale by name
     *
     * @param string $name
     *
     * @return LocaleModel|null
     */
    public function findOneByName($name)
    {
        $name = mb_strtolower($name, 'UTF-8');

        if ($this->connection->sismember(self::KEY, $name)) {
            return new LocaleModel($name);
        }

        return null;
    }

    /**
     * Save model
     *
     * @param LocaleModel $model
     */
    public function save(LocaleModel $model)
    {
        $this->connection->sadd(self::KEY, $model->getName());
    }

    /**
     * Delete model
     *
     * @param LocaleModel $model
     */
    public function delete(LocaleModel $model)
    {
        $this->connection->srem(self::KEY, $model->getName());
    }
}