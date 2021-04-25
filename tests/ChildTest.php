<?php

namespace Tleckie\Async\Tests;

use Opis\Closure\SerializableClosure;
use PHPUnit\Framework\TestCase;
use Tleckie\Async\Child;
use Tleckie\Async\Encoder;

/**
 * Class ChildTest
 *
 * @backupGlobals disabled
 * @package Tleckie\Async\Tests
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class ChildTest extends TestCase
{
    /** @var Child */
    private Child $child;

    /** @var Encoder */
    private Encoder $encoder;

    public function setUp(): void
    {
        $this->encoder = new Encoder();
        $this->child = new Child(
            $this->encoder
        );
    }

    /**
     * @test
     * @runTestsInSeparateProcesses
     */
    public function handle(): void
    {
        $closure = function () {
            return 33;
        };

        $encode = $this->encoder->encode(new SerializableClosure($closure));

        $output = $this->encoder->decode(
            $this->child->handle($encode)->output()
        );

        static::assertEquals(33, $output);
        static::assertFalse($this->child->hasError());
        static::assertNull($this->child->exception());
    }

    /**
     * @test
     */
    public function handleException(): void
    {
        $closure = function () {
            throw new \Exception('Test message');
        };

        $encode = $this->encoder->encode(new SerializableClosure($closure));

        $output = $this->encoder->decode(
            $this->child->handle($encode)->exception()
        );

        static::assertStringContainsString('Test message', $output->exception()->getMessage());
        static::assertTrue($this->child->hasError());
        static::assertNull($this->child->output());
    }

    /**
     * @test
     */
    public function write(): void
    {
        $closure = function () {
            return 'Same return value';
        };

        $encode = $this->encoder->encode(new SerializableClosure($closure));

        $this->child->handle($encode);
        static::assertInstanceOf(Child::class, $this->child->write());
    }
}
