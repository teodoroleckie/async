<?php

namespace Tleckie\Async\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Tleckie\Async\Async;

/**
 * Class AsyncTest
 *
 * @backupGlobals disabled
 * @package       Tleckie\Async\Tests
 * @author        Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class AsyncTest extends TestCase
{
    /** @var Async */
    private Async $async;

    public function setUp(): void
    {
        $this->async = new Async(null, null, 0);
    }

    /**
     * @test
     */
    public function add(): void
    {
        $this->async->add(static function () {
            usleep(8000);

            return 555;
        });
        $this->async->add(static function () {
            return 2;
        });

        $values = $this->async->wait();
        static::assertContainsEquals(555, $values);
        static::assertContainsEquals(2, $values);
    }

    /**
     * @test
     * @runTestsInSeparateProcesses
     */
    public function then(): void
    {
        $this->async->add(static function () {
            return 555;
        })->then(static function (int $value) {
            static::assertEquals(555, $value);
        });

        $this->async->wait();
    }

    /**
     * @test
     * @runTestsInSeparateProcesses
     */
    public function failed(): void
    {
        $this->async->add(static function () {
            throw new Exception('Test message');
        })->catch(function ($exception) {
            static::assertStringContainsString('Test message', $exception->getMessage());
        });

        $this->async->wait();
    }
}
