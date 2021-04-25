<?php

namespace Tleckie\Async;

use Throwable;

use Exception;
use Opis\Closure\SerializableClosure;
use Tleckie\Async\SerializeException;
/**
 * Class Child
 *
 * @package Tleckie\Async
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class Child
{
    /** @var Encoder */
    private Encoder $encoder;

    /** @var mixed */
    private mixed $output = null;

    /** @var mixed */
    private mixed $exception = null;

    /**
     * Child constructor.
     *
     * @param Encoder $encoder
     */
    public function __construct(Encoder $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param string|null $encoded
     * @return $this
     */
    public function handle(?string $encoded): self
    {
        try {
            $task = $this->encoder->decode($encoded);
            $this->output = $this->encoder->encode($task());
        } catch (Throwable $exception) {
            $this->exception = $this->encoder->encode(new SerializeException($exception));
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function exception(): mixed
    {
        return $this->exception;
    }

    /**
     * @return mixed
     */
    public function output(): mixed
    {
        return $this->output;
    }

    /**
     * @return $this
     */
    public function write(): self
    {
        fwrite(($this->hasError()) ? STDERR : STDOUT, ($this->exception) ?? $this->output);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return isset($this->exception);
    }

    /**
     * @codeCoverageIgnore
     */
    public function close()
    {
        if ($this->exception) {
            exit(1);
        }
        exit(0);
    }
}
