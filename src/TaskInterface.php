<?php

namespace Tleckie\Async;

/**
 * Interface TaskInterface
 *
 * @package Tleckie\Async
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
interface TaskInterface
{
    /**
     * @return int
     */
    public function id(): int;

    /**
     * @return int
     */
    public function pid(): int;

    /**
     * @return $this
     */
    public function start(): self;

    /**
     * @param callable $callback
     * @return mixed
     */
    public function then(callable $callback): self;

    /**
     * @param callable $callback
     * @return mixed
     */
    public function catch(callable $callback): self;

    /**
     * @return $this
     */
    public function handle(): self;

    /**
     * @return $this
     */
    public function error(): self;

    /**
     * @return bool
     */
    public function isRunning(): bool;

    /**
     * @return bool
     */
    public function isSuccessful(): bool;

    /**
     * @return bool
     */
    public function isTerminated(): bool;

    /**
     * @return mixed
     */
    public function output(): mixed;

    /**
     * @return mixed
     */
    public function getErrorOutput(): mixed;
}
