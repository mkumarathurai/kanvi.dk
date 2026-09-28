<?php

namespace Tests\Feature;

use Symfony\Component\Process\Process;
use Tests\TestCase;

class ConcurrentManagementTest extends TestCase
{
    public function test_removals_and_finalization_serialize_with_other_writes(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'kanvi-admin-concurrency-');
        $environment = ['APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $path,
            'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array'];
        $active = [];
        try {
            (new Process([PHP_BINARY, 'artisan', 'migrate', '--force', '--no-interaction'], base_path(), $environment))->mustRun();
            $worker = function (array $input) use ($environment, &$active) {
                $process = new Process([PHP_BINARY, 'tests/Support/concurrent-management.php'], base_path(), $environment);
                $process->setInput(json_encode($input));
                $process->start();
                $active[] = $process;

                return $process;
            };
            $finish = function (Process $process) {
                $process->wait();
                $this->assertTrue($process->isSuccessful(), $process->getErrorOutput().$process->getOutput());

                return json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            };
            $created = $finish($worker(['operation' => 'create']))['body'];
            $first = $worker(['operation' => 'remove', ...$created, 'version' => 0, 'option' => $created['options'][0]]);
            $second = $worker(['operation' => 'remove', ...$created, 'version' => 0, 'option' => $created['options'][1]]);
            $statuses = [$finish($first)['status'], $finish($second)['status']];
            sort($statuses);
            $this->assertSame([200, 409], $statuses);
            $snapshot = $finish($worker(['operation' => 'inspect', ...$created]))['body'];
            $this->assertSame(2, $snapshot['option_count']);
            $this->assertSame(1, $snapshot['audits']);
            $this->assertSame(422, $finish($worker(['operation' => 'remove', ...$created, 'version' => 1, 'option' => $created['options'][2]]))['status']);

            $created = $finish($worker(['operation' => 'create']))['body'];
            $payload = ['editor_id' => bin2hex(random_bytes(16)), 'changes' => [
                ['field' => 'name', 'value' => 'Concurrent participant', 'revision' => 1],
                ['field' => $created['options'][0], 'value' => 'can', 'revision' => 2],
            ]];
            $submission = ['operation' => 'submit', ...$created, 'token' => bin2hex(random_bytes(32)), 'payload' => $payload];
            $finalizing = $worker(['operation' => 'finalize', ...$created, 'version' => 0, 'option' => $created['options'][0]]);
            $voting = $worker($submission);
            $this->assertSame(200, $finish($finalizing)['status']);
            $voteStatus = $finish($voting)['status'];
            $this->assertContains($voteStatus, [200, 409]);
            $snapshot = $finish($worker(['operation' => 'inspect', ...$created]))['body'];
            $this->assertSame('finalized', $snapshot['status']);
            $this->assertSame($voteStatus === 200 ? 1 : 0, $snapshot['responses']);
            $this->assertSame(1, $snapshot['audits']);
            $this->assertSame(409, $finish($worker($submission))['status']);
        } finally {
            foreach ($active as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }
            foreach ([$path, $path.'-wal', $path.'-shm', $path.'-journal'] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}
