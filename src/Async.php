<?php

namespace Tleckie\Async;

use Exception;

/**
 * Class Async
 *
 * @package Tleckie\Async
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class Async
{
    /** @var array */
    protected array $pendingQueue = [];

    /** @var array */
    protected array $progressQueue = [];

    /** @var array */
    protected array $finishedQueue = [];

    /** @var array */
    protected array $failedQueue = [];

    /** @var TaskFactory */
    private TaskFactory $taskFactory;

    /** @var Encoder  */
    private  Encoder $encoder;

    /** @var array  */
    private array $results = [];

    /** @var int  */
    private int $sleep;

    /**
     * Async constructor.
     *
     * @param TaskFactory|null $taskFactory
     * @param int|null         $sleep
     * @param Encoder|null     $encoder
     */
    public function __construct(
        ?TaskFactory $taskFactory = null,
        ?int $sleep = 50,
        ?Encoder $encoder = null
    )
    {
        $this->taskFactory = $taskFactory ?? new TaskFactory();
        $this->encoder = $encoder?? new Encoder();
        $this->sleep = $sleep;

        $this->listener();
    }

    private function listener(): void
    {
        pcntl_async_signals(true);

        pcntl_signal(SIGCHLD, function ($signo, $status) {
            while (true) {
                $pid = pcntl_waitpid(-1, $processState, WNOHANG | WUNTRACED);
                if ($pid <= 0) {
                    break;
                }

                $process = $this->progressQueue[$pid] ?? null;
                if (!$process) {
                    continue;
                }

                if (!$status['status']) {
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
    private function finished(TaskInterface $task): TaskInterface
    {
        unset($this->progressQueue[$task->pid()]);

        $this->notify();

        $this->results[] = $task->handle();

        $this->finishedQueue[$task->pid()] = $task;

        return $task;
    }

    private function notify(): void
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
    public function failed(TaskInterface $task): TaskInterface
    {
        unset($this->progressQueue[$task->pid()]);

        $this->notify();

        $this->failedQueue[$task->pid()] = $task->error();

        return $task;
    }

    /**
     * @param TaskInterface $task
     * @return TaskInterface
     */
    public function progress(TaskInterface $task): TaskInterface
    {
        $task->start();

        unset($this->pendingQueue[$task->id()]);

        $this->progressQueue[$task->pid()] = $task;

        return $task;
    }

    /**
     * @param $process
     * @return TaskInterface
     */
    public function add($process): TaskInterface
    {
        $task = $this->taskFactory->createTask($process, $this->encoder);

        return $this->pending($task);
    }

    /**
     * @param TaskInterface $task
     * @return TaskInterface
     */
    private function pending(TaskInterface $task): TaskInterface
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