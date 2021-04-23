<?php

namespace Tleckie\Async\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Tleckie\Async\Encoder;
use Tleckie\Async\Task;

/**
 * Class TaskTest
 *
 * @package Tleckie\Async\Tests
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class TaskTest extends TestCase
{
    /** @var Task */
    private Task $task;

    /** @var Process|MockObject */
    private Process $processMock;


    public function setUp(): void
    {
        $this->processMock = $this->createMock(Process::class);

        $this->task = new Task(
            $this->processMock,
            new Encoder(),
            1
        );
    }

    /**
     * @test
     */
    public function isRunning(): void
    {
        $this->processMock->expects(static::once())
            ->method('isRunning')
            ->willReturn(true);

        static::assertTrue($this->task->isRunning());
    }

    /**
     * @test
     */
    public function isSuccessful(): void
    {
        $this->processMock->expects(static::once())
            ->method('isSuccessful')
            ->willReturn(true);

        static::assertTrue($this->task->isSuccessful());
    }

    /**
     * @test
     */
    public function isTerminated(): void
    {
        $this->processMock->expects(static::once())
            ->method('isTerminated')
            ->willReturn(true);

        static::assertTrue($this->task->isTerminated());
    }
}