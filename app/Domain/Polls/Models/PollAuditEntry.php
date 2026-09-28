<?php

namespace App\Domain\Polls\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class PollAuditEntry extends Model
{
    use HasUlids;

    protected $fillable = ['poll_id', 'admin_access_id', 'action', 'details'];

    protected function casts(): array
    {
        return ['details' => 'array'];
    }
}
