<?php

namespace Tleckie\Async;

/**
 * Interface TaskFactoryInterface
 *
 * @package Tleckie\Async
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
interface TaskFactoryInterface
{
    /**
     * @param         $process
     * @param Encoder $encoder
     * @param string  $binary
     * @return TaskInterface
     */
    public function createTask($process, Encoder $encoder, $binary = PHP_BINARY): TaskInterface;
}
