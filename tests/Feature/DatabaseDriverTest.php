<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseDriverTest extends TestCase
{
    /**
     * CI runs the suite twice: on SQLite and on the engine production uses.
     * PHPUnit's <env> entries do not overwrite a variable the environment has
     * already set, but a MySQL job that quietly ran on SQLite would be worse
     * than no MySQL job at all, so the run states which driver it demands.
     */
    public function test_the_suite_runs_on_the_driver_the_environment_asked_for(): void
    {
        $expected = env('EXPECTED_DB_DRIVER');

        if (! $expected) {
            $this->markTestSkipped('No driver demanded; the suite runs on whatever is configured.');
        }

        $this->assertSame($expected, DB::connection()->getDriverName());
    }
}
