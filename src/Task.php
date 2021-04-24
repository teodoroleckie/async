<?php

/** @noinspection UnserializeExploitsInspection */

namespace Tleckie\Async;

use Symfony\Component\Process\Process;

/**
 * Class Task
 *
 * @package Tleckie\Async
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class Task implements TaskInterface
{
    /** @var int */
    protected int $id;

    /** @var int */
    protected int $pid;

    /** @var Process */
    protected Process $process;

    /** @var callable[] */
    protected array $success = [];

    /** @var callable[] */
    protected array $error = [];

    /** @var Encoder */
    protected Encoder $encoder;

    /**
     * Task constructor.
     *
     * @param Process $process
     * @param Encoder $encoder
     * @param int     $id
     */
    public function __construct(
        Process $process,
        Encoder $encoder,
        int $id
    ) {
        $this->process = $process;
        $this->encoder = $encoder;
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function then(callable $callback): self
    {
        $this->success[] = $callback;

        return $this;
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function catch(callable $callback): self
    {
        $this->error[] = $callback;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function handle(): self
    {
        $output = [$this->output()];

        foreach ($this->success as $callback) {
            $this->call($callback, $output);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function output(): mixed
    {
        return $this->encoder->decode(
            $this->process->getOutput()
        );
    }

    /**
     * @param callable $callback
     * @param array    $arguments
     */
    protected function call(callable $callback, array $arguments): void
    {
        call_user_func_array($callback, $arguments);
    }

    /**
     * @inheritdoc
     */
    public function error(): self
    {
        $output = [$this->getErrorOutput()];
        foreach ($this->error as $callback) {
            $this->call($callback, $output);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getErrorOutput(): mixed
    {
        return $this->encoder->decode(
            $this->process->getErrorOutput()
        );
    }

    /**
     * @inheritdoc
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function pid(): int
    {
        return $this->pid;
    }

    /**
     * @inheritdoc
     */
    public function start(): self
    {
        $this->process->start();

        $this->pid = $this->process->getPid();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isRunning(): bool
    {
        return $this->process->isRunning();
    }

    /**
     * @inheritdoc
     */
    public function isSuccessful(): bool
    {
        return $this->process->isSuccessful();
    }

    /**
     * @inheritdoc
     */
    public function isTerminated(): bool
    {
        return $this->process->isTerminated();
    }
}
