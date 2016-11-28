<?php

namespace Application\Transformer;


/**
 * Abstract json transformer
 */
abstract class JsonTransformer
{
    /**
     * Transform array of objects
     *
     * @param array $data
     *
     * @return array
     */
    public static function transformAll(array $data)
    {
        $result = [];

        foreach ($data as $item) {
            if ($item !== null) {
                $result[] = static::transform($item);
            }
        }

        return $result;
    }

    /**
     * Transform item
     *
     * @param Object $item
     *
     * @return array
     */
    public static function transform($item)
    {
        throw new \LogicException('Please, redefine me!');
    }
}