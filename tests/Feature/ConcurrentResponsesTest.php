<?php

namespace Tests\Feature;

use Symfony\Component\Process\Process;
use Tests\Support\IsolatedDatabase;
use Tests\TestCase;

class ConcurrentResponsesTest extends TestCase
{
    use IsolatedDatabase;

    public function test_parallel_creation_and_reordered_retries_preserve_one_identity_and_latest_revision(): void
    {
        $environment = $this->isolatedDatabaseEnvironment();
        $processes = [];
        try {
            $migrate = new Process([PHP_BINARY, 'artisan', 'migrate', '--force', '--no-interaction'], base_path(), $environment);
            $migrate->mustRun();
            $worker = function (array $input) use ($environment) {
                $process = new Process([PHP_BINARY, 'tests/Support/concurrent-response.php'], base_path(), $environment);
                $process->setInput(json_encode($input));

                return $process;
            };
            $create = $worker(['operation' => 'create']);
            $create->mustRun();
            $ids = json_decode($create->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            $token = bin2hex(random_bytes(32));
            $editor = bin2hex(random_bytes(16));
            foreach ([1, 1, 4, 3, 2, 5, 1] as $revision) {
                $process = $worker([
                    'operation' => 'submit', 'poll' => $ids['poll'], 'token' => $token,
                    'payload' => ['editor_id' => $editor, 'changes' => [
                        ['field' => 'name', 'value' => 'Concurrent participant', 'revision' => 1],
                        ['field' => $ids['option'], 'value' => $revision === 5 ? 'maybe' : 'can', 'revision' => $revision],
                    ]],
                ]);
                $process->start();
                $processes[] = $process;
            }
            foreach ($processes as $process) {
                $process->wait();
                $this->assertTrue($process->isSuccessful(), $process->getErrorOutput().$process->getOutput());
            }
            $inspect = $worker(['operation' => 'inspect']);
            $inspect->mustRun();
            $this->assertSame(['participants' => 1, 'responses' => 1, 'value' => 'maybe'], json_decode($inspect->getOutput(), true));
        } finally {
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }
            $this->dropIsolatedDatabase();
        }
    }
}
