<?php

namespace Tleckie\Async;

use function base64_decode;
use function unserialize;

/**
 * Class Encoder
 *
 * @package Tleckie\Async
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class Encoder
{
    /**
     * @param mixed $object
     * @return string|null
     */
    public function encode(mixed $object): ?string
    {
        return base64_encode(serialize($object));
    }

    /**
     * @param string|null $string
     * @return mixed
     */
    public function decode(?string $string): mixed
    {
        return @unserialize(base64_decode($string));
    }
}
