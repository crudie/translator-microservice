<?php

namespace Application\Controller;

use Application\Transformer\LocaleTransformer;
use Domain\Model\Locale\LocaleRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;


/**
 * Locale controller
 */
class LocaleController
{
    /**
     * @var LocaleRepository
     */
    private $repository;

    /**
     * LocaleController constructor.
     *
     * @param LocaleRepository $repository
     */
    public function __construct(LocaleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Return a list of locales
     */
    public function listAction()
    {
        return LocaleTransformer::transformAll($this->repository->findAll());
    }
}