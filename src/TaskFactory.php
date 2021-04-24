<?php

namespace Tleckie\Async;

use Opis\Closure\SerializableClosure;
use Symfony\Component\Process\Process;

/**
 * Class TaskFactory
 *
 * @package Tleckie\Async
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class TaskFactory implements TaskFactoryInterface
{
    /** @var int */
    protected int $pid = 0;

    /** @var int */
    protected int $index = 0;

    /** @var string */
    protected string $script;

    /** @var string */
    protected string $autoloader;

    /**
     * TaskFactory constructor.
     */
    public function __construct()
    {
        $this->autoloader = $this->find('vendor/autoload.php');
        $this->script = $this->find('bin/child');
    }

    /**
     * @param string $file
     * @return string
     */
    protected function find(string $file): string
    {
        $paths = array_filter([
            __DIR__ . '/../../../../' . $file,
            __DIR__ . '/../../../' . $file,
            __DIR__ . '/../../' . $file,
            __DIR__ . '/../' . $file,
        ], static function (string $path) {
            return file_exists($path);
        });

        return reset($paths);
    }

    /**
     * @inheritdoc
     */
    public function createTask(
        $process,
        Encoder $encoder,
        $binary = PHP_BINARY
    ): TaskInterface {
        $process = new Process([
            $binary,
            $this->script,
            $this->autoloader,
            $encoder->encode(new SerializableClosure($process))
        ]);

        return new Task($process, $encoder, $this->id());
    }

    /**
     * @return int
     */
    protected function id(): int
    {
        return (++$this->index) . $this->pid();
    }

    /**
     * @return int
     */
    protected function pid(): int
    {
        $this->pid = $this->pid ?? getmypid();

        return (string)$this->pid;
    }
}
