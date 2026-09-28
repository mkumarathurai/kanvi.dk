<?php

namespace App\Actions\Polls;

use App\Domain\Polls\Models\AdminAccess;
use App\Domain\Polls\Models\Poll;

final readonly class CreatedPoll
{
    public function __construct(
        public Poll $poll,
        public AdminAccess $adminAccess,
        #[\SensitiveParameter] public string $adminToken,
    ) {}
}
