<?php

namespace App\Console\Commands;

use App\Actions\Polls\PurgeExpiredPolls as Purge;
use Illuminate\Console\Command;

class PurgeExpiredPolls extends Command
{
    protected $signature = 'kanvi:purge-polls';

    protected $description = 'Delete polls whose retention window has passed, with their participants and responses';

    public function handle(Purge $purge): int
    {
        $cutoff = $purge->cutoff();
        $deleted = $purge->handle();
        $this->info(sprintf('Deleted %d poll(s) last active before %s.', $deleted, $cutoff->toDateTimeString()));

        return self::SUCCESS;
    }
}
