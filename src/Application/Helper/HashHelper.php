<?php

namespace Application\Helper;

/**
 * For hashes
 */
class HashHelper
{
    /**
     * Hash string
     *
     * @param $string
     *
     * @return string
     */
    public static function hash($string)
    {
        return sprintf('%u', crc32($string));
    }
}