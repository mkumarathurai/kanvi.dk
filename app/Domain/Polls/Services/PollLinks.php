<?php

namespace App\Domain\Polls\Services;

final class PollLinks
{
    public function route(string $name, mixed $parameters = []): string
    {
        // Mail and social metadata must never trust an incoming Host header.
        return rtrim(config('app.url'), '/').route($name, $parameters, false);
    }
}
