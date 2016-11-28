<?php

namespace Application\Transformer;

use Domain\Model\Locale\LocaleModel;
use Domain\Model\Translation\TranslationModel;


/**
 * TranslationModel transformer
 */
class TranslationTransformer extends JsonTransformer
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
        if (!($item instanceof TranslationModel)) {
            throw new \LogicException(sprintf('Cannot transform %s class', get_class($item)));
        }

        return [
            'key' => $item->getKey(),
            'translation' => $item->getTranslation(),
        ];
    }
}