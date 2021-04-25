<?php

namespace Tleckie\Async;

use Serializable;
use Throwable;

/**
 * Class SerializeException
 *
 * @package Tleckie\Async
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class SerializeException implements Serializable
{
    /** @var array */
    private array $data = [];

    /** @var Throwable */
    private Throwable $exception;

    /**
     * SerializeException constructor.
     *
     * @param Throwable $exception
     */
    public function __construct(Throwable $exception)
    {
        $this->data[] = get_class($exception);
        $this->data[] = sprintf("%s %s", $exception->getMessage(), $exception->getTraceAsString());
        $this->data[] = $exception->getCode();
        $this->data[] = $exception->getPrevious();
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return \serialize($this->data);
    }

    /**
     * @return Throwable
     */
    public function exception(): Throwable
    {
        return $this->exception;
    }

    /**
     * @param string $serialized
     * @return Throwable
     */
    public function unserialize($serialized)
    {
        $data = \unserialize($serialized);

        [$className, $message, $code, $previous] = $data;

        $this->exception = new $className(
            $message,
            $code,
            $previous,
        );
    }
}