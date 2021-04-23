<?php

namespace Tleckie\Async;

/**
 * Class Async
 *
 * @package Tleckie\Async
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class Async
{
    /** @var TaskInterface[] */
    protected array $pendingQueue = [];

    /** @var TaskInterface[] */
    protected array $progressQueue = [];

    /** @var TaskInterface[] */
    protected array $finishedQueue = [];

    /** @var TaskInterface[] */
    protected array $failedQueue = [];

    /** @var TaskFactoryInterface */
    protected TaskFactoryInterface $taskFactory;

    /** @var Encoder */
    protected Encoder $encoder;

    /** @var mixed[] */
    protected array $results = [];

    /** @var int */
    protected int $sleep;

    /**
     * Async constructor.
     *
     * @param TaskFactoryInterface|null $taskFactory
     * @param Encoder|null              $encoder
     * @param int|null                  $sleep
     */
    public function __construct(
        ?TaskFactoryInterface $taskFactory = null,
        ?Encoder $encoder = null,
        ?int $sleep = 5000
    ) {
        $this->taskFactory = $taskFactory ?? new TaskFactory();
        $this->encoder = $encoder ?? new Encoder();
        $this->sleep = $sleep;

        $this->listener();
    }

    protected function listener(): void
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGCHLD, function ($signo, $status) {
            while (true) {
                $pid = pcntl_waitpid(-1, $processState, WNOHANG | WUNTRACED);
                if ($pid <= 0) {
                    break;
                }

                $process = $this->progressQueue[$pid] ?? null;

                if (!$process || 0 === $status['status']) {
                    $this->finished($process);
                    continue;
                }

                $this->failed($process);
            }
        });
    }

    /**
     * @param TaskInterface $task
     * @return TaskInterface
     */
    protected function finished(TaskInterface $task): TaskInterface
    {
        unset($this->progressQueue[$task->pid()]);

        $this->notify();

        $this->results[] = $task->handle()->output();

        $this->finishedQueue[$task->pid()] = $task;

        return $task;
    }

    protected function notify(): void
    {
        $process = array_shift($this->pendingQueue);

        if (!$process) {
            return;
        }

        $this->progress($process);
    }

    /**
     * @param TaskInterface $task
     * @return TaskInterface
     */
    protected function progress(TaskInterface $task): TaskInterface
    {
        $task->start();

        unset($this->pendingQueue[$task->id()]);

        $this->progressQueue[$task->pid()] = $task;

        return $task;
    }

    /**
     * @param TaskInterface $task
     * @return TaskInterface
     */
    protected function failed(TaskInterface $task): TaskInterface
    {
        unset($this->progressQueue[$task->pid()]);

        $this->notify();

        $this->failedQueue[$task->pid()] = $task->error();

        return $task;
    }

    /**
     * @param callable $process
     * @return TaskInterface
     */
    public function add(callable $process): TaskInterface
    {
        $task = $this->taskFactory->createTask($process, $this->encoder);

        return $this->pending($task);
    }

    /**
     * @param TaskInterface $task
     * @return TaskInterface
     */
    protected function pending(TaskInterface $task): TaskInterface
    {
        $this->pendingQueue[$task->id()] = $task;

        $this->notify();

        return $task;
    }

    /**
     * @return array
     */
    public function wait(): array
    {
        while (true) {
            if (!count($this->progressQueue)) {
                break;
            }
            usleep($this->sleep);
        }

        return $this->results;
    }
}
