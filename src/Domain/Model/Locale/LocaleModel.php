<?php

namespace Domain\Model\Locale;

/**
 * Locale model
 */
class LocaleModel
{
    /**
     * @var string
     */
    private $name;

    /**
     * LocaleModel constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return mb_strtolower($this->name, 'UTF-8');
    }
}