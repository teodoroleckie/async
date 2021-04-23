<?php

namespace Tleckie\Async;


use Opis\Closure\SerializableClosure;
use Symfony\Component\Process\Process;
use function Opis\Closure\serialize;

/**
 * Class TaskFactory
 *
 * @package Tleckie\Async
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class TaskFactory
{
    /** @var int */
    private int $pid = 0;

    /** @var int */
    private int $index = 0;

    /** @var string|null  */
    private string|null $script = null;

    /** @var string|null  */
    private string|null $autoloader = null;

    /**
     * TaskFactory constructor.
     */
    public function __construct()
    {
        $this->findAutoload()
            ->findScript();
    }

    /**
     * @return $this
     */
    private function findAutoload(): self
    {
        if (!$this->autoloader) {
            $paths = array_filter([
                __DIR__ . '/../../../../vendor/autoload.php',
                __DIR__ . '/../../../vendor/autoload.php',
                __DIR__ . '/../../vendor/autoload.php',
                __DIR__ . '/../vendor/autoload.php',
            ], static function (string $path) {
                return file_exists($path);
            });
            $this->autoloader = reset($paths);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function findScript(): self
    {
        if (!$this->script) {
            $paths = array_filter([
                __DIR__ . '/../../../../bin/child',
                __DIR__ . '/../../../bin/child',
                __DIR__ . '/../../bin/child',
                __DIR__ . '/../bin/child',
                __DIR__ . '/bin/child',

            ], static function (string $path) {
                return file_exists($path);
            });
            $this->script = reset($paths);
        }

        return $this;
    }

    /**
     * @param         $process
     * @param Encoder $encoder
     * @param string  $binary
     * @return TaskInterface
     */
    public function createTask(
        $process,
        Encoder $encoder,
        $binary = PHP_BINARY
    ): TaskInterface
    {
        $process = new Process([
            $binary,
            $this->script,
            $this->autoloader,
            $this->encode($process),
            null,
        ]);

        return new Task($process, $encoder, $this->id());
    }

    /**
     * @param callable $process
     * @return string
     */
    private function encode(callable $process): string
    {
        return base64_encode(serialize(
                new SerializableClosure($process)
            )
        );
    }

    /**
     * @return string
     */
    private function id(): string
    {
        return (++$this->index) . $this->pid();
    }

    /**
     * @return string
     */
    private function pid(): string
    {
        $this->pid = $this->pid ?? getmypid();

        return (string)$this->pid;
    }
}