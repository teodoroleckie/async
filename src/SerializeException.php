<?php

namespace Tleckie\Async;

use ReflectionException;
use ReflectionObject;
use Serializable;
use Throwable;
use function serialize;
use function unserialize;

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
        $this->data = [
            get_class($exception),
            $exception->getMessage(),
            $exception->getCode(),
            $exception->getPrevious(),
            $exception->getTrace()
        ];
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize($this->data);
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
     */
    public function unserialize($serialized)
    {
        [$className, $message, $code, $previous, $trace] = unserialize($serialized, [\Exception::class]);

        $this->exception = new $className(
            $message,
            $code,
            $previous,
        );

        try {
            $reflectionObject = new ReflectionObject($this->exception);
            $reflectionObjectProp = $reflectionObject->getProperty('trace');
            $reflectionObjectProp->setAccessible(true);
            $reflectionObjectProp->setValue($this->exception, $trace);
        } catch (ReflectionException $exception) {
        }
    }
}
