<?php

namespace Tleckie\Async;

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

        } catch (\Exception $exception) {
            $this->exception = $this->encoder->encode($exception);
        }

        return $this;
    }

    /**
     * @return \Exception|null
     */
    public function exception(): ?\Exception
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
        fwrite(STDOUT, ($this->exception) ?? $this->output);

        return $this;
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