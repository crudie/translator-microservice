<?php

namespace Application\Transformer;

use Domain\Model\Locale\LocaleModel;


/**
 * LocaleModel transformer
 */
class LocaleTransformer extends JsonTransformer
{
    /**
     * Transform item to array
     *
     * @param LocaleModel $item
     *
     * @return array
     */
    public static function transform($item)
    {
        if (!($item instanceof LocaleModel)) {
            throw new \LogicException(sprintf('Cannot transform %s class', get_class($item)));
        }

        return [
            'name' => $item->getName()
        ];
    }
}